<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Language\OneResource;
use App\Services\BaseService;
use App\Http\Resources\Admin\Language\AllResource;
use App\Models\Language;

class LanguageService extends BaseService
{
    public function __construct(Language $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->imageColumn = 'flag_icon';
        $this->imageFolder = 'flags';
    }
}
