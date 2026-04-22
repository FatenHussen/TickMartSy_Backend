<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['en' => 'piece', 'ar' => 'قطعة'],
            ['en' => 'kg', 'ar' => 'كيلوغرام'],
            ['en' => 'gram', 'ar' => 'غرام'],
            ['en' => 'liter', 'ar' => 'لتر'],
            ['en' => 'box', 'ar' => 'علبة'],
        ];

        foreach ($units as $item) {
            $unit = Unit::query()
                ->where('name->en', $item['en'])
                ->orWhere('name->ar', $item['ar'])
                ->first();

            if (!$unit) {
                $unit = new Unit();
            }

            $unit->setTranslations('name', [
                'ar' => $item['ar'],
                'en' => $item['en'],
            ]);
            $unit->is_active = true;
            $unit->save();
        }
    }
}
