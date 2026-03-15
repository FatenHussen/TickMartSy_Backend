<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\UpdateRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key')->get();

        return $this->sendResponse(
            data: $settings->map(fn($setting) => $this->formatSetting($setting))
        );
    }

    public function show(string $key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return $this->sendResponse(
            data: $this->formatSetting($setting)
        );
    }

    public function update(UpdateRequest $request, string $key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        if ($setting->type === 'file' && $request->hasFile('value')) {
            $this->deleteOldFile($setting->value);
            $value = $request->file('value')->store('settings', 'public');
        } else {
            $value = $this->normalizeValue($request->value, $setting->type);
        }

        $setting->update([
            'value' => $value,
        ]);

        return $this->sendResponse(
            message: 'Setting updated successfully',
            data: $this->formatSetting($setting->fresh())
        );
    }

    private function formatSetting(Setting $setting): array
    {
        return [
            'id' => $setting->id,
            'key' => $setting->key,
            'type' => $setting->type,
            'value' => $this->castValue($setting->value, $setting->type),
            'created_at' => $setting->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $setting->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function normalizeValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : 0,
            'json' => $value,
            default => $value,
        };
    }

    private function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : 0,
            'json' => is_array($value) ? $value : (json_decode($value, true) ?? $value),
            'file' => $value ? asset('storage/' . $value) : null,
            default => $value,
        };
    }

    private function deleteOldFile($path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
