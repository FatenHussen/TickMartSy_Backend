<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Http\Requests\User\Complaint\FilterRequest;
use App\Http\Requests\User\Complaint\StoreRequest;
use App\Http\Resources\Complaint\AllResource;
use App\Services\User\ComplaintService;

class ComplaintController extends Controller
{
    protected ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    public function index(FilterRequest $request)
    {
        $filters = $request->validated();

        $complaints = $this->complaintService->index($filters);

        return   $this->sendResponse(data: AllResource::collection($complaints));
    }



    public function store(StoreRequest $request)
    {
        $complaint = $this->complaintService->store($request->validated());
        return  $this->sendResponse();
    }


    public function orders()
    {
        $orders = $this->complaintService->orders();
        return   $this->sendResponse(data: $orders);
    }
}
