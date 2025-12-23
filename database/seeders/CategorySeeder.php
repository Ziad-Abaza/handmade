<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // General Categories
            ['category_name' => 'Electronics', 'description' => 'Devices and gadgets', 'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=800'],
            ['category_name' => 'Clothing', 'description' => 'Fashion and apparel', 'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=800'],
            ['category_name' => 'Books', 'description' => 'Books and literature', 'image' => 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=800'],
            ['category_name' => 'Home & Kitchen', 'description' => 'Appliances and decor', 'image' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?w=800'],
            ['category_name' => 'Sports', 'description' => 'Fitness and recreation', 'image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800'],

            // Handmade Jewelry
            ['category_name' => 'Handmade Jewelry', 'description' => 'Unique, handcrafted necklaces, bracelets, earrings, and rings', 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=800'],
            
            // Artisan Home Decor
            ['category_name' => 'Ceramics & Pottery', 'description' => 'Hand-thrown pottery and decorative ceramic pieces', 'image' => 'https://images.unsplash.com/photo-1565191999001-551c187427bb?w=800'],
            ['category_name' => 'Textiles & Weaving', 'description' => 'Handwoven fabrics and textile art', 'image' => 'https://images.unsplash.com/photo-1606103920295-9a091573f160?w=800'],
            ['category_name' => 'Woodworking', 'description' => 'Handcrafted wooden furniture and decor items', 'image' => 'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?w=800'],
            ['category_name' => 'Glass Art', 'description' => 'Hand-blown glassware and sculptures', 'image' => 'https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?w=800'],
            
            // Fashion & Accessories
['category_name' => 'Leather Goods', 'description' => 'Handcrafted bags, wallets, and leather accessories', 'image' => 'https://images.unsplash.com/photo-1547949003-9792a18a2601?w=800'],
            ['category_name' => 'Knit & Crochet', 'description' => 'Hand-knitted and crocheted clothing and accessories', 'image' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?w=800'],
            ['category_name' => 'Artisan Clothing', 'description' => 'Handmade apparel and fashion items', 'image' => 'https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=800'],
            
            // Art & Collectibles
            ['category_name' => 'Paintings & Drawings', 'description' => 'Original artwork and illustrations', 'image' => 'https://images.unsplash.com/photo-1513364776144-60967b0f800f?w=800'],
['category_name' => 'Sculpture', 'description' => 'Handcrafted three-dimensional art pieces', 'image' => 'https://images.unsplash.com/photo-1542385151-efd9000785a0?w=800'],
            ['category_name' => 'Paper Crafts', 'description' => 'Handmade paper goods and stationery', 'image' => 'https://images.unsplash.com/photo-1520004434532-668416a08753?w=800'],
            
            // Bath & Body
            ['category_name' => 'Natural Soaps', 'description' => 'Handmade soaps with natural ingredients', 'image' => 'https://images.unsplash.com/photo-1600857062241-98e5dba7f214?w=800'],
            ['category_name' => 'Candles & Scents', 'description' => 'Hand-poured candles and home fragrances', 'image' => 'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?w=800'],
            
            // Food & Beverage
            ['category_name' => 'Artisan Foods', 'description' => 'Handcrafted jams, chocolates, and gourmet goods', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800'],
            
            // For Kids
            ['category_name' => 'Handmade Toys', 'description' => 'Unique, safe toys crafted with care', 'image' => 'https://images.unsplash.com/photo-1558877385-81a1c7e67d72?w=800'],
            
            // Pet Accessories
            ['category_name' => 'Pet Accessories', 'description' => 'Handmade items for your furry friends', 'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=800'],
            
            // Workshops & Kits
['category_name' => 'Craft Kits', 'description' => 'DIY kits for various crafts and projects', 'image' => 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?w=800'],
            ['category_name' => 'Workshops', 'description' => 'Learn from master artisans', 'image' => 'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?w=800'],
        ];

        foreach ($categories as $categoryData) {
            // استخراج رابط الصورة وفصله عن البيانات الأساسية
            $imageUrl = $categoryData['image'];
            unset($categoryData['image']);

            // إنشاء التصنيف
            $category = Category::create($categoryData);

            // جلب الصورة من الرابط وحفظها في Media Collection
            $category->addMediaFromUrl($imageUrl)
                ->preservingOriginal()
                ->toMediaCollection('category_images');
        }
    }
}