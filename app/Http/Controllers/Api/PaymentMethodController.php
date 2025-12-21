<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the payment methods.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PaymentMethod::query()
            ->when($request->has('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->when($request->has('is_online'), function ($query) use ($request) {
                $query->where('is_online', $request->boolean('is_online'));
            })
            ->orderBy('name');
            
        $methods = $request->has('per_page')
            ? $query->paginate($request->input('per_page', 15))
            : $query->get();
            
        return PaymentMethodResource::collection($methods)->response();
    }

    /**
     * Store a newly created payment method.
     */
    public function store(PaymentMethodRequest $request): JsonResponse
    {
        $method = DB::transaction(function () use ($request) {
            return PaymentMethod::create($request->validated());
        });
        
        return response()->json([
            'message' => 'Payment method created successfully',
            'data' => new PaymentMethodResource($method),
        ], 201);
    }
    
    /**
     * Display the specified payment method.
     */
    public function show(PaymentMethod $method): JsonResponse
    {
        return response()->json([
            'data' => new PaymentMethodResource($method->load('config')),
        ]);
    }
    
    /**
     * Update the specified payment method.
     */
    public function update(PaymentMethodRequest $request, PaymentMethod $method): JsonResponse
    {
        $method = DB::transaction(function () use ($request, $method) {
            $method->update($request->validated());
            return $method->fresh();
        });
        
        return response()->json([
            'message' => 'Payment method updated successfully',
            'data' => new PaymentMethodResource($method->load('config')),
        ]);
    }
    
    /**
     * Remove the specified payment method.
     */
    public function destroy(PaymentMethod $method): JsonResponse
    {
        // Prevent deletion if there are associated orders
        if ($method->orders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete payment method that is in use by orders',
            ], 422);
        }
        
        $method->delete();
        
        return response()->json([
            'message' => 'Payment method deleted successfully',
        ]);
    }
    
    /**
     * Toggle the active status of a payment method.
     */
    public function toggleStatus(PaymentMethod $method): JsonResponse
    {
        $method->update(['is_active' => !$method->is_active]);
        
        return response()->json([
            'message' => 'Payment method status updated successfully',
            'data' => [
                'is_active' => (bool) $method->fresh()->is_active,
            ],
        ]);
    }
    
    /**
     * Get active payment methods for the frontend.
     */
    public function activeMethods(): JsonResponse
    {
        $methods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return PaymentMethodResource::collection($methods)->response();
    }
}
