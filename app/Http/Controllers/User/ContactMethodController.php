<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMethod\AllResource;
use App\Models\ContactMethod;
use Illuminate\Http\Request;

class ContactMethodController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'nullable|in:number,email,url,whts',
        ]);

        $items = ContactMethod::query()
            ->when(
                $request->filled('type'),
                fn($query) => $query->where('type', $request->input('type'))
            )
            ->orderBy('id')
            ->get();

        return $this->sendResponse(data: AllResource::collection($items));
    }
}
