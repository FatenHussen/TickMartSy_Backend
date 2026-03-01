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
        // Convert working_hours object to key-value pairs for KeyValue component
        if (isset($data['working_hours']) && is_array($data['working_hours'])) {
            $workingHours = [];
            foreach ($data['working_hours'] as $day => $hours) {
                if (is_array($hours)) {
                    $closed = $hours['closed'] ?? false;
                    if ($closed) {
                        $workingHours[$day] = 'Closed';
                    } else {
                        $open = $hours['open'] ?? '';
                        $close = $hours['close'] ?? '';
                        $workingHours[$day] = "{$open} - {$close}";
                    }
                }
            }
            $data['working_hours'] = $workingHours;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert key-value pairs back to working_hours object structure
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
