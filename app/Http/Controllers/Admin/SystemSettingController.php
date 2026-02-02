<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Http\Requests\Admin\SystemSetting\UpdateSettingRequest;
use App\Http\Requests\Admin\SystemSetting\BatchUpdateRequest;
use App\Http\Requests\Admin\SystemSetting\CreateSettingRequest;

class SystemSettingController extends Controller
{
    /**
     * Get all settings grouped by category
     */
    public function index()
    {
        $settings = SystemSetting::active()
            ->orderBy('group')
            ->orderBy('title')
            ->get()
            ->groupBy('group');

        return $this->sendResponse(
            data: $settings->map(function ($groupSettings) {
                return $groupSettings->map(function ($setting) {
                    return [
                        'id' => $setting->id,
                        'key' => $setting->key,
                        'value' => $this->castValue($setting->value, $setting->type),
                        'type' => $setting->type,
                        'title' => $setting->title,
                        'description' => $setting->description,
                    ];
                });
            })
        );
    }

    /**
     * Get settings by group
     */
    public function getByGroup(string $group)
    {
        $settings = SystemSetting::byGroup($group)
            ->active()
            ->orderBy('title')
            ->get()
            ->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $this->castValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'title' => $setting->title,
                    'description' => $setting->description,
                ];
            });

        return $this->sendResponse(data: $settings);
    }

    /**
     * Update multiple settings
     */
    public function updateBatch(BatchUpdateRequest $request)
    {
        $updatedCount = 0;

        foreach ($request->settings as $settingData) {
            $setting = SystemSetting::where('key', $settingData['key'])->first();
            
            if ($setting) {
                // Convert value to string for storage
                $value = $settingData['value'];
                if ($setting->type === 'boolean') {
                    $value = $value ? '1' : '0';
                }
                
                $setting->update(['value' => (string) $value]);
                $updatedCount++;
            }
        }

        // Clear cache
        SystemSetting::clearCache();

        return $this->sendResponse(
            message: "Updated {$updatedCount} settings successfully",
            data: ['updated_count' => $updatedCount]
        );
    }

    /**
     * Update single setting
     */
    public function update(UpdateSettingRequest $request, string $key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();

        // Convert value to string for storage
        $value = $request->value;
        if ($setting->type === 'boolean') {
            $value = $value ? '1' : '0';
        }

        $setting->update(['value' => (string) $value]);

        return $this->sendResponse(
            message: 'Setting updated successfully',
            data: [
                'key' => $setting->key,
                'value' => $this->castValue($setting->value, $setting->type),
                'type' => $setting->type,
                'title' => $setting->title,
            ]
        );
    }

    /**
     * Get single setting
     */
    public function show(string $key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();

        return $this->sendResponse(
            data: [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $this->castValue($setting->value, $setting->type),
                'type' => $setting->type,
                'title' => $setting->title,
                'description' => $setting->description,
            ]
        );
    }

    /**
     * Create new setting
     */
    public function store(CreateSettingRequest $request)
    {
        $setting = SystemSetting::create($request->validated());

        return $this->sendResponse(
            message: 'Setting created successfully',
            data: [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $this->castValue($setting->value, $setting->type),
                'type' => $setting->type,
                'group' => $setting->group,
                'title' => $setting->title,
                'description' => $setting->description,
            ]
        );
    }

    /**
     * Delete setting
     */
    public function destroy(string $key)
    {
        $setting = SystemSetting::where('key', $key)->firstOrFail();
        $setting->delete();

        return $this->sendResponse(
            message: 'Setting deleted successfully'
        );
    }

    /**
     * Clear all settings cache
     */
    public function clearCache()
    {
        SystemSetting::clearCache();

        return $this->sendResponse(
            message: 'Settings cache cleared successfully'
        );
    }

    /**
     * Cast value to appropriate type
     */
    private function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : 0,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}