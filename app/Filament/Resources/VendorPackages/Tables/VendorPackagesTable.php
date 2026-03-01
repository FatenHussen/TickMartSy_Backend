<?php

namespace App\Filament\Resources\VendorPackages\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VendorPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الباقة')
                    ->formatStateUsing(fn($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('المدة (أيام)')
                    ->suffix(' يوم')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_products')
                    ->label('عدد المنتجات')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميزة')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_premium_badge')
                    ->label('شارة مميزة')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_analytics')
                    ->label('تحليلات')
                    ->boolean(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('نسبة العمولة')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشطة')
                    ->placeholder('الكل')
                    ->trueLabel('نشطة فقط')
                    ->falseLabel('غير نشطة فقط'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميزة')
     ->placeholder('الكل')
                    ->trueLabel('مميزة فقط')
                    ->falseLabel('عادية فقط'),
            ])
            ->actions([
                ViewAction::make(),
                \Filament\Actions\Action::make('subscribe')
                    ->label('اشتراك')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد الاشتراك')
                    ->modalDescription(fn($record) => "هل تريد الاشتراك في باقة {$record->name} بسعر {$record->price} دولار")
                    ->modalSubmitActionLabel('تأكيد الاشتراك')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function ($record) {
                        $user = \Illuminate\Support\Facades\Auth::guard('vendor-user')->user();

                        if (!$user) {
                            \Filament\Notifications\Notification::make()
                                ->title('خطأ')
                                ->body('يجب تسجيل الدخول أولاً')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Get user's shops through pivot table
                        $shop = \App\Models\Shop::whereHas('vendorUsers', function ($query) use ($user) {
                            $query->where('vendor_users.id', $user->id);
                        })->first();

                        if (!$shop) {
                            \Filament\Notifications\Notification::make()
                                ->title('خطأ')
                                ->body('لا يوجد متجر مرتبط بحسابك')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Check if there's an active subscription
                        $activeSubscription = $shop->subscriptions()
                            ->where('status', 'active')
                            ->where('ends_at', '>=', now()->toDateString())
                            ->first();

                        if ($activeSubscription) {
                            \Filament\Notifications\Notification::make()
                                ->title('تنبيه')
                                ->body('لديك اشتراك نشط بالفعل. سينتهي في ' . $activeSubscription->ends_at->format('Y-m-d'))
                                ->warning()
                                ->send();
                            return;
                        }

                        // Create new subscription
                        $subscription = \App\Models\VendorSubscription::create([
                            'shop_id' => $shop->id,
                            'vendor_package_id' => $record->id,
                            'starts_at' => now()->toDateString(),
                            'ends_at' => now()->addDays($record->duration_days)->toDateString(),
                            'status' => 'active',
                            'auto_renew' => false,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('تم الاشتراك بنجاح')
                            ->body("تم الاشتراك في باقة {$record->name}. ينتهي الاشتراك في {$subscription->ends_at->format('Y-m-d')}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn() => \Illuminate\Support\Facades\Auth::guard('vendor-user')->check()),
            ])
            ->defaultSort('price', 'asc');
    }
}

