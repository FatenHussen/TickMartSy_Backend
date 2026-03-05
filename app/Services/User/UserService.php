<?php

namespace App\Services\User;

use App\Exceptions\AccountAlreadyExistsException;
use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\InactiveAccountException;
use App\Exceptions\InvalidVerificationCodeException;
use App\Exceptions\NotFoundException;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Jobs\SendOtpJob;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Verification;
use App\Services\BaseService;
use App\Traits\FileTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserService
{
    use FileTrait;

    public function __construct(public User $model)
    {
        $this->model = $model;
    }

    /* =========================
        Helpers
    ========================= */

    private function resolveField(array $data): string
    {
        return !empty($data['phone']) ? 'phone' : 'email';
    }

    private function resolveUser(array $data): User
    {
        $field = $this->resolveField($data);

        $user = $this->model->where($field, $data[$field])->first();

        if (!$user) {
            throw new NotFoundException();
        }

        return $user;
    }

    private function createOtp(
        User $user,
        string $type,
        int $minutes = 60
    ): Verification {
        return Verification::create([
            'code'    => rand(10000, 99999),
            'user_id' => $user->id,
            'type'    => $type,
            'end_at'  => now()->addMinutes($minutes),
        ]);
    }

    private function sendOtp(User $user, Verification $verification, string $channel): void
    {
        if ($channel === 'phone') {
            SendOtpJob::dispatch($user->phone, $verification->code);
        } else {
            Mail::to($user->email)->send(
                new OtpMail($user, $verification->code, $verification->end_at)
            );
        }
    }

    /* =========================
        Auth
    ========================= */

    public function register(array $data): bool
    {
        $field = $this->resolveField($data);

        $existingUser = $this->model
            ->where($field, $data[$field])
            ->first();

        if ($existingUser) {
            if (!$existingUser->{$field . '_verified_at'}) {
                $verification = $this->createOtp($existingUser, 'verification');
                $this->sendOtp($existingUser, $verification, $field);
                throw new InactiveAccountException();
            }
            throw new AccountAlreadyExistsException();
        }

        $user = $this->model->create([
            $field           => $data[$field],
            'password'       => $data['password'],
            'name'           => $data['name'],
            'city_id'        => $data['city_id'],
            'governorate_id' => $data['governorate_id'],
        ]);

        try {
            $verification = $this->createOtp($user, 'verification');
            $this->sendOtp($user, $verification, $field);
        } catch (\Throwable $e) {
        }

        return true;
    }




    public function login(array $data)
    {
         $field = $this->resolveField($data);
        $user  = $this->resolveUser($data);

        if (!Hash::check($data['password'], $user->password)) {
            throw new CustomExceptionWithMessage('custom.wrong_credential');
        }

        if (!$user->{$field . '_verified_at'}) {
            $verification = $this->createOtp($user, 'verification');
            $this->sendOtp($user, $verification, $field);

            throw new InvalidVerificationCodeException();
        }

        $user->load('currency');

        return new UserResource($user);
    }

    public static function logout(): bool
    {
        auth('user')->user()?->currentAccessToken()?->delete();
        return true;
    }

    /* =========================
        OTP Verification
    ========================= */

    public function verifyOtp(array $data)
    {
        $field = $this->resolveField($data);
        $user  = $this->resolveUser($data);

        $verification = Verification::where([
            'user_id' => $user->id,
            'code'    => $data['code'],
            'type'    => 'verification',
        ])
            ->whereNull('verified_at')
            ->where('end_at', '>', now())
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('custom.otp_valid');
        }

        $user->update([$field . '_verified_at' => now()]);
        $verification->update(['verified_at' => now()]);

        // Award registration points on first verification
        try {
            $pointService = app(\App\Services\PointService::class);
            if (!$pointService->isEventCompleted($user->id, 'user_registration')) {
                $pointService->awardPoints(
                    userId: $user->id,
                    ruleCode: 'user_registration',
                    referenceType: 'user',
                    referenceId: $user->id
                );
                $pointService->markEventCompleted($user->id, 'user_registration');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to award registration points', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return new UserResource($user);
    }

    /* =========================
        Password Reset
    ========================= */

    public function sendPasswordOtp(array $data): bool
    {
        $user  = $this->resolveUser($data);
        $field = $this->resolveField($data);

        $verification = $this->createOtp($user, 'reset_password');
        $this->sendOtp($user, $verification, $field);

        return true;
    }

    public function verifyPassword(array $data)
    {
        $user = $this->resolveUser($data);

        $verification = Verification::where([
            'user_id' => $user->id,
            'code'    => $data['code'],
            'type'    => 'reset_password',
        ])
            ->where('end_at', '>', now())
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('custom.password_valid');
        }

        return new UserResource($user);
    }

    public function resetPassword(int $id, string $newPassword)
    {
        $user = $this->model->find($id);

        if (!$user) {
            throw new NotFoundException();
        }

        $user->update(['password' => Hash::make($newPassword)]);
        auth('user')->user()?->tokens()->delete();

        return new UserResource($user);
    }

    public function updatePassword(int $id, array $data): bool
    {
        $user = $this->model->findOrFail($id);

        if (!Hash::check($data['old_password'], $user->password)) {
            throw new CustomExceptionWithMessage('custom.wrong_password');
        }

        $user->update(['password' => Hash::make($data['new_password'])]);
        return true;
    }

    /* =========================
        Update Contact
    ========================= */

    public function updateContact(string $type, string $code): bool
    {
        $user = auth('user')->user();

        $verification = $this->createOtp($user, $type, $code);

        $type === 'update_phone'
            ? SendOtpJob::dispatch($code, $verification->code)
            : Mail::to($code)->send(new OtpMail($user, $verification->code, $verification->end_at));

        return true;
    }

    public function verifyUpdate(array $data)
    {
        $user = auth('user')->user();

        $verification = Verification::where('user_id', $user->id)
            ->where('code', $data['code'])
            ->whereIn('type', ['update_email', 'update_phone'])
            ->whereNull('verified_at')
            ->where('end_at', '>', now())
            ->latest()
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('custom.otp_invalid');
        }

        $field = $verification->type === 'update_email' ? 'email' : 'phone';

        $user->update([
            $field => $verification->code,
            $field . '_verified_at' => now(),
        ]);

        $verification->update(['verified_at' => now()]);

        return new UserResource($user);
    }

    /* =========================
        Account
    ========================= */

    public function deleteAccount(): bool
    {
        $userId = auth('user')->id();
        $this->model->where('id', $userId)->delete();
        return true;
    }
    public function get_profile()
    {
        $data = auth('user')->user()
            ->loadCount(['orders', 'userBasketSchedules'])
            ->load(['pointWallet', 'preferredPaymentMethod', 'currency']);

        return new ProfileResource($data);
    }

    public function update_profile($data, $id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundException();
        }

        if (isset($data['image'])) {

            if ($user->image) {
                $this->deleteFile("public", "users", $user->image);
            }

            $path = $this->uploadFile("public", "users", $data['image']);

            $data['image'] = $path;
        }

        $user->update(
            collect($data)->except('image')->toArray()
                + (isset($data['image']) ? ['image' => $data['image']] : [])
        );

        return new ProfileResource($user->fresh()->load('currency'));
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
        $user = auth('user')->id();
        $delete = User::where('id', $user)->first();
        $delete->delete();
        return true;
    }

    public function update_email($request)
    {
        $user = auth('user')->user();


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
        $user = auth('user')->user();

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
        $user = auth('user')->user();

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

        $title = [
            'en' => 'Profile Updated',
            'ar' => 'تم تحديث معلومات حسابك',
        ];

        $body = [
            'en' => 'Your account information has been successfully updated.',
            'ar' => 'تم تحديث بيانات حسابك بنجاح.',
        ];

        $type = 'user';
        $payload = $user->id;

        //app(\App\Services\NotificationService::class)->send($user, $title, $body, 0, $type, $payload);

        \Filament\Notifications\Notification::make()
            ->title($title['ar'])
            ->body($body['ar'])
            ->sendToDatabase($user);

        return new UserResource($user);
    }

    public function storeOrUpdateToken(User $user, array $data): void
    {
        $user->fcmTokens()->updateOrCreate(
            ['device_id' => $data['deviceId']],
            ['fcm_token' => $data['fcmToken']]
        );
    }

    public function markterRequest(User $user, $data)
    {
        Log::info($user->is_affiliate && !$user->affiliate_approved);
        Log::info($user->is_affiliate && $user->affiliate_approved);

        if ($user->is_affiliate && !$user->affiliate_approved) {
            Log::info("Hello");
            throw new CustomExceptionWithMessage('You have submitted a marketing request, just wait for a response from the admin.');
        }
        if ($user->is_affiliate && $user->affiliate_approved) {
            throw new CustomExceptionWithMessage('You are a marketer, you dont need to submit an application.');
        }
        $user->update([
            'is_affiliate' => true,
        ]);
        //notification
    }

    public function updatePaymentGateway(int $userId, int $paymentMethodId): ProfileResource
    {
        $user = $this->model->findOrFail($userId);

        $user->update([
            'preferred_payment_method_id' => $paymentMethodId
        ]);

        return new ProfileResource($user->fresh()
            ->loadCount(['orders', 'userBasketSchedules'])
            ->load(['pointWallet', 'preferredPaymentMethod']));
    }
}
