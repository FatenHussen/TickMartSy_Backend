<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShop extends CreateRecord
{
    protected static string $resource = ShopResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert Repeater array to working_hours object structure
        if (isset($data['working_hours']) && is_array($data['working_hours'])) {
            $workingHours = [];
            foreach ($data['working_hours'] as $item) {
                if (isset($item['day'])) {
                    $workingHours[$item['day']] = [
                        'open' => $item['open'] ?? null,
                        'close' => $item['close'] ?? null,
                        'closed' => $item['closed'] ?? false,
                    ];
                }
            }
            $data['working_hours'] = $workingHours;
        }

        return $data;
    }
}
