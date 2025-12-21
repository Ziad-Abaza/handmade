<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class WishlistItem extends Model
{
    protected $fillable = [
        'wishlist_id',
        'product_id',
        'options',
        'note',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the wishlist that owns the item.
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * Get the product that is wishlisted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who owns this wishlist item.
     */
    public function user(): BelongsTo
    {
        return $this->wishlist->user();
    }

    /**
     * Move this item to another wishlist.
     */
    public function moveToWishlist(Wishlist $wishlist): bool
    {
        // Prevent moving to the same wishlist
        if ($this->wishlist_id === $wishlist->id) {
            return false;
        }

        // Check if the same product already exists in the target wishlist
        $exists = $wishlist->items()
            ->where('product_id', $this->product_id)
            ->exists();

        if ($exists) {
            return false;
        }

        $this->wishlist_id = $wishlist->id;
        return $this->save();
    }

    /**
     * Get the price of the product at the time it was added to the wishlist.
     * This could be extended to track price history.
     */
    public function getPriceAttribute()
    {
        return $this->product->price;
    }
}
