<?php

namespace App\Http\Requests\Concerns;

use App\Authorization\CityAccess;
use App\Models\Admin;
use Illuminate\Validation\Validator;

trait ValidatesShopAreaCityScope
{
    public function withValidatorForShopAreaCityScope(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $admin = auth('admin')->user();
            if (! $admin instanceof Admin) {
                return;
            }

            $access = CityAccess::for($admin);
            if ($access->hasFullAccess()) {
                return;
            }

            if (! $this->filled('area_id')) {
                return;
            }

            $areaId = (int) $this->input('area_id');
            if (! $access->canAssignArea($areaId)) {
                $validator->errors()->add(
                    'area_id',
                    'The selected area is not within your allowed cities.'
                );
            }
        });
    }
}
