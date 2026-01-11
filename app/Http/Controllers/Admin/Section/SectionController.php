<?php

namespace App\Http\Controllers\Admin\Section;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Service\FilterRequest;
use App\Http\Requests\Admin\Service\StoreRequest;
use App\Http\Requests\Admin\Service\UpdateRequest;
use App\Http\Resources\DisplayType\OneResource;
use App\Models\Banner;
use App\Models\DisplayType;
use App\Models\Page;
use App\Services\Admin\AdminService;
use App\Services\Admin\ServiceService;
use Illuminate\Http\Request;

class SectionController extends BaseCRUDController
{
    public function pages()
    {
        return $this->sendResponse(data: Page::all());
    }
    public function sectionItemTypes()
    {
        return $this->sendResponse(data: config('section_items'));
    }
    public function displayTypes(Request $request)
    {
        $query = DisplayType::query();

        if ($request->manual_model) {
            $query = $query->where('manual_model', $request->manual_model);
        }
        $data = $query->get();
        return $this->sendResponse(data: OneResource::collection($data));
    }
}
