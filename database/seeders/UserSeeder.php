<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مستخدمين أساسيين ببيانات محددة
        $users = [
            [
                'name' => 'أحمد علي',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'phone' => '0501234567',
                'gender' => 'male',
                'is_active' => true,
                'role' => 'admin',
                'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'سارة المنصور',
                'email' => 'vendor@example.com',
                'password' => Hash::make('password'),
                'phone' => '0507654321',
                'gender' => 'female',
                'is_active' => true,
                'role' => 'vendor',
                'image' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400&h=400&fit=crop',
            ],
        ];

        // بيانات عشوائية واقعية لـ 10 مستخدمين إضافيين
        $names = [
            ['name' => 'محمد القحطاني', 'gender' => 'male'],
            ['name' => 'ليلى عبدالله', 'gender' => 'female'],
            ['name' => 'عمر الفاروق', 'gender' => 'male'],
            ['name' => 'نورة الشهري', 'gender' => 'female'],
            ['name' => 'خالد العتيبي', 'gender' => 'male'],
            ['name' => 'ريم التميمي', 'gender' => 'female'],
            ['name' => 'ياسر جلال', 'gender' => 'male'],
            ['name' => 'هند صبري', 'gender' => 'female'],
            ['name' => 'فهد البقمي', 'gender' => 'male'],
            ['name' => 'مريم فوزي', 'gender' => 'female'],
        ];

        foreach ($names as $i => $data) {
            $users[] = [
                'name' => $data['name'],
                'email' => "user" . ($i + 1) . "@example.com",
                'password' => Hash::make('password'),
                'phone' => '05500000' . $i,
                'gender' => $data['gender'],
                'is_active' => true,
                'role' => 'user',
                // استخدام خدمة صور شخصية تعتمد على الاسم لضمان عدم حدوث 404
                'image' => "https://ui-avatars.com/api/?name=" . urlencode($data['name']) . "&background=random&color=fff&size=512",
            ];
        }

        foreach ($users as $userData) {
            $imageUrl = $userData['image'];
            unset($userData['image']);

            $user = User::create($userData);

            // جلب الصورة من الرابط وحفظها
            try {
                $user->addMediaFromUrl($imageUrl)
                    ->preservingOriginal()
                    ->toMediaCollection('profile_pictures');
            } catch (\Exception $e) {
                \Log::error("Failed to upload profile picture for: " . $user->name);
            }
        }
    }
}