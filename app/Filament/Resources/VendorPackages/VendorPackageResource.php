<?php

namespace App\Filament\Resources\VendorPackages;

use App\Filament\Resources\VendorPackages\Pages\ListVendorPackages;
use App\Models\VendorPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class VendorPackageResource extends Resource
{
    protected static ?string $model = VendorPackage::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;
    protected static ?string $navigationLabel = 'باقات التجار';
    protected static ?string $modelLabel = 'باقة تاجر';
    protected static ?string $pluralModelLabel = 'باقات التجار';
    protected static ?int $navigationSort = 90;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->active()->ordered();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->formatStateUsing(fn($record) => $record->getTranslation('name', app()->getLocale())),
                Tables\Columns\TextColumn::make('slug')
                    ->label('المعرف')
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('المدة (يوم)')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_products')
                    ->label('حد المنتجات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('نسبة العمولة %')
                    ->sortable(),
                Tables\Columns\TextColumn::make('has_premium_badge')
                    ->label('شارة مميزة')
                    ->boolean()
                    ->sortable(),
            ])
            ->actions([
                Action::make('subscribe')
                    ->label('اشترك')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الاشتراك')
                    ->modalDescription(fn($record) => 'هل تريد الاشتراك في باقة "' . $record->getTranslation('name', app()->getLocale()) . '"؟')
                    ->action(function ($record) {
                        $user = auth()->guard('vendor-user')->user();
                        if (!$user?->vendor_id) {
                            \Filament\Notifications\Notification::make()
                                ->title('خطأ')
                                ->body('لم يتم العثور على التاجر.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $vendor = \App\Models\Vendor::find($user->vendor_id);
                        if (!$vendor) {
                            \Filament\Notifications\Notification::make()
                                ->title('خطأ')
                                ->body('التاجر غير موجود.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $activeSub = $vendor->subscriptions()
                            ->where('status', 'active')
                            ->where('ends_at', '>=', now()->toDateString())
                            ->first();
                        if ($activeSub) {
                            \Filament\Notifications\Notification::make()
                                ->title('لديك اشتراك نشط')
                                ->body('لديك بالفعل اشتراك نشط في باقة ' . ($activeSub->package->getTranslation('name', app()->getLocale()) ?? '') . '.')
                                ->warning()
                                ->send();
                            return;
                        }
                        \App\Models\VendorSubscription::create([
                            'vendor_id' => $vendor->id,
                            'vendor_package_id' => $record->id,
                            'starts_at' => now(),
                            'ends_at' => now()->addDays($record->duration_days),
                            'auto_renew' => false,
                            'status' => 'active',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('تم الاشتراك بنجاح')
                            ->body('تم تفعيل اشتراكك في الباقة.')
                            ->success()
                            ->send();
                    }),
            ])
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorPackages::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}