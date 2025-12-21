<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    /**
     * Get a paginated list of the user's orders.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $orders = $user->orders()
                ->with(['status', 'paymentStatus', 'items.product'])
                ->latest()
                ->paginate($request->input('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders->items(),
                    'pagination' => [
                        'total' => $orders->total(),
                        'per_page' => $orders->perPage(),
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'from' => $orders->firstItem(),
                        'to' => $orders->lastItem(),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    /**
     * Get order details by ID.
     * 
     * @param Order $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $this->authorize('view', $order);

            $order->load([
                'status',
                'paymentStatus',
                'paymentMethod',
                'items.product',
                'histories' => function ($query) {
                    $query->latest();
                },
                'histories.status',
                'histories.user'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'available_statuses' => OrderStatus::all()
                ]
            ]);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this order',
                'error' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track an order by order number.
     */
    /**
     * Track an order by order number and email.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function track(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'order_number' => 'required|string|exists:orders,order_number',
                'email' => 'required|email'
            ]);

            $order = Order::where('order_number', $validated['order_number'])
                ->where('billing_email', $validated['email'])
                ->with([
                    'status', 
                    'histories' => function($query) {
                        $query->latest();
                    }, 
                    'histories.status',
                    'items.product'
                ])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or email does not match',
                'error' => 'Invalid order number or email'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the order tracking form.
     */
    /**
     * Get the order tracking form data.
     * 
     * @return JsonResponse
     */
    public function showTrackForm(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'form' => [
                    'order_number' => '',
                    'email' => ''
                ]
            ]
        ]);
    }

    /**
     * Cancel an order.
     */
    /**
     * Cancel an order.
     * 
     * @param Order $order
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel(Order $order, Request $request): JsonResponse
    {
        try {
            $this->authorize('update', $order);

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order cannot be cancelled',
                    'error' => 'Order cancellation not allowed in current status'
                ], 400);
            }

            DB::transaction(function () use ($order) {
                $cancelledStatus = OrderStatus::where('name', 'cancelled')->firstOrFail();
                
                $order->update([
                    'status_id' => $cancelledStatus->id
                ]);

                // Add to order history
                $order->histories()->create([
                    'status_id' => $cancelledStatus->id,
                    'comment' => 'Order cancelled by customer',
                    'user_id' => auth()->id()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Order has been cancelled successfully',
                'data' => [
                    'order_id' => $order->id,
                    'status' => 'cancelled'
                ]
            ]);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this order',
                'error' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download order invoice.
     */
    /**
     * Generate and download order invoice PDF.
     * 
     * @param Order $order
     * @return \Illuminate\Http\Response
     */
    public function invoice(Order $order)
    {
        try {
            $this->authorize('view', $order);

            $order->load(['user', 'items.product', 'status', 'paymentMethod']);
            
            $pdf = PDF::loadView('pdf.invoice', [
                'order' => $order
            ]);

            return $pdf->download("invoice-{$order->order_number}.pdf");
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this invoice',
                'error' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display order history.
     */
    /**
     * Get order history.
     * 
     * @param Order $order
     * @return JsonResponse
     */
    public function history(Order $order): JsonResponse
    {
        try {
            $this->authorize('view', $order);

            $histories = $order->histories()
                ->with(['status', 'user'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'histories' => $histories
                ]
            ]);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view order history',
                'error' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
