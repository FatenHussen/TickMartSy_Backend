<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreUser;
use Illuminate\Support\Facades\Hash;

class StoreUserSeeder extends Seeder
{
    public function run(): void
    {
        StoreUser::create([
            'name' => 'Admin Store',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), 
            'is_active' => true,
            'store_id' => 1,
        ]);

        StoreUser::create([
            'name' => 'Store Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'store_id' => 1,

        ]);

        StoreUser::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
            'store_id' => 1,

        ]);
    }
}
