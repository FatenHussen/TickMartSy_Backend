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
                    ->label(__('custom.package_name'))
                    ->formatStateUsing(fn($record) => $record->getTranslation('name', app()->getLocale()))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('custom.price'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label(__('custom.duration_days'))
                    ->suffix(' ' . __('custom.all')) // يمكنك وضع "يوم" هنا بالعربية أو Days بالإنجليزية
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_products')
                    ->label(__('custom.max_products'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('custom.is_featured'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_premium_badge')
                    ->label(__('custom.has_premium_badge'))
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_analytics')
                    ->label(__('custom.has_analytics'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(__('custom.commission_rate'))
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('custom.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('custom.created_at'))
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('custom.active_filter'))
                    ->placeholder(__('custom.all'))
                    ->trueLabel(__('custom.active_only'))
                    ->falseLabel(__('custom.inactive_only')),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('custom.featured_filter'))
                    ->placeholder(__('custom.all'))
                    ->trueLabel(__('custom.featured_only'))
                    ->falseLabel(__('custom.normal_only')),
            ])
            ->actions([
                ViewAction::make(),
                \Filament\Actions\Action::make('subscribe')
                    ->label(__('custom.subscribe'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('custom.confirm_subscription'))
                    ->modalDescription(fn($record) => __("custom.subscription_success_body", [
                        'package' => $record->name,
                        'date' => now()->addDays($record->duration_days)->format('Y-m-d'),
                    ]))
                    ->modalSubmitActionLabel(__('custom.confirm'))
                    ->modalCancelActionLabel(__('custom.cancel'))
                    ->action(function ($record) {
                        $user = Auth::guard('vendor-user')->user();

                        if (!$user) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('custom.subscription_error_login'))
                                ->danger()
                                ->send();
                            return;
                        }

                        $activeSubscription = \App\Models\VendorSubscription::query()
                            ->where('vendor_id', $user->vendor_id)
                            ->whereIn('status', ['active', 'pending'])
                            ->where('ends_at', '>=', now()->toDateString())
                            ->first();

                        if ($activeSubscription) {
                            \Filament\Notifications\Notification::make()
                                ->title(__('custom.subscription_warning_active', [
                                    'date' => $activeSubscription->ends_at->format('Y-m-d'),
                                ]))
                                ->warning()
                                ->send();
                            return;
                        }

                        $subscription = \App\Models\VendorSubscription::create([
                            'vendor_id' => $user->vendor_id,
                            'vendor_package_id' => $record->id,
                            'starts_at' => now()->toDateString(),
                            'ends_at' => now()->addDays($record->duration_days)->toDateString(),
                            'status' => 'active',
                            'auto_renew' => false,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('custom.subscription_success'))
                            ->body(__('custom.subscription_success_body', [
                                'package' => $record->name,
                                'date' => $subscription->ends_at->format('Y-m-d'),
                            ]))
                            ->success()
                            ->send();
                    })
                    ->visible(fn() => Auth::guard('vendor-user')->check()),
            ])
            ->defaultSort('price', 'asc');
    }
}
