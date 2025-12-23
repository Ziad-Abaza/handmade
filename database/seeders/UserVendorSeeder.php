<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class UserVendorSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id')->toArray();
        $vendors = Vendor::pluck('id')->toArray();

        if (empty($users) || empty($vendors)) {
            $this->command->warn('No users or vendors found. Skipping UserVendorSeeder.');
            return;
        }

        $userVendorData = [];

        foreach ($users as $userId) {
            $randomVendorIds = (array) array_rand(array_flip($vendors), min(2, count($vendors)));

            foreach ($randomVendorIds as $vendorId) {
                $userVendorData[] = [
                    'user_id' => $userId,
                    'vendor_id' => $vendorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('user_vendor')->insert($userVendorData);
    }
}