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
                    $closed = $item['closed'] ?? false;
                    $workingHours[$item['day']] = [
                        'open' => $closed ? null : ($item['open'] ?? null),
                        'close' => $closed ? null : ($item['close'] ?? null),
                        'closed' => $closed,
                    ];
                }
            }
            $data['working_hours'] = $workingHours;
        }

        return $data;
    }
}
