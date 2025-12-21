<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get the current user's cart with items.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        
        return response()->json([
            'success' => true,
            'data' => new CartResource($cart->load(['items.product', 'items.vendor'])),
            'message' => 'Cart retrieved successfully.'
        ]);
    }

    /**
     * Add items to the cart or update existing items.
     */
    public function store(CartRequest $request): JsonResponse
    {
        // Log the start of cart operation
        Log::info('Starting cart operation', [
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->all()
        ]);
        
        $validated = $request->validated();
        
        Log::debug('Validated cart request data', [
            'validated_data' => $validated,
            'session_id' => $request->hasSession() ? $request->session()->getId() : 'no-session'
        ]);
        
        return DB::transaction(function () use ($validated, $request) {
            $cart = $this->getOrCreateCart($request);
            
            // Process each item in the request
            foreach ($validated['items'] as $index => $itemData) {
                Log::debug('Processing cart item', [
                    'item_index' => $index,
                    'item_data' => $itemData
                ]);
                
                $product = Product::findOrFail($itemData['product_id']);
                $price = 0;
                
                Log::debug('Found product', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'has_price' => isset($product->price)
                ]);
                
                // Determine price from product detail if available
                if (isset($itemData['options']['product_detail_id'])) {
                    $detail = \App\Models\ProductDetail::find($itemData['options']['product_detail_id']);
                    if ($detail) {
                        $price = $detail->price;
                        Log::debug('Using product detail price', [
                            'product_detail_id' => $detail->id,
                            'price' => $price
                        ]);
                    } else {
                        Log::warning('Product detail not found', [
                            'product_detail_id' => $itemData['options']['product_detail_id']
                        ]);
                    }
                }
                
                // Fallback to product price if detail not found or no detail specified
                if ($price == 0) {
                    if (isset($product->price)) {
                        $price = $product->price;
                        Log::debug('Using product base price', [
                            'product_id' => $product->id,
                            'price' => $price
                        ]);
                    } else {
                        Log::error('No valid price found for product', [
                            'product_id' => $product->id,
                            'has_price' => isset($product->price)
                        ]);
                        throw new \Exception('No valid price found for product ' . $product->id);
                    }
                }
                
                // Create or update cart item
                $existingItem = $cart->items()
                    ->where('product_id', $product->id)
                    ->where('vendor_id', $product->vendor_id)
                    ->first();
                
                if ($existingItem) {
                    // Update existing item - increment quantity
                    $existingItem->quantity += $itemData['quantity'];
                    $existingItem->save();
                    Log::debug('Updated existing cart item quantity', [
                        'item_id' => $existingItem->id,
                        'old_quantity' => $existingItem->quantity - $itemData['quantity'],
                        'new_quantity' => $existingItem->quantity
                    ]);
                } else {
                    // Create new cart item
                    $cart->items()->create([
                        'product_id' => $product->id,
                        'vendor_id' => $product->vendor_id,
                        'quantity' => $itemData['quantity'],
                        'price' => $price,
                        'options' => $itemData['options'] ?? [],
                        'note' => $itemData['note'] ?? null,
                    ]);
                    Log::debug('Created new cart item', [
                        'product_id' => $product->id,
                        'vendor_id' => $product->vendor_id,
                        'quantity' => $itemData['quantity'],
                        'price' => $price
                    ]);
                }
            }
            
            // Update cart details
            $cart->fill($request->only([
                'shipping_method',
                'shipping_address',
                'billing_address',
                'same_as_billing'
            ]));
            
            // If same as billing, copy shipping address to billing address
            if ($request->boolean('same_as_billing') && $request->filled('shipping_address')) {
                $cart->billing_address = $request->shipping_address;
            }
            
            $cart->save();
            
            Log::debug('Cart saved, refreshing totals', [
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'session_id' => $cart->session_id,
                'item_count' => $cart->items()->count()
            ]);
            
            $cart->refreshTotals();
            
            $response = [
                'success' => true,
                'data' => new CartResource($cart->load(['items.product', 'items.vendor'])),
                'message' => 'Cart updated successfully.'
            ];
            
            Log::info('Cart operation completed', [
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'item_count' => $cart->items()->count(),
                'total' => $cart->total
            ]);
            
            return response()->json($response);
        });
    }

    /**
     * Update a cart item's quantity.
     */
    public function updateItem(Request $request, CartItem $item): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);
        
        $item->update(['quantity' => $request->quantity]);
        $item->cart->refreshTotals();
        
        return response()->json([
            'success' => true,
            'data' => new CartResource($item->cart->load(['items.product', 'items.vendor'])),
            'message' => 'Cart item updated successfully.'
        ]);
    }

    /**
     * Remove a cart item.
     */
    public function removeItem(CartItem $item): JsonResponse
    {
        $cart = $item->cart;
        $item->delete();
        $cart->refreshTotals();
        
        return response()->json([
            'success' => true,
            'data' => new CartResource($cart->load(['items.product', 'items.vendor'])),
            'message' => 'Item removed from cart.'
        ]);
    }

    /**
     * Clear the cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();
        $cart->update([
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'shipping' => 0,
            'total' => 0
        ]);
        
        return response()->json([
            'success' => true,
            'data' => new CartResource($cart->load('items')),
            'message' => 'Cart cleared successfully.'
        ]);
    }

    /**
     * Get the number of items in the cart.
     */
    public function count(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request);
        $count = $cart->items()->sum('quantity');
        
        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
            'message' => 'Cart item count retrieved successfully.'
        ]);
    }

    /**
     * Get or create a cart for the current user/session.
     */
    protected function getOrCreateCart(Request $request): Cart
    {
        $query = Cart::with(['items.product', 'items.vendor']);
        
        if (Auth::check()) {
            // For authenticated users, try to find their cart
            $cart = $query->where('user_id', Auth::id())->first();
            
            if (!$cart) {
                // If no cart exists, create one
                $cartData = ['user_id' => Auth::id()];
                // Only set session_id if we're in a web context
                if ($request->hasSession()) {
                    $cartData['session_id'] = $request->session()->getId();
                } else {
                    // For API requests without session, use a unique identifier
                    $cartData['session_id'] = 'api_' . Str::random(40);
                }
                
                $cart = Cart::create($cartData);
            }
        } else {
            // For guests, use session ID or API token
            if ($request->hasSession()) {
                $sessionId = $request->session()->getId();
            } else {
                // For API requests without session, use the API token or create a unique ID
                $sessionId = $request->bearerToken() ?? 'guest_' . Str::random(40);
            }
            
            $cart = $query->where('session_id', $sessionId)->first();
            
            if (!$cart) {
                $cart = Cart::create(['session_id' => $sessionId]);
            }
        }
        
        return $cart;
    }
}