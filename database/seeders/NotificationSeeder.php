<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = \App\Models\User::pluck('id')->toArray();

        foreach ($userIds as $userId) {
            DB::table('notifications')->insert([
                [
                    'id' => Str::uuid()->toString(),
                    'type' => 'App\Notifications\GeneralNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $userId,
                    'data' => json_encode([
                        'title' => 'Welcome!',
                        'message' => 'Welcome to the Handmade platform.',
                        'action_url' => '/dashboard'
                    ]),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'type' => 'App\Notifications\OrderUpdateNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $userId,
                    'data' => json_encode([
                        'title' => 'Order Update',
                        'message' => 'Your order #12345 has been shipped!',
                        'order_id' => 12345
                    ]),
                    'read_at' => null,
                    'created_at' => now()->subDay(),
                    'updated_at' => now()->subDay(),
                ]
            ]);
        }
    }
}
