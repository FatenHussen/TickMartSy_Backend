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


    public function settings()
    {
        return $this->sendResponse(data: Setting::pluck('value', 'key')->toArray());
    }


    // public function contactus(StoreRequest $request)
    // {
    //     Contact::create($request->validated());
    //     return $this->sendResponse();
    // }
}
