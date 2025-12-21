<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['status', 'paymentMethod', 'paymentStatus', 'items', 'items.product']);
        
        // Filter by user_id if not admin
        if (!$request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        }
        
        // Filter by status
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        
        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        // Search by order number or customer name/email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%");
            });
        }
        
        // Pagination
        $perPage = $request->per_page ?? 15;
        $orders = $query->latest()->paginate($perPage);
        
        return response()->json([
            'data' => OrderResource::collection($orders),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(OrderRequest $request): JsonResponse
    {
        $orderData = $request->validated();
        
        // Generate order number
        $orderData['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $orderData['user_id'] = $request->user()->id;
        
        // Set default status if not provided
        if (!isset($orderData['status_id'])) {
            $defaultStatus = OrderStatus::where('is_default', true)->first();
            if ($defaultStatus) {
                $orderData['status_id'] = $defaultStatus->id;
            }
        }
        
        DB::beginTransaction();
        
        try {
            // Create the order
            $order = Order::create($orderData);
            
            // Create order items
            foreach ($orderData['items'] as $item) {
                $orderItem = new OrderItem([
                    'product_id' => $item['product_id'],
                    'vendor_id' => $item['vendor_id'],
                    'product_name' => $item['product_name'] ?? null,
                    'product_sku' => $item['product_sku'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'options' => $item['options'] ?? null,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total' => ($item['price'] * $item['quantity']) - ($item['discount_amount'] ?? 0) + ($item['tax_amount'] ?? 0),
                ]);
                
                $order->items()->save($orderItem);
            }
            
            // Create initial order history
            $order->histories()->create([
                'status_id' => $order->status_id,
                'comment' => 'Order created',
                'notify_customer' => true,
                'created_by' => $request->user()->id,
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Order created successfully',
                'data' => new OrderResource($order->load(['status', 'paymentMethod', 'paymentStatus', 'items'])),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create order',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later.',
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'status', 
            'paymentMethod', 
            'paymentStatus', 
            'items', 
            'items.product', 
            'items.vendor',
            'histories',
            'histories.status',
            'histories.createdBy',
        ]);
        
        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update the specified order in storage.
     */
    public function update(OrderRequest $request, Order $order): JsonResponse
    {
        $orderData = $request->validated();
        
        DB::beginTransaction();
        
        try {
            // Update order
            $order->update($orderData);
            
            // If items are provided, update them
            if (isset($orderData['items'])) {
                $order->items()->delete();
                
                foreach ($orderData['items'] as $item) {
                    $orderItem = new OrderItem([
                        'product_id' => $item['product_id'],
                        'vendor_id' => $item['vendor_id'],
                        'product_name' => $item['product_name'] ?? null,
                        'product_sku' => $item['product_sku'] ?? null,
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'options' => $item['options'] ?? null,
                        'tax_amount' => $item['tax_amount'] ?? 0,
                        'discount_amount' => $item['discount_amount'] ?? 0,
                        'total' => ($item['price'] * $item['quantity']) - ($item['discount_amount'] ?? 0) + ($item['tax_amount'] ?? 0),
                    ]);
                    
                    $order->items()->save($orderItem);
                }
            }
            
            // If status changed, add to history
            if (isset($orderData['status_id']) && $order->wasChanged('status_id')) {
                $order->histories()->create([
                    'status_id' => $order->status_id,
                    'comment' => $request->status_comment ?? 'Order status updated',
                    'notify_customer' => $request->notify_customer ?? false,
                    'created_by' => $request->user()->id,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Order updated successfully',
                'data' => new OrderResource($order->load(['status', 'paymentMethod', 'paymentStatus', 'items'])),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order update failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update order',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later.',
            ], 500);
        }
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Soft delete the order
            $order->delete();
            
            // Add to history
            $order->histories()->create([
                'status_id' => $order->status_id,
                'comment' => 'Order cancelled',
                'notify_customer' => true,
                'created_by' => auth()->id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Order cancelled successfully',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order cancellation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later.',
            ], 500);
        }
    }
    
    /**
     * Get order statuses.
     */
    public function statuses(): JsonResponse
    {
        $statuses = OrderStatus::all(['id', 'name', 'color', 'is_default']);
        
        return response()->json([
            'data' => $statuses,
        ]);
    }
}
