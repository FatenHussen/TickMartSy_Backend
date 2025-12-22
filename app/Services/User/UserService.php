<?php

namespace App\Services\User;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\InvalidVerificationCodeException;
use App\Exceptions\NotFoundException;
use App\Http\Resources\User\UserResource;
use App\Jobs\SendOtpJob;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Verification;
use App\Services\BaseService;
use App\Traits\FileTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{
    use FileTrait;

    public function __construct(public User $model) {}

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

        $user = User::where($field, $data[$field])->first();

        if (!$user) {
            throw new NotFoundException();
        }

        return $user;
    }

    private function createOtp(
        User $user,
        string $type,
        ?string $value = null,
        int $minutes = 60
    ): Verification {
        return Verification::create([
            'code'    => rand(10000, 99999),
            'user_id' => $user->id,
            'type'    => $type,
            'value'   => $value,
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

        $user = User::create([
            $field           => $data[$field],
            'password'       => $data['password'],
            'name'           => $data['name'],
            'city_id'        => $data['city_id'],
            'governorate_id' => $data['governorate_id'],
        ]);

        $verification = $this->createOtp($user, 'verification');
        $this->sendOtp($user, $verification, $field);

        return true;
    }

    public function login(array $data)
    {
        $field = $this->resolveField($data);
        $user  = $this->resolveUser($data);

        if (!Hash::check($data['password'], $user->password)) {
            throw new CustomExceptionWithMessage('wrong_credential');
        }

        if (!$user->{$field . '_verified_at'}) {
            $verification = $this->createOtp($user, 'verification');
            $this->sendOtp($user, $verification, $field);

            throw new InvalidVerificationCodeException();
        }

        return new UserResource($user);
    }

    public static function logout(): bool
    {
        auth()->user()?->currentAccessToken()?->delete();
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
            throw new CustomExceptionWithMessage('Otp_valid');
        }

        $user->update([$field . '_verified_at' => now()]);
        $verification->update(['verified_at' => now()]);

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
            throw new CustomExceptionWithMessage('password_valid');
        }

        return new UserResource($user);
    }

    public function resetPassword(int $id, string $newPassword)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundException();
        }

        $user->update(['password' => Hash::make($newPassword)]);
        auth()->user()?->tokens()->delete();

        return new UserResource($user);
    }

    public function updatePassword(int $id, array $data): bool
    {
        $user = User::findOrFail($id);

        if (!Hash::check($data['old_password'], $user->password)) {
            throw new CustomExceptionWithMessage('wrong_password');
        }

        $user->update(['password' => Hash::make($data['new_password'])]);
        return true;
    }

    /* =========================
        Update Contact
    ========================= */

    public function updateContact(string $type, string $value): bool
    {
        $user = auth('users')->user();

        $verification = $this->createOtp($user, $type, $value);

        $type === 'update_phone'
            ? SendOtpJob::dispatch($value, $verification->code)
            : Mail::to($value)->send(new OtpMail($user, $verification->code, $verification->end_at));

        return true;
    }

    public function verifyUpdate(array $data)
    {
        $user = auth('users')->user();

        $verification = Verification::where('user_id', $user->id)
            ->where('code', $data['code'])
            ->whereIn('type', ['update_email', 'update_phone'])
            ->whereNull('verified_at')
            ->where('end_at', '>', now())
            ->latest()
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('otp_invalid');
        }

        $field = $verification->type === 'update_email' ? 'email' : 'phone';

        $user->update([
            $field => $verification->value,
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
        $userId = auth('users')->id();
        User::where('id', $userId)->delete();
        return true;
    }
}
