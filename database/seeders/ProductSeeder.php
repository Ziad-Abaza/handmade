<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Category;
use App\Models\Vendor;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $vendors = Vendor::all();

        if ($categories->isEmpty() || $vendors->isEmpty()) {
            $this->command->error("Please seed Categories and Vendors first!");
            return;
        }

        $productsData = [
            // --- Leather Goods (The Leather Lab) ---
            ['name' => 'Leather Messenger Bag', 'desc' => 'Handcrafted full-grain leather bag perfect for laptops.', 'cat' => 'Leather Goods', 'vendor' => 'The Leather Lab', 'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=800'],
            ['name' => 'Premium Leather Wallet', 'desc' => 'Slim bifold wallet made from vegetable-tanned leather.', 'cat' => 'Leather Goods', 'vendor' => 'The Leather Lab', 'img' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800'],
            ['name' => 'Travel Leather Duffel', 'desc' => 'Spacious weekender bag made from premium cowhide.', 'cat' => 'Leather Goods', 'vendor' => 'The Leather Lab', 'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800'],
            ['name' => 'Leather Journal Cover', 'desc' => 'Refillable leather cover for A5 notebooks.', 'cat' => 'Leather Goods', 'vendor' => 'The Leather Lab', 'img' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800'],
            ['name' => 'Minimalist Leather Belt', 'desc' => 'Classic hand-stitched leather belt with brass buckle.', 'cat' => 'Leather Goods', 'vendor' => 'The Leather Lab', 'img' => 'https://images.unsplash.com/photo-1624222247344-550fb8ecfe31?w=800'],

            // --- Electronics (Elite Electronics) ---
            ['name' => 'Mechanical Gaming Keyboard', 'desc' => 'RGB backlit mechanical keyboard with blue switches.', 'cat' => 'Electronics', 'vendor' => 'Elite Electronics', 'img' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=800'],
            ['name' => 'Smartphone Pro Max', 'desc' => 'High-end smartphone with professional camera system.', 'cat' => 'Electronics', 'vendor' => 'Elite Electronics', 'img' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800'],
            ['name' => 'Noise Cancelling Headphones', 'desc' => 'Wireless over-ear headphones with superior sound.', 'cat' => 'Electronics', 'vendor' => 'Elite Electronics', 'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800'],
            ['name' => 'Ultra-thin Laptop 14"', 'desc' => 'Powerful performance in a sleek aluminum chassis.', 'cat' => 'Electronics', 'vendor' => 'Elite Electronics', 'img' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800'],
            ['name' => '4K Action Camera', 'desc' => 'Waterproof camera for your outdoor adventures.', 'cat' => 'Electronics', 'vendor' => 'Elite Electronics', 'img' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=800'],

            // --- Ceramics & Pottery (Desert Clay Pottery) ---
            ['name' => 'Minimalist Ceramic Vase', 'desc' => 'Elegant hand-thrown ceramic vase for modern homes.', 'cat' => 'Ceramics & Pottery', 'vendor' => 'Desert Clay Pottery', 'img' => 'https://images.unsplash.com/photo-1578749553370-4bc3b1669562?w=800'],
            ['name' => 'Artisan Coffee Mug', 'desc' => 'Large handmade mug with unique blue glaze.', 'cat' => 'Ceramics & Pottery', 'vendor' => 'Desert Clay Pottery', 'img' => 'https://images.unsplash.com/photo-1514228742587-6b1558fbed20?w=800'],
            ['name' => 'Speckled Pasta Bowls', 'desc' => 'Set of 4 hand-glazed stoneware bowls.', 'cat' => 'Ceramics & Pottery', 'vendor' => 'Desert Clay Pottery', 'img' => 'https://images.unsplash.com/photo-1610701596007-11502861dcfa?w=800'],
            ['name' => 'Terracotta Planter', 'desc' => 'Breathable clay pot perfect for indoor succulents.', 'cat' => 'Ceramics & Pottery', 'vendor' => 'Desert Clay Pottery', 'img' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?w=800'],
            ['name' => 'Handmade Serving Platter', 'desc' => 'Unique oval platter with rustic edges.', 'cat' => 'Ceramics & Pottery', 'vendor' => 'Desert Clay Pottery', 'img' => 'https://images.unsplash.com/photo-1574362848149-11496d93a7c7?w=800'],

            // --- Handmade Jewelry (Golden Hour Jewelry) ---
            ['name' => 'Silver Moon Earrings', 'desc' => 'Sterling silver earrings inspired by the night sky.', 'cat' => 'Handmade Jewelry', 'vendor' => 'Golden Hour Jewelry', 'img' => 'https://images.unsplash.com/photo-1535633302713-102a019a130d?w=800'],
            ['name' => 'Gold Plated Necklace', 'desc' => 'Minimalist gold chain with a delicate pendant.', 'cat' => 'Handmade Jewelry', 'vendor' => 'Golden Hour Jewelry', 'img' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=800'],
            ['name' => 'Turquoise Beaded Bracelet', 'desc' => 'Bohemian style bracelet with natural turquoise.', 'cat' => 'Handmade Jewelry', 'vendor' => 'Golden Hour Jewelry', 'img' => 'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=800'],
            ['name' => 'Hammered Silver Ring', 'desc' => 'Hand-forged sterling silver band.', 'cat' => 'Handmade Jewelry', 'vendor' => 'Golden Hour Jewelry', 'img' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=800'],
            ['name' => 'Custom Name Pendant', 'desc' => 'Personalized 18k gold plated necklace.', 'cat' => 'Handmade Jewelry', 'vendor' => 'Golden Hour Jewelry', 'img' => 'https://images.unsplash.com/photo-1590548237214-411d3315a6b7?w=800'],

            // --- Textiles & Weaving (Pure Silk Textiles) ---
            ['name' => 'Handwoven Wool Rug', 'desc' => 'Traditional geometric patterns woven with 100% organic wool.', 'cat' => 'Textiles & Weaving', 'vendor' => 'Pure Silk Textiles', 'img' => 'https://images.unsplash.com/photo-1534889156217-d34a45934b53?w=800'],
            ['name' => 'Boho Wall Hanging', 'desc' => 'Large macrame wall art for a bohemian home style.', 'cat' => 'Textiles & Weaving', 'vendor' => 'Pure Silk Textiles', 'img' => 'https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?w=800'],
            ['name' => 'Indigo Dyed Table Runner', 'desc' => 'Natural indigo hand-dyed cotton runner.', 'cat' => 'Textiles & Weaving', 'vendor' => 'Pure Silk Textiles', 'img' => 'https://images.unsplash.com/photo-1616486332302-42bcaf2aa100?w=800'],
            ['name' => 'Embroidered Throw Pillow', 'desc' => 'Soft linen pillow case with artisan embroidery.', 'cat' => 'Textiles & Weaving', 'vendor' => 'Pure Silk Textiles', 'img' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=800'],
            ['name' => 'Linen Dinner Napkins', 'desc' => 'Set of 6 pre-washed organic linen napkins.', 'cat' => 'Textiles & Weaving', 'vendor' => 'Pure Silk Textiles', 'img' => 'https://images.unsplash.com/photo-1611270629569-8b357cb88da9?w=800'],

            // --- Woodworking (Oak & Iron Woodworking) ---
            ['name' => 'Wooden Dining Table', 'desc' => 'Solid oak dining table, seats 6 people comfortably.', 'cat' => 'Woodworking', 'vendor' => 'Oak & Iron Woodworking', 'img' => 'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?w=800'],
            ['name' => 'Solid Oak Bookshelf', 'desc' => 'Sturdy hand-finished shelf for your library.', 'cat' => 'Woodworking', 'vendor' => 'Oak & Iron Woodworking', 'img' => 'https://images.unsplash.com/photo-1594620302200-9a762244a156?w=800'],
            ['name' => 'Walnut Cutting Board', 'desc' => 'End-grain cutting board with juice groove.', 'cat' => 'Woodworking', 'vendor' => 'Oak & Iron Woodworking', 'img' => 'https://images.unsplash.com/photo-1592659762303-90081d34b277?w=800'],
            ['name' => 'Live Edge Coffee Table', 'desc' => 'Natural wood slab table with steel hairpin legs.', 'cat' => 'Woodworking', 'vendor' => 'Oak & Iron Woodworking', 'img' => 'https://images.unsplash.com/photo-1532372320572-cda25653a26d?w=800'],
            ['name' => 'Wooden Wall Clock', 'desc' => 'Minimalist design made from cherry wood.', 'cat' => 'Woodworking', 'vendor' => 'Oak & Iron Woodworking', 'img' => 'https://images.unsplash.com/photo-1563861826100-9cb868fdbe1c?w=800'],

            // --- Bath & Body (Organic Blooms & Oils) ---
            ['name' => 'Natural Lavender Soap', 'desc' => 'Cold-pressed soap with essential oils and dried lavender.', 'cat' => 'Natural Soaps', 'vendor' => 'Organic Blooms & Oils', 'img' => 'https://images.unsplash.com/photo-1600857062241-98e5dba7f214?w=800'],
            ['name' => 'Scented Soy Candle', 'desc' => 'Hand-poured candle with notes of sandalwood and vanilla.', 'cat' => 'Candles & Scents', 'vendor' => 'Organic Blooms & Oils', 'img' => 'https://images.unsplash.com/photo-1602874801007-bd458bb1b8b6?w=800'],
            ['name' => 'Organic Face Cream', 'desc' => 'Natural moisturizer with aloe vera and vitamin E.', 'cat' => 'Bath & Body', 'vendor' => 'Organic Blooms & Oils', 'img' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=800'],
            ['name' => 'Rosewater Facial Mist', 'desc' => 'Pure rose hydrosol for a refreshing skin boost.', 'cat' => 'Bath & Body', 'vendor' => 'Organic Blooms & Oils', 'img' => 'https://images.unsplash.com/photo-1590156221170-cc398d09a97d?w=800'],
            ['name' => 'Himalayan Bath Salts', 'desc' => 'Relaxing soak with pink salts and essential oils.', 'cat' => 'Bath & Body', 'vendor' => 'Organic Blooms & Oils', 'img' => 'https://images.unsplash.com/photo-1560750588-73207b1ef5b8?w=800'],
        ];

        foreach ($productsData as $data) {
            $vendor = $vendors->where('brand_name', $data['vendor'])->first() ?? $vendors->random();

            // 1. إنشاء المنتج
            $product = Product::create([
                'vendor_id' => $vendor->id,
                'product_name' => $data['name'],
                'description' => $data['desc'],
            ]);

            // 2. ربط المنتج بالتصنيف
            $category = $categories->where('category_name', $data['cat'])->first() ?? $categories->random();
            $product->categories()->attach($category->id);

            // 3. إنشاء تفاصيل المنتج
            $detail = ProductDetail::create([
                'product_id' => $product->id,
                'size' => collect(['Small', 'Medium', 'Large', 'Standard'])->random(),
                'color' => collect(['Natural', 'Black', 'White', 'Blue', 'Brown'])->random(),
                'price' => rand(50, 1500),
                'discount' => rand(0, 30),
                'stock' => rand(2, 100),
                'material' => collect(['Leather', 'Wood', 'Ceramic', 'Linen', 'Metal'])->random(),
            ]);

            // 4. رفع الصورة
            try {
                $detail->addMediaFromUrl($data['img'])
                    ->preservingOriginal()
                    ->toMediaCollection('product_images');
            } catch (\Exception $e) {
                \Log::warning("Could not download image for: " . $data['name']);
            }
        }
    }
}