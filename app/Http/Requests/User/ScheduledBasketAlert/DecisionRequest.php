<?php

namespace App\Http\Requests\User\ScheduledBasketAlert;

use App\Models\ScheduledBasketAlert;
use Illuminate\Foundation\Http\FormRequest;

class DecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('user')->check();
    }

    public function rules(): array
    {
        return [
            'decision' => [
                'required',
                'in:' . implode(',', [
                    ScheduledBasketAlert::DECISION_ACCEPT_PARTIAL,
                    ScheduledBasketAlert::DECISION_WAIT_FULL,
                    ScheduledBasketAlert::DECISION_SKIP_CYCLE,
                    ScheduledBasketAlert::DECISION_DISMISSED,
                ]),
            ],
        ];
    }
}
