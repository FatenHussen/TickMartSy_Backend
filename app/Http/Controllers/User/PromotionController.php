<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\AllResource;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'page_slug' => ['nullable', 'string', 'max:255', 'exists:pages,slug'],
        ]);

        $query = Promotion::query()
            ->active()
            ->orderBy('id')
            ->with('pages');

        if ($request->filled('page_slug')) {
            $query->forPageSlug($request->string('page_slug')->toString());
        }

        return $this->sendResponse(data: AllResource::collection($query->get()));
    }
}
