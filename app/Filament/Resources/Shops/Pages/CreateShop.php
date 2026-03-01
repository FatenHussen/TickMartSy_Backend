<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShop extends CreateRecord
{
    protected static string $resource = ShopResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert key-value pairs to working_hours object structure
        if (isset($data['working_hours']) && is_array($data['working_hours'])) {
            $workingHours = [];
            foreach ($data['working_hours'] as $day => $hours) {
                if (strtolower($hours) === 'closed' || empty($hours)) {
                    $workingHours[$day] = [
                        'open' => null,
                        'close' => null,
                        'closed' => true
                    ];
                } else {
                    // Parse "09:00 - 18:00" format
                    $times = array_map('trim', explode('-', $hours));
                    $workingHours[$day] = [
                        'open' => $times[0] ?? null,
                        'close' => $times[1] ?? null,
                        'closed' => false
                    ];
                }
            }
            $data['working_hours'] = $workingHours;
        }

        return $data;
    }
}
