<?php

namespace App\Http\Controllers\Driver;

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
            'key' => 'nullable|string',
        ]);

        $items = ContactMethod::query()
            ->where('key', 'like', '%driver%')
            ->when(
                $request->filled('type'),
                fn($query) => $query->where('type', $request->input('type'))
            )
            ->when(
                $request->filled('key'),
                fn($query) => $query->where('key', $request->input('key'))
            )
            ->orderBy('id')
            ->get();

        return $this->sendResponse(data: AllResource::collection($items));
    }
}
