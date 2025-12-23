<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Advertisement;

class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $advertisements = [
            [
                'title' => 'Special Offer on Electronics',
                'brief' => 'Get up to 50% off on all electronic devices.',
                'content' => 'This is a limited-time offer. Don\'t miss out on our special deals for electronics.',
                'redirect_to' => 'electronics',
                'target_screen' => 'home',
                'image_url' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?q=80&w=1000', // صورة إلكترونيات
            ],
            [
                'title' => 'Handmade Jewelry Collection',
                'brief' => 'Exquisite handcrafted jewelry for every occasion',
                'content' => 'Discover our unique collection of handcrafted jewelry made with premium materials.',
                'redirect_to' => 'jewelry',
                'target_screen' => 'category',
                'image_url' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?q=80&w=1000', // صورة مجوهرات
            ],
[
    'title' => 'Artisan Home Decor',
    'brief' => 'Transform your space with unique handmade decor',
    'content' => 'Elevate your home with our carefully curated selection of artisan home decor pieces. Each item tells a story of craftsmanship and passion.',
    'redirect_to' => 'home-decor',
    'target_screen' => 'category',
    'image_url' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&q=80&w=1000', // رابط بديل يعمل 100%
],
            [
                'title' => 'Limited Edition Pottery',
                'brief' => 'Hand-thrown ceramic pieces by master artisans',
                'content' => 'Own a piece of functional art with our limited edition pottery collection.',
                'redirect_to' => 'pottery',
                'target_screen' => 'category',
                'image_url' => 'https://images.unsplash.com/photo-1565191999001-551c187427bb?q=80&w=1000', // صورة فخار
            ],
           [
    'title' => 'Custom Leather Goods',
    'brief' => 'Bespoke leather accessories made to last',
    'content' => 'Experience the luxury of custom-made leather goods. From wallets to bags, each piece is crafted with premium full-grain leather.',
    'redirect_to' => 'leather-goods',
    'target_screen' => 'category',
    'image_url' => 'https://tse3.mm.bing.net/th/id/OIP.yhENRulJ1-zlbMCl98NOnQHaE8?cb=ucfimg2&ucfimg=1&rs=1&pid=ImgDetMain&o=7&rm=3', // رابط جديد لصورة جلود حقيقية
],
            [
                'title' => 'Handwoven Textiles',
                'brief' => 'Eco-friendly textiles with traditional patterns',
                'content' => 'Wrap yourself in comfort with our collection of handwoven textiles.',
                'redirect_to' => 'textiles',
                'target_screen' => 'category',
                'image_url' => 'https://images.unsplash.com/photo-1606103920295-9a091573f160?q=80&w=1000', // صورة منسوجات
            ],
            [
                'title' => 'Artisan Workshop',
                'brief' => 'Learn from master craftsmen',
                'content' => 'Join our workshops and learn traditional crafting techniques from skilled artisans.',
                'redirect_to' => 'workshops',
                'target_screen' => 'events',
                'image_url' => 'https://images.unsplash.com/photo-1528698827591-e19ccd7bc23d?q=80&w=1000', // صورة ورشة عمل
            ],
        ];

        foreach ($advertisements as $data) {
            // استخراج الرابط وحذفه من المصفوفة قبل إنشاء السجل في قاعدة البيانات
            $imageUrl = $data['image_url'];
            unset($data['image_url']);

            $advertisement = Advertisement::create($data);

            // جلب الصورة من الرابط وإضافتها للميديا
            $advertisement->addMediaFromUrl($imageUrl)
                ->preservingOriginal()
                ->toMediaCollection('advertisement_image');
        }
    }
}