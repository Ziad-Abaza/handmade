<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run()
    {
        $vendors = [
            [
                'brand_name' => 'Elite Electronics',
                'description' => 'Your primary source for the latest gadgets, high-performance laptops, and premium audio equipment.',
                'phone' => '+966501112223',
                'status' => 'active',
                'image_url' => 'https://images.unsplash.com/photo-1573164713988-8665fc963095?w=500',
            ],
            [
                'brand_name' => 'Golden Hour Jewelry',
                'description' => 'Exquisite handmade jewelry featuring semi-precious stones and pure silver, designed for your special moments.',
                'phone' => '+966509990001',
                'status' => 'active',
                'image_url' => 'https://images.unsplash.com/photo-1617038220319-276d3cfab638?w=500',
            ],
            [
                'brand_name' => 'Desert Clay Pottery',
                'description' => 'Unique ceramic pieces inspired by the desert landscape. Functional art for your kitchen and living room.',
                'phone' => '+966502223334',
                'status' => 'active',
                'image_url' => 'https://images.unsplash.com/photo-1506806732259-39c2d0268443?w=500',
            ],
            [
                'brand_name' => 'Organic Blooms & Oils',
                'description' => 'All-natural bath and body products, hand-poured candles, and organic essential oils for your well-being.',
                'phone' => '+966508889990',
                'status' => 'active',
                'image_url' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=500',
            ],
            [
    'brand_name' => 'The Leather Lab',
    'description' => 'Custom leather accessories from wallets to travel bags. Quality craftsmanship and full-grain leather only.',
    'phone' => '+966505556667',
    'status' => 'active',
    'image_url' => 'https://media1.s-nbcnews.com/i/newscms/2015_30/697596/leather-factory-today-tease-150725_c4e4b23f0761bbe9b3d66322d9d63cc4.jpg',
],

[
    'brand_name' => 'Pure Silk Textiles',
    'description' => 'Bespoke handwoven fabrics and luxury scarves. We blend traditional techniques with modern fashion trends.',
    'phone' => '+966507778889',
    'status' => 'active',
    'image_url' => 'https://images.unsplash.com/photo-1528459105426-b9548367069b?auto=format&fit=crop&q=80&w=800',
],

[
    'brand_name' => 'Oak & Iron Woodworking',
    'description' => 'Handcrafted furniture and home decor made from sustainable solid wood. Beauty that lasts for generations.',
    'phone' => '+966504445556',
    'status' => 'active',
    'image_url' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&q=80&w=800',
],
        ];

        foreach ($vendors as $vendorData) {
            $imageUrl = $vendorData['image_url'];
            unset($vendorData['image_url']);

            $vendor = Vendor::create($vendorData);

            try {
                $vendor->addMediaFromUrl($imageUrl)
                    ->preservingOriginal()
                    ->toMediaCollection('vendor_images');
            } catch (\Exception $e) {
                \Log::warning("Could not upload image for vendor: " . $vendorData['brand_name']);
            }
        }
    }
}