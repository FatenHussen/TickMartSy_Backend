<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageSection\OneResource;
use App\Models\Page;
use App\Services\Base\PageSection\PageSectionPresentationService;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct(
        private PageSectionPresentationService $pageSectionPresentation,
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'page_slug' => ['required', 'string', 'max:255', 'exists:pages,slug'],
        ]);

        $page = Page::where('slug', $request->page_slug)->firstOrFail();

        $sections = $this->pageSectionPresentation->getSectionsForUserView($page, $request);

        return $this->sendResponse(data: OneResource::collection($sections));
    }
}
