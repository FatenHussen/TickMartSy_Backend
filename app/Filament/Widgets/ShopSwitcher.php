<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class ShopSwitcher extends Widget implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected string $view = 'filament.widgets.shop-switcher';

    public ?int $shop_id = null;

    public function mount(): void
    {
        $this->shop_id = session('shop_id')
            ?? Auth::user('vendor-user')
            ->shops()
            ->select('shops.id')
            ->value('id');
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('shop_id')
                ->options(
                    Auth::user('vendor-user')
                        ->shops()
                        ->select('shops.id', 'shops.name')
                        ->pluck('shops.name', 'shops.id')
                )->label('branch')
                ->placeholder('')
                ->reactive()
                ->afterStateUpdated(fn($state) => session(['shop_id' => $state])),
        ];
    }

    protected function getFormModel(): string
    {
        return self::class;
    }
    public function getShopOptions(): array
    {
        return Auth::user('vendor-user')
            ->shops()
            ->pluck('name', 'id')
            ->toArray();
    }
}
