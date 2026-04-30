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

        $sections = $page->pageSections()
            ->where('page_sections.is_active', true)
            ->whereHas('section', fn ($query) => $query->where('is_active', true))
            ->with('section.sectionItems.item')
            ->get();

        $sections = $sections
            ->filter(fn ($pageSection) => $this->matchesShowWhen($pageSection->show_when ?? [], $request))
            ->values();

        return $this->sendResponse(data: OneResource::collection($sections));
    }

    private function matchesShowWhen(array $showWhen, Request $request): bool
    {
        if (empty($showWhen)) {
            return true;
        }

        foreach ($showWhen as $key => $expectedValue) {
            if ((string) $request->query($key) !== (string) $expectedValue) {
                return false;
            }
        }

        return true;
    }
}
