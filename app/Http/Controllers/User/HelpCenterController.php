<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contactus\StoreRequest;
use App\Http\Resources\Branch\AllResource as BranchAllResource;
use App\Http\Resources\Faq\AllResource;
use App\Http\Resources\ServiceCenter\AllResource as ServiceCenterAllResource;
use App\Models\Branch;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\ServiceCenter;
use App\Models\Setting;
use App\Models\Tire;
use Illuminate\Http\Request;

class HelpCenterController extends Controller
{
    public function faqs(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:orders,delivery,payments,account,stores&drivers,other',
            // 'type'   => 'required|string',
            'search' => 'nullable|string'
        ]);

        $query = Faq::where('type', $request->type);

        if ($request->filled('search')) {

            $locale = app()->getLocale();

            $query->where(function ($q) use ($request, $locale) {
                $q->where("question->$locale", 'like', '%' . $request->search . '%')
                    ->orWhere("answer->$locale", 'like', '%' . $request->search . '%');
            });
        }

        $faqs = $query->get();
        $types = ['orders', 'delivery', 'payments', 'account', 'stores&drivers', 'other'];

        return $this->sendResponse(
            data: [
                'types' => $types,
                'faqs' =>
                AllResource::collection(
                    $faqs
                ),
            ]

        );
    }


    // public function settings()
    // {
    //     return $this->sendResponse(data: Setting::pluck('value', 'key')->toArray());
    // }

    public function settings()
    {
        $settings = Setting::all()->keyBy('key');
        $locale = app()->getLocale();

        return $this->sendResponse(data: [
            'welcome' => [
                'image' => isset($settings['welcome_image'])
                    ? [asset('storage/' . $settings['welcome_image']->value)]
                    : null,
                'text'  => isset($settings['welcome_text'])
                    ? ($settings['welcome_text']->value[$locale]
                        ?? $settings['welcome_text']->value['en']
                        ?? null)
                    : null,
            ],
            'login' => [
                'image' => isset($settings['login_image'])
                    ? asset('storage/' . $settings['login_image']->value)
                    : null,
                'link'  => $settings['login_link']->value ?? null,
            ],
            'quick_action' => [
                'image' => isset($settings['quick_action_image'])
                    ? asset('storage/' . $settings['quick_action_image']->value)
                    : null,
            ],
            'quick_order' => $this->formatQuickOrderSettings($settings, $locale),
            'contact' => [
                'phone' => $settings['phone']->value ?? null,
                'whatsapp' => $settings['whts']->value ?? null,
                'email' => $settings['email']->value ?? null,
                'instagram' => $settings['instagram']->value ?? null,
                'facebook' => $settings['facebook']->value ?? null,
            ],
            'color' => [
                'main_color' => isset($settings['main_color'])
                    ? $settings['main_color']->value
                    : '#E4F0FB',
                'text_color' => isset($settings['text_color'])
                    ? $settings['text_color']->value
                    : '#2A2A2A',
                'second_color' => isset($settings['second_color'])
                    ? $settings['second_color']->value
                    : '#e27676',
            ],
            'dark_color' => [
                'main_color' => isset($settings['dark_main_color'])
                    ? $settings['dark_main_color']->value
                    : '#0D1117',
                'text_color' => isset($settings['dark_text_color'])
                    ? $settings['dark_text_color']->value
                    : '#FFFFFF',
                'second_color' => isset($settings['dark_second_color'])
                    ? $settings['dark_second_color']->value
                    : '#9CA3AF',
            ],
        ]);
    }

    /**
     * Home «طلب سريع» hero: visibility + background + step cards styling.
     *
     * @param  \Illuminate\Support\Collection<string, Setting>  $settings
     * @return array<string, mixed>
     */
    private function formatQuickOrderSettings($settings, string $locale): array
    {
        $enabledRaw = $settings['quick_order_enabled']->value ?? true;
        $isEnabled = filter_var($enabledRaw, FILTER_VALIDATE_BOOLEAN);

        $bgImage = $settings['quick_order_background_image']->value ?? null;
        if (is_array($bgImage)) {
            $bgImage = $bgImage[0] ?? null;
        }

        $cardVariant = $settings['quick_order_card_variant']->value ?? 'horizontal';
        if (! in_array($cardVariant, ['horizontal', 'vertical', 'square'], true)) {
            $cardVariant = 'horizontal';
        }

        $steps = $settings['quick_order_steps']->value ?? [];
        if (! is_array($steps)) {
            $steps = [];
        }

        return [
            'is_enabled' => $isEnabled,
            'background_image' => $bgImage
                ? asset('storage/' . ltrim((string) $bgImage, '/'))
                : null,
            'background_color' => $settings['quick_order_background_color']->value ?? '#FFE8D6',
            'card_background_color' => $settings['quick_order_card_background_color']->value ?? '#FFFFFF',
            'card_variant' => $cardVariant,
            'badge' => $this->localizedSettingText($settings['quick_order_badge']->value ?? null, $locale),
            'title' => $this->localizedSettingText($settings['quick_order_title']->value ?? null, $locale),
            'subtitle' => $this->localizedSettingText($settings['quick_order_subtitle']->value ?? null, $locale),
            'cta' => $this->localizedSettingText($settings['quick_order_cta']->value ?? null, $locale),
            'steps' => collect($steps)->map(function ($step) use ($locale) {
                return [
                    'number' => (int) ($step['number'] ?? 0),
                    'icon' => $step['icon'] ?? null,
                    'title' => $this->localizedSettingText($step['title'] ?? null, $locale),
                    'description' => $this->localizedSettingText($step['description'] ?? null, $locale),
                ];
            })->values()->all(),
            'action' => [
                'page_slug' => 'custom_order_request',
                'route' => '/api/user/custom-order-requests',
            ],
        ];
    }

    private function localizedSettingText(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return null;
        }

        return $value[$locale] ?? $value['en'] ?? $value['ar'] ?? null;
    }

    // public function contactus(StoreRequest $request)
    // {
    //     Contact::create($request->validated());
    //     return $this->sendResponse();
    // }
}
