<?php

namespace App\Http\Requests\Concerns;

use App\Authorization\CityAccess;
use App\Models\Admin;
use Illuminate\Validation\Validator;

trait ValidatesAdminRecordCityScope
{
    public function withValidatorForAdminRecordCityScope(Validator $validator): void
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

            if (! $this->has('city_ids')) {
                return;
            }

            $ids = array_map('intval', $this->input('city_ids', []));
            if (! $access->allCityIdsAreAssignable($ids)) {
                $validator->errors()->add(
                    'city_ids',
                    'The selected cities are not within your allowed scope.'
                );
            }
        });
    }
}
