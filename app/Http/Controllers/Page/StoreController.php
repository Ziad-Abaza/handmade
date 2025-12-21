<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StoreController extends Controller
{
    public function index($id)
    {
        try {
            $vendor = Vendor::with(['regions', 'followers'])->findOrFail($id);

            $products = Product::where('vendor_id', $id)
                ->with(['details', 'reviews'])
                ->orderByDesc('created_at')
                ->get();

            $response = [
                'status' => true,
                'data' => [
                    'store' => [
                        'vendor_id' => $vendor->id,
                        'brand_name' => $vendor->brand_name,
                        'description' => $vendor->description,
                        'phone' => $vendor->phone,
                        'status' => $vendor->status->value,
                        'vendor_image' => $vendor->getImageUrl(),
                        'followers_count' => $vendor->followers->count(),
                        'regions' => $vendor->regions->map(fn($region) => [
                            'region_id' => $region->id,
                            'region_name' => $region->name,
                            'delivery_cost' => $region->pivot->delivery_cost,
                            'discount' => $region->pivot->discount,
                        ]),
                    ],
                    'products' => $products->map(function ($product) {
                        $detail = $product->details->first();
                        return [
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'description' => $product->description,
                            'price' => $detail?->price ?? 0,
                            'discount' => $detail?->discount ?? 0,
                            'stock' => $detail?->stock ?? 0,
                            'product_image' => $detail?->getImageUrl() ?? asset('images/product-placeholder.jpg'),
                            'rating' => Cache::remember('product-rating-' . $product->id, now()->addMinutes(10), fn() => $product->reviews->avg('rating') ?? 0),
                        ];
                    }),
                ],
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
