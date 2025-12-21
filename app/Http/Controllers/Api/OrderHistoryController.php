<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderHistoryRequest;
use App\Http\Resources\OrderHistoryResource;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderHistoryController extends Controller
{
    /**
     * Display a listing of the order history.
     */
    public function index(Order $order): JsonResponse
    {
        $this->authorize('viewAny', [OrderHistory::class, $order]);
        
        $history = $order->histories()
            ->with(['status', 'createdBy'])
            ->latest()
            ->get();
            
        return response()->json([
            'data' => OrderHistoryResource::collection($history),
        ]);
    }

    /**
     * Store a newly created order history entry.
     */
    public function store(OrderHistoryRequest $request, Order $order): JsonResponse
    {
        $this->authorize('create', [OrderHistory::class, $order]);
        
        DB::beginTransaction();
        
        try {
            // Update order status
            $order->update(['status_id' => $request->status_id]);
            
            // Create history entry
            $history = $order->histories()->create([
                'status_id' => $request->status_id,
                'comment' => $request->comment,
                'notify_customer' => $request->boolean('notify_customer', false),
                'created_by' => $request->user()->id,
            ]);
            
            // TODO: Send notification if notify_customer is true
            
            DB::commit();
            
            return response()->json([
                'message' => 'Order status updated successfully',
                'data' => new OrderHistoryResource($history->load(['status', 'createdBy'])),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update order status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update order status',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    
    /**
     * Display the specified order history entry.
     */
    public function show(Order $order, OrderHistory $history): JsonResponse
    {
        $this->authorize('view', [$history, $order]);
        
        return response()->json([
            'data' => new OrderHistoryResource($history->load(['status', 'createdBy'])),
        ]);
    }
    
    /**
     * Get the latest status update for an order.
     */
    public function latest(Order $order): JsonResponse
    {
        $this->authorize('viewAny', [OrderHistory::class, $order]);
        
        $latest = $order->histories()
            ->with(['status', 'createdBy'])
            ->latest()
            ->first();
            
        return response()->json([
            'data' => $latest ? new OrderHistoryResource($latest) : null,
        ]);
    }
}
