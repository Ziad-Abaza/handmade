<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentStatusRequest;
use App\Http\Resources\PaymentStatusResource;
use App\Models\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PaymentStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $paymentStatuses = PaymentStatus::latest()->paginate(10);
        return PaymentStatusResource::collection($paymentStatuses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentStatusRequest $request): PaymentStatusResource
    {
        // If setting as default, update other statuses
        if ($request->is_default) {
            PaymentStatus::where('is_default', true)->update(['is_default' => false]);
        }

        $paymentStatus = PaymentStatus::create($request->validated());
        return new PaymentStatusResource($paymentStatus);
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentStatus $paymentStatus): PaymentStatusResource
    {
        return new PaymentStatusResource($paymentStatus);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentStatusRequest $request, PaymentStatus $paymentStatus): PaymentStatusResource
    {
        // If setting as default, update other statuses
        if ($request->is_default && !$paymentStatus->is_default) {
            PaymentStatus::where('is_default', true)->update(['is_default' => false]);
        }

        $paymentStatus->update($request->validated());
        return new PaymentStatusResource($paymentStatus);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentStatus $paymentStatus): JsonResponse
    {
        // Prevent deletion if there are orders with this status
        if ($paymentStatus->orders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete payment status that has associated orders.'
            ], Response::HTTP_CONFLICT);
        }

        $paymentStatus->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
