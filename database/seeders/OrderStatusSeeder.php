<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Pending',
                'color' => '#ffc107',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Processing',
                'color' => '#17a2b8',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shipped',
                'color' => '#007bff',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Delivered',
                'color' => '#28a745',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cancelled',
                'color' => '#dc3545',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Refunded',
                'color' => '#6c757d',
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('order_statuses')->insert($statuses);
    }
}
