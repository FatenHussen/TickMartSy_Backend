<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default admin if not exists
        if (!Admin::where('email', 'admin@admin.com')->exists()) {
            Admin::create([
                'name' => 'System Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $this->command->info('Created default admin for point system management.');
        }
    }
}