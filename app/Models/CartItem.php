<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cart_id',
        'product_id',
        'vendor_id',
        'quantity',
        'price',
        'discount',
        'tax',
        'options',
        'note',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'options' => 'array',
    ];

    protected $with = ['product', 'vendor'];

    /**
     * Get the cart that owns the cart item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product that owns the cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the vendor that owns the cart item.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Calculate the total for this cart item.
     */
    public function calculateTotal(): float
    {
        return ($this->price - $this->discount + $this->tax) * $this->quantity;
    }

    /**
     * Update the cart item's price from the product.
     */
    public function updatePriceFromProduct(): void
    {
        if ($this->product) {
            $this->price = $this->product->price;
            // You can add logic to apply discounts based on promotions, etc.
            $this->save();
        }
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2);
    }

    /**
     * Get the formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    /**
     * Check if the cart item is from a specific vendor.
     */
    public function isFromVendor(int $vendorId): bool
    {
        return $this->vendor_id === $vendorId;
    }
}
