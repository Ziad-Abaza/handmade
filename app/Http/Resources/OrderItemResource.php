<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'vendor_id' => $this->vendor_id,
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
            'options' => $this->when($this->options, $this->options),
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total' => (float) $this->total,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'slug' => $this->product->slug,
                    'image_url' => $this->product->image_url,
                ];
            }),
            'vendor' => $this->whenLoaded('vendor', function () {
                return [
                    'id' => $this->vendor->id,
                    'name' => $this->vendor->name,
                    'email' => $this->vendor->email,
                ];
            }),
            'downloads' => OrderDownloadResource::collection($this->whenLoaded('downloads')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
