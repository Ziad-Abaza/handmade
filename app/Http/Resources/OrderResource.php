<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'status' => [
                'id' => $this->status_id,
                'name' => $this->status->name,
                'color' => $this->status->color,
            ],
            'payment_method' => $this->whenLoaded('paymentMethod', [
                'id' => $this->payment_method_id,
                'name' => $this->paymentMethod->name ?? null,
            ]),
            'payment_status' => $this->whenLoaded('paymentStatus', [
                'id' => $this->payment_status_id,
                'name' => $this->paymentStatus->name ?? null,
                'color' => $this->paymentStatus->color ?? null,
            ]),
            'billing' => [
                'name' => $this->billing_name,
                'email' => $this->billing_email,
                'phone' => $this->billing_phone,
                'address' => $this->billing_address,
                'city' => $this->billing_city,
                'state' => $this->billing_state,
                'country' => $this->billing_country,
                'postcode' => $this->billing_postcode,
            ],
            'shipping' => [
                'name' => $this->shipping_name,
                'email' => $this->shipping_email,
                'phone' => $this->shipping_phone,
                'address' => $this->shipping_address,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'country' => $this->shipping_country,
                'postcode' => $this->shipping_postcode,
            ],
            'totals' => [
                'subtotal' => (float) $this->subtotal,
                'shipping' => (float) $this->shipping_cost,
                'tax' => (float) $this->tax_amount,
                'discount' => (float) $this->discount_amount,
                'total' => (float) $this->total,
            ],
            'shipping_method' => $this->shipping_method,
            'tracking_number' => $this->tracking_number,
            'transaction_id' => $this->transaction_id,
            'notes' => $this->when($this->notes, $this->notes),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'histories' => OrderHistoryResource::collection($this->whenLoaded('histories')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
