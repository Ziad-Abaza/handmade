<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlist_items')
            ->withPivot(['note'])
            ->withTimestamps();
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function setAsDefault(): void
    {
        // Remove default status from other wishlists
        $this->user->wishlists()
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
    }

    protected static function booted()
    {
        static::created(function ($wishlist) {
            // If this is the first wishlist for the user, make it default
            if ($wishlist->user->wishlists()->count() === 1) {
                $wishlist->update(['is_default' => true]);
            }
        });

        static::deleting(function ($wishlist) {
            // If deleting the default wishlist, set another one as default
            if ($wishlist->is_default) {
                $newDefault = $wishlist->user->wishlists()
                    ->where('id', '!=', $wishlist->id)
                    ->first();
                
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }
        });
    }
}