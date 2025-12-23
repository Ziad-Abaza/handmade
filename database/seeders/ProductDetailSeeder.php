<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductDetailSeeder extends Seeder
{
    public function run()
    {
        $products = Product::pluck('id')->toArray();

        if (empty($products)) {
            $this->command->info('You have no products yet.');
            return;
        }

        $productDetails = [];
        $productsData = Product::with('categories')->get();
        
        // Common variations for different product types
        $variations = [
            'jewelry' => [
                'materials' => ['Sterling Silver', '14K Gold Fill', 'Rose Gold Plated', 'Vermeil', 'Surgical Steel'],
                'sizes' => ['One Size'],
                'colors' => ['Silver', 'Gold', 'Rose Gold', 'Black', 'Antique'],
                'price_multiplier' => [1, 1.2, 1.5, 1.8, 2.2],
                'stock' => [1, 2, 3, 5, 10]
            ],
            'ceramics' => [
                'materials' => ['Stoneware', 'Porcelain', 'Earthenware'],
                'sizes' => ['Small', 'Medium', 'Large'],
                'colors' => ['Glazed White', 'Navy Blue', 'Sage Green', 'Terracotta', 'Speckled'],
                'price_multiplier' => [1, 1.1, 1.25],
                'stock' => [3, 5, 8, 10]
            ],
            'textiles' => [
                'materials' => ['Organic Cotton', 'Linen', 'Wool', 'Alpaca', 'Silk'],
                'sizes' => ['Throw (50x60")', 'Lap (40x50")', 'Bed (90x90")'],
                'colors' => ['Natural', 'Indigo', 'Charcoal', 'Cream', 'Sage'],
                'price_multiplier' => [1, 1.15, 1.3, 1.5],
                'stock' => [2, 3, 5]
            ],
            'leather' => [
                'materials' => ['Full-Grain Leather', 'Top-Grain Leather', 'Vegetable-Tanned Leather'],
                'sizes' => ['Small', 'Medium', 'Large'],
                'colors' => ['Chestnut', 'Black', 'Cognac', 'Olive', 'Natural'],
                'price_multiplier' => [1, 1.2, 1.4],
                'stock' => [1, 2, 3, 5]
            ],
            'food' => [
                'materials' => ['Organic', 'Vegan', 'Gluten-Free', 'Paleo'],
                'sizes' => ['4oz', '8oz', '16oz', 'Gift Set'],
                'colors' => ['N/A'],
                'price_multiplier' => [1, 1.5, 2, 2.5],
                'stock' => [5, 8, 10, 15]
            ]
        ];

        foreach ($productsData as $product) {
            // Determine product type based on categories
            $productType = 'jewelry'; // default
            $categories = $product->categories->pluck('category_name')->toArray();
            
            if (in_array('Ceramics & Pottery', $categories)) {
                $productType = 'ceramics';
            } elseif (in_array('Textiles & Weaving', $categories)) {
                $productType = 'textiles';
            } elseif (in_array('Leather Goods', $categories)) {
                $productType = 'leather';
            } elseif (in_array('Artisan Foods', $categories) || in_array('Natural Soaps', $categories)) {
                $productType = 'food';
            }
            
            $var = $variations[$productType];
            $variationCount = $productType === 'jewelry' ? rand(2, 4) : rand(1, 3);
            
            for ($i = 0; $i < $variationCount; $i++) {
                $priceMultiplier = $var['price_multiplier'][array_rand($var['price_multiplier'])];
                $basePrice = $product->base_price * $priceMultiplier;
                
                $productDetails[] = [
                    'product_id' => $product->id,
                    'size' => $var['sizes'][array_rand($var['sizes'])],
                    'color' => $var['colors'][array_rand($var['colors'])],
                    'price' => round($basePrice, 2),
                    'discount' => (rand(0, 10) > 7) ? rand(5, 20) : 0, // 30% chance of having a discount
                    'stock' => $var['stock'][array_rand($var['stock'])],
                    'material' => $var['materials'][array_rand($var['materials'])],
                    'created_at' => $product->created_at,
                    'updated_at' => now(),
                ];
            }
        }

        ProductDetail::insert($productDetails);

        foreach (ProductDetail::all() as $productDetail) {
            $imageCount = rand(1, 3);
            for ($j = 0; $j < $imageCount; $j++) {
                $randomImage = 'product(' . rand(1, 6) . ').jpeg';

                $productDetail->addMedia(public_path("images/test/{$randomImage}"))
                    ->preservingOriginal()
                    ->toMediaCollection('product_images');
            }
        }

        $this->command->info('Product details seeded successfully.');
    }
}
