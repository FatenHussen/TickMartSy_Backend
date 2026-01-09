<?php

namespace App\Http\Controllers\Admin\Section;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\Admin\Service\FilterRequest;
use App\Http\Requests\Admin\Service\StoreRequest;
use App\Http\Requests\Admin\Service\UpdateRequest;
use App\Models\Banner;
use App\Models\Page;
use App\Services\Admin\AdminService;
use App\Services\Admin\ServiceService;

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
}
