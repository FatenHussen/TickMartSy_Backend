<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Http\Resources\PageSection\OneResource;
use App\Models\Page;
use App\Models\Section;
use App\Services\Base\Section\SectionApiService;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function __construct(private UserService $service) {}
    public function index(Request $request)
    {
        $page = Page::where('slug', $request->page_slug)->firstOrFail();

        $sections = $page->pageSections()->with('section.sectionItems.item')->get();

        return $this->sendResponse(data: OneResource::collection($sections));
    }
}
