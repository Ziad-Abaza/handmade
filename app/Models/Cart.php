<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Cart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'session_id',
        'subtotal',
        'discount',
        'tax',
        'shipping',
        'total',
        'coupons',
        'shipping_address',
        'billing_address',
        'shipping_method',
        'completed_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total' => 'decimal:2',
        'coupons' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($cart) {
            if (empty($cart->session_id)) {
                $cart->session_id = session()->getId() ?? (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the orders associated with this cart.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the items for the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(array $itemData): CartItem
    {
        $item = $this->items()->updateOrCreate(
            [
                'product_id' => $itemData['product_id'],
                'vendor_id' => $itemData['vendor_id'],
            ],
            [
                'quantity' => $itemData['quantity'] ?? 1,
                'price' => $itemData['price'] ?? 0,
                'options' => $itemData['options'] ?? [],
                'note' => $itemData['note'] ?? null,
            ]
        );

        $this->refreshTotals();
        
        return $item;
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $itemId): bool
    {
        $this->items()->where('id', $itemId)->delete();
        return $this->refreshTotals();
    }

    /**
     * Update item quantity.
     */
    public function updateQuantity(int $itemId, int $quantity): ?CartItem
    {
        $item = $this->items()->find($itemId);
        
        if ($item) {
            $item->update(['quantity' => $quantity]);
            $this->refreshTotals();
        }
        
        return $item;
    }

    /**
     * Apply a coupon to the cart.
     */
    public function applyCoupon(string $code, float $discount, string $type = 'fixed'): bool
    {
        $coupons = $this->coupons ?? [];
        $coupons[$code] = [
            'code' => $code,
            'discount' => $discount,
            'type' => $type,
            'applied_at' => now()->toDateTimeString(),
        ];

        $this->coupons = $coupons;
        return $this->refreshTotals();
    }

    /**
     * Remove a coupon from the cart.
     */
    public function removeCoupon(string $code): bool
    {
        $coupons = $this->coupons ?? [];
        
        if (isset($coupons[$code])) {
            unset($coupons[$code]);
            $this->coupons = $coupons;
            return $this->refreshTotals();
        }
        
        return false;
    }

    /**
     * Recalculate cart totals.
     */
    public function refreshTotals(): bool
    {
        $totals = $this->items()
            ->select(DB::raw('SUM(price * quantity) as subtotal, SUM(discount * quantity) as discount, SUM(tax * quantity) as tax'))
            ->first();

        $this->subtotal = $totals->subtotal ?? 0;
        $this->discount = $totals->discount ?? 0;
        $this->tax = $totals->tax ?? 0;
        $this->total = $this->subtotal - $this->discount + $this->tax + $this->shipping;
        
        return $this->save();
    }

    /**
     * Get the cart for the current session or user.
     */
    public static function current(): ?self
    {
        if (auth()->check()) {
            return static::firstOrCreate(['user_id' => auth()->id()]);
        }
        
        return static::firstOrCreate(['session_id' => session()->getId()]);
    }

    /**
     * Merge a session cart with a user cart.
     */
    public function mergeWithSessionCart(string $sessionId): void
    {
        $sessionCart = self::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->first();

        if ($sessionCart) {
            foreach ($sessionCart->items as $item) {
                $this->addItem([
                    'product_id' => $item->product_id,
                    'vendor_id' => $item->vendor_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'options' => $item->options,
                ]);
            }
            
            $sessionCart->delete();
        }
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): bool
    {
        $this->items()->delete();
        return $this->refreshTotals();
    }

    /**
     * Get the number of items in the cart.
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Check if the cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items_count === 0;
    }

    /**
     * Get the formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 2);
    }

    /**
     * Get the formatted total.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }
}
