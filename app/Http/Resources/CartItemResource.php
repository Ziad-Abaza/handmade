<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
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
            'cart_id' => $this->cart_id,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->product_name,
                'slug' => $this->product->slug,
                'price' => (float) $this->price,
                'sale_price' => $this->product->sale_price ? (float) $this->product->sale_price : null,
                'image' => $this->getProductImage(),
                'in_stock' => $this->product->in_stock,
                'stock_quantity' => $this->product->stock_quantity,
            ],
            'vendor' => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'slug' => $this->vendor->slug,
            ],
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'options' => $this->options ?? [],
            'note' => $this->note,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'meta' => [
                'formatted_price' => number_format($this->price, 2),
                'formatted_discount' => number_format($this->discount, 2),
                'formatted_tax' => number_format($this->tax, 2),
                'formatted_total' => number_format($this->total, 2),
                'is_available' => $this->product->in_stock && $this->product->stock_quantity >= $this->quantity,
            ]
        ];
    }

    protected function getProductImage()
    {
        // Check if we have a specific product detail selected
        if (isset($this->options['product_detail_id'])) {
            $detail = \App\Models\ProductDetail::find($this->options['product_detail_id']);
            if ($detail) {
                return $detail->getImageUrl();
            }
        }

        // Fallback: Try to get the first detail's image if available
        $firstDetail = $this->product->details()->first();
        if ($firstDetail) {
            return $firstDetail->getImageUrl();
        }

        return asset('images/product-placeholder.jpg');
    }
}
