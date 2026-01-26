<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\UpdateEmailRequest;
use App\Http\Requests\User\Auth\UpdatePaswordRequest;
use App\Http\Requests\User\Auth\UpdatePhoneRequest;
use App\Http\Requests\User\Auth\VerifyUpdateRequest;
use App\Http\Requests\User\Auth\UpdateProfileRequest;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(protected UserService $service) {}
    public function get_profile()
    {
        $res = $this->service->get_profile();
        return $this->sendResponse(data: $res);
    }

    public function update_profile(UpdateProfileRequest $request)
    {
        $res = $request->validated();
        $id = auth('user')->id();
        $data = $this->service->update_profile($res, $id);
        return $this->sendResponse(data: $data);
    }

    public function update_password(UpdatePaswordRequest $request)
    {
        $res = $request->validated();
        $id = auth('user')->id();
        $data = $this->service->update_password($id, $res);
        return $this->sendResponse(data: $data);
    }
    public function delete_account(Request $request)
    {

        $res = $this->service->delete_account($request);
        return $this->sendResponse();
    }

    public function update_email(UpdateEmailRequest $request)
    {
        $res = $this->service->update_email($request);
        return $this->sendResponse();
    }

    public function update_phone(UpdatePhoneRequest $request)
    {
        $res = $this->service->update_phone($request);
        return $this->sendResponse();
    }
    public function verify_update(VerifyUpdateRequest $request)
    {
        $res = $this->service->verify_update($request);
        return $this->sendResponse(data: $res);
    }
}
