<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\VendorUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopUserSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::with('vendor')->get();

        foreach ($shops as $shop) {
            $users = VendorUser::where('vendor_id', $shop->vendor_id)->get();

            foreach ($users as $user) {
                DB::table('shop_users')->updateOrInsert(
                    [
                        'shop_id' => $shop->id,
                        'vendor_user_id' => $user->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
