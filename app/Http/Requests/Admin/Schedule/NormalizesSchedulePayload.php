<?php

namespace App\Http\Requests\Admin\Schedule;

use App\Models\Language;
use Illuminate\Http\UploadedFile;

trait NormalizesSchedulePayload
{
    protected array $locales = [];

    protected function mergeNormalizedSchedulePayload(bool $requireAllLocalesForName): void
    {
        $this->locales = Language::active()->pluck('code')->toArray();
        if ($this->locales === []) {
            $this->locales = ['ar', 'en'];
        }

        $data = $this->all();

        if (isset($data['name']) && is_array($data['name'])) {
            $prepared = [];
            foreach ($this->locales as $locale) {
                if (array_key_exists($locale, $data['name'])) {
                    $prepared[$locale] = $data['name'][$locale];
                } elseif ($requireAllLocalesForName) {
                    $prepared[$locale] = null;
                }
            }
            $this->merge(['name' => $prepared]);
        }

        if (isset($data['description']) && is_array($data['description'])) {
            $prepared = [];
            foreach ($this->locales as $locale) {
                if (array_key_exists($locale, $data['description'])) {
                    $prepared[$locale] = $data['description'][$locale];
                }
            }
            $this->merge(['description' => $prepared]);
        }

        $discountType = $this->input('discount_type');
        if ($discountType === '' || $discountType === 'null') {
            $this->merge(['discount_type' => null]);
        }

        $discountValue = $this->input('discount_value');
        if ($discountValue === '' || $discountValue === 'null') {
            $this->merge(['discount_value' => null]);
        }

        if ($this->file('images') instanceof UploadedFile) {
            $this->merge([
                'images' => [$this->file('images')],
            ]);
        }
    }
}
