<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WishlistItemRequest;
use App\Http\Requests\WishlistRequest;
use App\Http\Resources\WishlistItemResource;
use App\Http\Resources\WishlistResource;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WishlistController extends Controller
{
    /**
     * Get all wishlists for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $wishlists = $request->user()->wishlists()
            ->withCount('items')
            ->latest()
            ->get();

        return WishlistResource::collection($wishlists);
    }

    /**
     * Store a newly created wishlist.
     */
    public function store(WishlistRequest $request): JsonResponse
    {
        $wishlist = $request->user()->wishlists()->create([
            'name' => $request->name,
            'is_default' => $request->boolean('is_default', false),
        ]);

        // If this is set as default, update other wishlists
        if ($wishlist->is_default) {
            $wishlist->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'data' => new WishlistResource($wishlist->loadCount('items')),
            'message' => 'Wishlist created successfully'
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified wishlist with its items.
     */
    public function show(Wishlist $wishlist): JsonResponse
    {
        $this->authorize('view', $wishlist);

        $wishlist->load(['items.product.details.media']);
        
        return response()->json([
            'success' => true,
            'data' => new WishlistResource($wishlist->load(['items.product'])),
            'message' => 'Wishlist retrieved successfully'
        ]);
    }

    /**
     * Update the specified wishlist.
     */
    public function update(WishlistRequest $request, Wishlist $wishlist): JsonResponse
    {
        $this->authorize('update', $wishlist);

        $wishlist->update([
            'name' => $request->name ?? $wishlist->name,
            'is_default' => $request->has('is_default') 
                ? $request->boolean('is_default')
                : $wishlist->is_default,
        ]);

        // If this is set as default, update other wishlists
        if ($wishlist->is_default) {
            $wishlist->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'data' => new WishlistResource($wishlist->loadCount('items')),
            'message' => 'Wishlist updated successfully'
        ]);
    }

    /**
     * Remove the specified wishlist.
     */
    public function destroy(Wishlist $wishlist): JsonResponse
    {
        $this->authorize('delete', $wishlist);

        // Don't allow deleting the default wishlist if it's the only one
        if ($wishlist->is_default && $wishlist->user->wishlists()->count() === 1) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Cannot delete the only wishlist. Please create another one first.',
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Wishlist deleted successfully'
        ], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Add an item to the wishlist.
     */
    public function addItem(WishlistItemRequest $request, Wishlist $wishlist): JsonResponse
    {
        $this->authorize('update', $wishlist);

        $product = Product::findOrFail($request->product_id);

        $wishlistItem = $wishlist->items()->create([
            'product_id' => $product->id,
            'options' => $request->options ?? [],
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'data' => new WishlistItemResource($wishlistItem->load('product')),
            'message' => 'Item added to wishlist'
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Update a wishlist item.
     */
    public function updateItem(
        Request $request, 
        Wishlist $wishlist, 
        WishlistItem $item
    ): JsonResponse {
        $this->authorize('update', $wishlist);
        $this->authorize('update', $item);

        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
            'options' => 'nullable|array',
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'data' => new WishlistItemResource($item->load('product')),
            'message' => 'Wishlist item updated successfully'
        ]);
    }

    /**
     * Remove an item from the wishlist.
     */
    public function removeItem(Wishlist $wishlist, WishlistItem $item): JsonResponse
    {
        $this->authorize('update', $wishlist);
        $this->authorize('delete', $item);

        $item->delete();

        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Item removed from wishlist'
        ], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Move an item to another wishlist.
     */
    public function moveItem(
        Request $request, 
        Wishlist $wishlist, 
        WishlistItem $item
    ): JsonResponse {
        $this->authorize('update', $wishlist);
        $this->authorize('update', $item);

        $targetWishlist = $request->user()->wishlists()
            ->findOrFail($request->target_wishlist_id);

        if ($targetWishlist->id === $wishlist->id) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Target wishlist must be different from the current one'
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$item->moveToWishlist($targetWishlist)) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'This product already exists in the target wishlist'
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'data' => new WishlistItemResource($item->fresh('product')),
            'message' => 'Item moved to another wishlist'
        ]);
    }

    /**
     * Get the default wishlist.
     */
    public function getDefaultWishlist(Request $request): JsonResponse
    {
        $wishlist = $request->user()->defaultWishlist();
        $wishlist->load(['items.product.details.media']);
        
        return response()->json([
            'success' => true,
            'data' => new WishlistResource($wishlist->loadCount('items')),
            'message' => 'Default wishlist retrieved successfully'
        ]);
    }

    /**
     * Check if a product is in any of the user's wishlists.
     */
    public function checkProductInWishlists(
        Request $request, 
        Product $product
    ): JsonResponse {
        $inWishlist = $product->isInWishlist($request->user()->id);
        
        return response()->json([
            'success' => true,
            'data' => ['in_wishlist' => $inWishlist],
            'message' => 'Product wishlist status retrieved'
        ]);
    }
}
