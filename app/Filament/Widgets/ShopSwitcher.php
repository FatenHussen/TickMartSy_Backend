<?php

namespace App\Filament\Widgets;

use App\Models\VendorUser;
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
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        if (!$user) {
            $this->shop_id = null;
            return;
        }

        $this->shop_id = session('shop_id')
            ?? $user->shops()
            ->select('shops.id')
            ->value('id');
    }

    protected function getFormSchema(): array
    {
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        $options = $user
            ? $user->shops()
                ->select('shops.id', 'shops.name')
                ->pluck('shops.name', 'shops.id')
            : [];

        return [
            Forms\Components\Select::make('shop_id')
                ->options($options)
                ->label('branch')
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
        /** @var VendorUser|null $user */
        $user = Auth::guard('vendor-user')->user();

        return $user
            ? $user->shops()
                ->pluck('name', 'id')
                ->toArray()
            : [];
    }
}
