<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'user_id' => $this->user_id,
            'session_id' => $this->session_id,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'shipping' => (float) $this->shipping,
            'total' => (float) $this->total,
            'coupons' => $this->when($this->coupons, $this->coupons, []),
            'items_count' => $this->when(isset($this->items_count), $this->items_count),
            'is_empty' => $this->when(isset($this->items_count), $this->items_count === 0),
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
            'shipping_method' => $this->shipping_method,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'meta' => [
                'formatted_subtotal' => number_format($this->subtotal, 2),
                'formatted_discount' => number_format($this->discount, 2),
                'formatted_tax' => number_format($this->tax, 2),
                'formatted_shipping' => number_format($this->shipping, 2),
                'formatted_total' => number_format($this->total, 2),
            ]
        ];
    }
}
