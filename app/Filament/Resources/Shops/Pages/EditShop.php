<?php

namespace App\Filament\Resources\Shops\Pages;

use App\Filament\Resources\Shops\ShopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShop extends EditRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert working_hours object to array for Repeater component
        if (isset($data['working_hours']) && is_array($data['working_hours'])) {
            $workingHoursArray = [];
            foreach ($data['working_hours'] as $day => $hours) {
                if (is_array($hours)) {
                    $workingHoursArray[] = [
                        'day' => $day,
                        'open' => $hours['open'] ?? null,
                        'close' => $hours['close'] ?? null,
                        'closed' => $hours['closed'] ?? false,
                    ];
                }
            }
            $data['working_hours'] = $workingHoursArray;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert Repeater array back to working_hours object structure
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
