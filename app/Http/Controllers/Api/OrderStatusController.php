<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStatusRequest;
use App\Http\Resources\OrderStatusResource;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderStatusController extends Controller
{
    /**
     * Display a listing of the order statuses.
     */
    public function index(Request $request): JsonResponse
    {
        $statuses = OrderStatus::query()
            ->when($request->has('is_default'), function ($query) use ($request) {
                $query->where('is_default', $request->boolean('is_default'));
            })
            ->orderBy('name')
            ->get();
            
        return response()->json([
            'data' => OrderStatusResource::collection($statuses),
        ]);
    }

    /**
     * Store a newly created order status.
     */
    public function store(OrderStatusRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // If this is set as default, unset any existing default
            if ($request->boolean('is_default')) {
                OrderStatus::where('is_default', true)->update(['is_default' => false]);
            }
            
            $status = OrderStatus::create($request->validated());
            
            DB::commit();
            
            return response()->json([
                'message' => 'Order status created successfully',
                'data' => new OrderStatusResource($status),
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create order status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create order status',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
    
    /**
     * Display the specified order status.
     */
    public function show(OrderStatus $status): JsonResponse
    {
        return response()->json([
            'data' => new OrderStatusResource($status),
        ]);
    }
    
    /**
     * Update the specified order status.
     */
    public function update(OrderStatusRequest $request, OrderStatus $status): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            
            // If this is set as default, unset any existing default
            if ($request->boolean('is_default') && !$status->is_default) {
                OrderStatus::where('is_default', true)
                    ->where('id', '!=', $status->id)
                    ->update(['is_default' => false]);
            }
            
            $status->update($data);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Order status updated successfully',
                'data' => new OrderStatusResource($status->fresh()),
            ]);
            
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
     * Remove the specified order status.
     */
    public function destroy(OrderStatus $status): JsonResponse
    {
        // Prevent deletion of default status
        if ($status->is_default) {
            return response()->json([
                'message' => 'Cannot delete the default order status',
            ], 422);
        }
        
        // Check if status is in use
        if ($status->orders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete status that is in use by orders',
            ], 422);
        }
        
        $status->delete();
        
        return response()->json([
            'message' => 'Order status deleted successfully',
        ]);
    }
    
    /**
     * Set a status as default.
     */
    public function setDefault(OrderStatus $status): JsonResponse
    {
        if ($status->is_default) {
            return response()->json([
                'message' => 'This status is already set as default',
            ]);
        }
        
        DB::beginTransaction();
        
        try {
            // Unset current default
            OrderStatus::where('is_default', true)
                ->where('id', '!=', $status->id)
                ->update(['is_default' => false]);
                
            // Set new default
            $status->update(['is_default' => true]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Default order status updated successfully',
                'data' => new OrderStatusResource($status->fresh()),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to set default order status: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to set default order status',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
