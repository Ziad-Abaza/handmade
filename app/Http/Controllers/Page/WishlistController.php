<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Http\Requests\WishlistItemRequest;
use App\Http\Requests\WishlistRequest;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WishlistController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display a listing of the user's wishlists.
     */
    public function index(): JsonResponse
    {
        $wishlists = Auth::user()->wishlists()
            ->withCount('items')
            ->with(['items.product.media'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'wishlists' => $wishlists,
                'default_wishlist' => $wishlists->firstWhere('is_default', true)
            ]
        ]);
    }

    /**
     * Show the form for creating a new wishlist.
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'form_data' => [
                    'name' => '',
                    'is_default' => false
                ]
            ]
        ]);
    }

    /**
     * Store a newly created wishlist.
     */
    public function store(WishlistRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $wishlist = $request->user()->wishlists()->create([
                'name' => $request->name,
                'is_default' => $request->has('is_default'),
            ]);

            if ($wishlist->is_default) {
                $wishlist->setAsDefault();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Wishlist created successfully',
                'data' => $wishlist->load('items')
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified wishlist.
     */
    public function show(Wishlist $wishlist): JsonResponse
    {
        $this->authorize('view', $wishlist);

        $wishlist->load(['items.product.media', 'items.product.vendor']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'wishlist' => $wishlist,
                'suggested_products' => $this->getSuggestedProducts($wishlist)
            ]
        ]);
    }

    /**
     * Show the form for editing the specified wishlist.
     */
    public function edit(Wishlist $wishlist): JsonResponse
    {
        $this->authorize('update', $wishlist);

        return response()->json([
            'success' => true,
            'data' => [
                'wishlist' => $wishlist
            ]
        ]);
    }

    /**
     * Update the specified wishlist.
     */
    public function update(WishlistRequest $request, Wishlist $wishlist): JsonResponse
    {
        try {
            $this->authorize('update', $wishlist);
            
            DB::beginTransaction();
            
            $wishlist->update([
                'name' => $request->name,
                'is_default' => $request->has('is_default'),
            ]);

            if ($wishlist->is_default) {
                $wishlist->setAsDefault();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Wishlist updated successfully',
                'data' => $wishlist->fresh()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified wishlist.
     */
    public function destroy(Wishlist $wishlist): JsonResponse
    {
        try {
            // Manual authorization check
            if (Auth::id() !== $wishlist->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You do not own this wishlist'
                ], 403);
            }
            
            $wishlist->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Wishlist deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a product to the wishlist.
     */
    public function addItem(Wishlist $wishlist, Request $request): JsonResponse
    {
        try {
            $this->authorize('update', $wishlist);

            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'note' => 'nullable|string|max:500',
            ]);

            $item = $wishlist->items()->create([
                'product_id' => $validated['product_id'],
                'note' => $validated['note'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist',
                'data' => $item->load('product')
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove an item from the wishlist.
     */
    public function removeItem(WishlistItem $wishlistItem): JsonResponse
    {
        try {
            $this->authorize('delete', $wishlistItem);
            
            $wishlistItem->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Item removed from wishlist'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from wishlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move an item to another wishlist.
     */
    public function moveItem(WishlistItem $wishlistItem, Wishlist $targetWishlist): JsonResponse
    {
        try {
            $this->authorize('update', $wishlistItem->wishlist);
            $this->authorize('update', $targetWishlist);

            $wishlistItem->update(['wishlist_id' => $targetWishlist->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Item moved to another wishlist',
                'data' => [
                    'item' => $wishlistItem->fresh(),
                    'new_wishlist' => $targetWishlist
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set a wishlist as default.
     */
    public function setDefault(Wishlist $wishlist): JsonResponse
    {
        try {
            $this->authorize('update', $wishlist);
            
            DB::transaction(function () use ($wishlist) {
                // First, unset any existing default
                Wishlist::where('user_id', $wishlist->user_id)
                    ->where('id', '!=', $wishlist->id)
                    ->update(['is_default' => false]);
                
                // Then set the new default
                $wishlist->update(['is_default' => true]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Wishlist set as default',
                'data' => $wishlist->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set wishlist as default',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suggested products based on wishlist items.
     */
    protected function getSuggestedProducts(Wishlist $wishlist)
    {
        if ($wishlist->items->isEmpty()) {
            return [];
        }

        $categoryIds = $wishlist->items
            ->pluck('product.category_id')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($categoryIds)) {
            return [];
        }

        return Product::whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $wishlist->items->pluck('product_id'))
            ->with('media')
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }
}
