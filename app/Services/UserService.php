<?php

namespace App\Services;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\NotFoundException;
use App\Exceptions\VerificationException;
use App\Http\Resources\User\UserResource;
use App\Jobs\SendOtpJob;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Verification;
use App\Traits\FileTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserService extends BaseService
{
    use FileTrait;
    public $model;
    public function __construct(User $model)
    {
        $this->model = $model;
    }
    public function register($data)
    {
        if (!empty($data['phone'])) {
            $user = User::create([
                'phone' => $data['phone'],
                'password' => $data['password'],
                'name' => $data['name'],
                'city_id' => $data['city_id'],
                'governorate_id' => $data['governorate_id']

            ]);

            $this->send_otp($data, $user->id);
        } elseif (!empty($data['email'])) {
            $user = User::create([
                'email' => $data['email'],
                'password' => $data['password'],
                'name' => $data['name'],
                'city_id' => $data['city_id'],
                'governorate_id' => $data['governorate_id']

            ]);

            $this->send_otp($data, $user->id);
        }

        return true;
    }

    public function login($data)
    {
        $field = !empty($data['phone']) ? 'phone' : 'email';

        $user = User::where($field, $data[$field])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new CustomExceptionWithMessage('wrong_credential');
        }
        if($user->is_block)
        {
            throw new CustomExceptionWithMessage('account_blocked');
        }

        if (!$user->{$field . '_verified_at'}) {
            $this->send_otp($data, $user->id);
            throw new VerificationException();
        }

        return new UserResource($user);
    }

    public function send_otp($data, $id)
    {
        $user = User::findOrFail($id);
        if (isset($data['phone']) && $data['phone']) {
            if ($user->phone_verified_at) {
                throw new CustomExceptionWithMessage('already_otp');
            }
            $otp = rand(10000, 99999);
            $phone = $user->phone;
            SendOtpJob::dispatch($phone, $otp);
            Log::info("phone is service" . $user->phone);
            $verification = Verification::create([
                'code' => $otp,
                'user_id' => $user->id,
                'end_at' => Carbon::now()->addMinutes(60),
                'type' => 'verification',
            ]);
            return true;
        } elseif (isset($data['email']) && $data['email']) {
            if ($user->email_verified_at) {
                throw new CustomExceptionWithMessage('already_otp');
            }
            $otp = rand(10000, 99999);
            $verification = Verification::create([
                'code' => $otp,
                'user_id' => $user->id,
                'end_at' => Carbon::now()->addMinutes(60),
                'type' => 'verification',
            ]);
            Mail::to($user->email)->send(new OtpMail($user, $otp, $verification->end_at));
            return true;
        }
    }

    public function verify_otp($request)
    {
        if (!empty($request['phone'])) {
            $user = User::where('phone', $request['phone'])->first();

            $verification = Verification::where('user_id', $user->id)
                ->where('code', $request['code'])
                ->where('type', 'verification')
                ->whereNull('verified_at')
                ->where('end_at', '>', now())
                ->first();

            if (!$verification) {
                throw new CustomExceptionWithMessage('Otp_valid');
            }

            $user->update(['phone_verified_at' => now()]);
            $verification->update(['verified_at' => now()]);

            return new UserResource($user);
        } elseif (!empty($request['email'])) {
            $user = User::where('email', $request['email'])->first();

            $verification = Verification::where('user_id', $user->id)
                ->where('code', $request['code'])
                ->where('type', 'verification')
                ->whereNull('verified_at')
                ->where('end_at', '>', now())
                ->first();

            if (!$verification) {
                throw new CustomExceptionWithMessage('Otp_valid');
            }

            $user->update(['email_verified_at' => now()]);
            $verification->update(['verified_at' => now()]);

            return new UserResource($user);
        }
    }

    public function send_password($data, $userId)
    {
        $user = User::findOrFail($userId);
        if (isset($data['phone']) && $data['phone']) {

            $otp = rand(10000, 99999);
            $phone = $user->phone;
            SendOtpJob::dispatch($phone, $otp);
            Log::info("phone is service" . $user->phone);
            $verification = Verification::create([
                'code' => $otp,
                'user_id' => $user->id,
                'end_at' => Carbon::now()->addMinutes(60),
                'type' => 'reset_password',
            ]);
            return true;
        } elseif (isset($data['email']) && $data['email']) {
            Log::info($data['email']);
            $otp = rand(10000, 99999);
            $verification = Verification::create([
                'code' => $otp,
                'user_id' => $user->id,
                'end_at' => Carbon::now()->addMinutes(60),
                'type' => 'reset_password',
            ]);

            Mail::to($user->email)->send(new OtpMail($user, $otp, $verification->end_at));
            return true;
        }
    }
    public function verify_password($request)
    {
        $field = !empty($request['phone']) ? 'phone' : 'email';

        $user = User::where($field, $request[$field])->first();

        if (!$user) {
            throw new CustomExceptionWithMessage('user_not_found');
        }

        $verification = Verification::where('user_id', $user->id)
            ->where('code', $request['code'])
            ->where('type', 'reset_password')
            ->where('end_at', '>', now())
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('password_valid');
        }

        return new UserResource($user);
    }

    public function reset_password($id, $newPassword)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundException();
        }

        $user->update(['password' => Hash::make($newPassword)]);
        auth()->user()?->tokens()->delete();

        return new UserResource($user);
    }

    public static function logout($request)
    {  
        auth()->user()->currentAccessToken()->delete();
        return true;
    }

      
    public function update_password($id, $request)
    {
        $user = User::findOrFail($id);

        if (!Hash::check($request['old_password'], $user->password)) {
            throw new CustomExceptionWithMessage('wrong_password');
        }

        $user->update(['password' => Hash::make($request['new_password'])]);
        return true;
    }
    public function delete_account($request)
    {
        $user = auth('users')->id();
        $delete = User::where('id', $user)->first();
        $delete->delete();
        return true;
    }

    public function update_email($request)
    {
        $user = auth('users')->user();


        $otp = rand(10000, 99999);

        $verification = Verification::create([
            'code' => $otp,
            'user_id' => $user->id,
            'end_at' => Carbon::now()->addMinutes(60),
            'type' => 'update_email',
            'value' => $request['email'],
        ]);

        Mail::to($request['email'])->send(new OtpMail($user, $otp, $verification->end_at));

        return true;
    }

    public function update_phone($request)
    {
        $user = auth('users')->user();

        $otp = rand(10000, 99999);

        $verification = Verification::create([
            'code' => $otp,
            'user_id' => $user->id,
            'end_at' => Carbon::now()->addMinutes(60),
            'type' => 'update_phone',
            'value' => $request['phone'],
        ]);

        SendOtpJob::dispatch($request['phone'], $otp);

        return true;
    }

    public function verify_update($request)
    {
        $user = auth('users')->user();

        $verification = Verification::where('user_id', $user->id)
            ->where('code', $request['code'])
            ->whereIn('type', ['update_email', 'update_phone'])
            ->whereNull('verified_at')
            ->where('end_at', '>', now())
            ->latest()
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('otp_invalid');
        }

        if ($verification->type === 'update_email') {
            $user->update(['email' => $verification->value, 'email_verified_at' => now()]);
        } elseif ($verification->type === 'update_phone') {
            $user->update(['phone' => $verification->value, 'phone_verified_at' => now()]);
        }

        $verification->update(['verified_at' => now()]);


        return new UserResource($user);
    }
    
}