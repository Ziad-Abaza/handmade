<?php

namespace App\Http\Controllers\Page;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ProductCollectionController extends Controller
{
    /**
     * قائمة المنتجات الأكثر طلباً مع pagination (أول detail فقط)
     */
    public function mostDemanded(Request $request)
    {
        $products = Product::with(['details' => function ($query) {
                    $query->select('id', 'product_id', 'price', 'discount')->limit(1);
                },
                'vendor:id,brand_name'
            ])
            ->whereHas('reviews')
            ->orderByDesc(function ($query) {
                return $query->selectRaw('COALESCE(AVG(reviews.rating), 0)')
                    ->from('reviews')
                    ->whereColumn('reviews.product_id', 'products.id');
            })
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $products,
        ]);
    }

    /**
     * قائمة المنتجات المروّجة مع pagination (أول detail وأول pivot من vendor_promotion)
     */
    public function promoted(Request $request)
    {
        $currentDate = Carbon::now();

        $products = Product::join('vendors', 'products.vendor_id', '=', 'vendors.id')
            ->join('vendor_promotion', 'vendors.id', '=', 'vendor_promotion.vendor_id')
            ->join('promotions', 'vendor_promotion.promotion_id', '=', 'promotions.id')
            ->where('vendor_promotion.status', 'approved')
            ->where('vendor_promotion.start_date', '<=', $currentDate)
            ->where('vendor_promotion.end_date', '>=', $currentDate)
            ->select('products.*', 'vendor_promotion.*')
            ->with(['details' => function ($query) {
                    $query->select('id', 'product_id', 'price', 'discount')->limit(1);
                },
                'vendor:id,brand_name'
            ])
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $products,
        ]);
    }

    /**
     * قائمة المنتجات للمتاجر المفضلة (followed) مع pagination (أول detail فقط)
     */
    public function followed(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $user = Auth::user();
        $vendorIds = $user->followedVendors->pluck('id');

        $products = Product::with(['details' => function ($query) {
                    $query->select('id', 'product_id', 'price', 'discount')->limit(1);
                },
                'vendor:id,brand_name'
            ])
            ->whereIn('vendor_id', $vendorIds)
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $products,
        ]);
    }

    /**
     * قائمة المنتجات ضمن تصنيف معين مع pagination (أول detail فقط + أول pivot من category_product)
     */
    public function trendingByCategory(Request $request, $categoryId)
    {
        $category = Category::findOrFail($categoryId);

        // الحصول على المنتجات مع أول detail
        $query = $category->products()->with(['details' => function ($query) {
                    $query->select('id', 'product_id', 'price', 'discount')->limit(1);
                }]);

        $paginated = $query->paginate(15);

        // تحويل البيانات لتضمين أول pivot
        $paginated->getCollection()->transform(function ($product) {
            $detail = $product->details->first();
            return [
                'product_id'    => $product->id,
                'product_name'  => $product->product_name,
                'product_image' => $detail?->getImageUrl() ?? asset('images/product-placeholder.jpg'),
                'price'         => $detail?->price,
                'discount'      => $detail?->discount,
                'pivot'         => $product->pivot->toArray(),
            ];
        });

        return response()->json([
            'status'   => true,
            'category' => [
                'id'   => $category->id,
                'name' => $category->category_name,
            ],
            'data'     => $paginated,
        ]);
    }

    public function getVendorProducts()
    {
        return Product::where('vendor_id', 6)
            ->with(['details' => function ($query) {
                $query->select('id', 'product_id', 'price', 'discount');
            }])
            ->get()
            ->map(function ($product) {
                $detail = $product->details->first();
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_image' => $detail?->getImageUrl() ?? asset('images/product-placeholder.jpg'),
                    'vendor_image' => $product->vendor?->getImageUrl() ?? asset('images/vendor-placeholder.jpg'),
                    'vendor_id' => $product->vendor?->id,
                    'price' => $detail?->price ,
                    'discount' => $detail?->discount ,
                ];
            });
    }
}
