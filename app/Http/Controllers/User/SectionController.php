<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ResetPasswordRequest;
use App\Http\Requests\User\Auth\SendOtpRequest;
use App\Http\Requests\User\Auth\SendPasswordRequest;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Http\Requests\User\Auth\VerifyOtpRequest;
use App\Http\Requests\User\Auth\VerifyPasswordRequest;
use App\Http\Resources\SectionPage\OneResource;
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

        $sections = $page->pageSections()->with('section.sectionItems.item')->get()
            ->map(function ($pageSection) {
                $section = $pageSection->section;

                $pageSection->api_data = app(SectionApiService::class)->preview($section);

                return $pageSection;
            });

        return $this->sendResponse(data: OneResource::collection($sections));
    }
}
