<?php

namespace App\Services\Driver;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\NotFoundException;
use App\Http\Resources\Driver\DriverResource;
use App\Http\Resources\Driver\DriverProfileResource;
use App\Jobs\SendOtpJob;
use App\Models\Driver;
use App\Models\Verification;
use App\Traits\FileTrait;
use Illuminate\Support\Facades\Hash;

class DriverService
{
    use FileTrait;

    public function __construct(public Driver $model)
    {
        $this->model = $model;
    }

    /* =========================
        Helpers
    ========================= */

    private function resolveDriver(array $data): Driver
    {
        $driver = $this->model
            ->where('phone', $data['phone'])
            ->first();

        if (!$driver) {
            throw new NotFoundException();
        }

        return $driver;
    }
    private function sendOtp(Driver $driver, Verification $verification): void
    {
        SendOtpJob::dispatch(
            $driver->phone,
            $verification->code
        );
    }
    private function createOtp(
        Driver $driver,
        string $type,
        int $minutes = 60
    ): Verification {
        return Verification::create([
            'code'    => rand(10000, 99999),
            'driver_id' => $driver->id,
            'type'    => $type,
            'end_at'  => now()->addMinutes($minutes),
        ]);
    }

    /* =========================
        Auth
    ========================= */

    public function login(array $data)
    {
        $driver = $this->resolveDriver($data);

        if (!Hash::check($data['password'], $driver->password)) {
            throw new CustomExceptionWithMessage('custom.wrong_credential');
        }

        return new DriverResource($driver);
    }

    public static function logout(): bool
    {
        auth('driver')->user()->currentAccessToken()?->delete();
        return true;
    }

    /* =========================
        Password Reset
    ========================= */

    public function sendPasswordOtp(array $data): bool
    {
        $driver = $this->resolveDriver($data);

        $verification = $this->createOtp($driver, 'reset_password');
        $this->sendOtp($driver, $verification, 'phone');

        return true;
    }

    public function verifyPassword(array $data)
    {
        $driver = $this->resolveDriver($data);

        $verification = Verification::where([
            'driver_id' => $driver->id,
            'code'    => $data['code'],
            'type'    => 'reset_password',
        ])
            ->where('end_at', '>', now())
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('custom.password_valid');
        }

        return new DriverResource($driver);
    }

    public function resetPassword(int $id, string $newPassword)
    {
        $driver = $this->model->find($id);

        if (!$driver) {
            throw new NotFoundException();
        }

        $driver->update([
            'password' => Hash::make($newPassword),
        ]);

        auth('driver')->user()->tokens()->delete();

        return new DriverResource($driver);
    }


    public function updateStatus($driverId, $status)
    {
        $driver = $this->model->findOrFail($driverId);
        $driver->update(['status' => $status]);

        return true;
    }

    /* =========================
        Profile Management
    ========================= */

    public function getProfile()
    {
        $driver = auth('driver')->user()->load(['areas', 'orders', 'ratings']);
        return new DriverProfileResource($driver);
    }

    public function updateProfile($data, $id)
    {
        $driver = $this->model->findOrFail($id);

        // Handle image upload
        if (isset($data['image'])) {
            // Delete old image if exists
            if ($driver->image) {
                $this->deleteFile("public", "drivers", $driver->image);
            }

            // Upload new image
            $path = $this->uploadFile("public", "drivers", $data['image']);
            $data['image'] = $path;
        }

        $driver->update($data);

        return new DriverProfileResource($driver->fresh()->load(['areas', 'orders', 'ratings']));
    }

    public function updatePhone($request)
    {
        $driver = auth('driver')->user();

        $otp = rand(10000, 99999);

        $verification = Verification::create([
            'code' => $otp,
            'driver_id' => $driver->id,
            'end_at' => now()->addMinutes(60),
            'type' => 'update_phone',
            'value' => $request['phone'],
        ]);

        SendOtpJob::dispatch($request['phone'], $otp);

        return true;
    }

    public function verifyUpdate($request)
    {
        $driver = auth('driver')->user();

        $verification = Verification::where('driver_id', $driver->id)
            ->where('code', $request['code'])
            ->where('type', 'update_phone')
            ->whereNull('verified_at')
            ->where('end_at', '>', now())
            ->latest()
            ->first();

        if (!$verification) {
            throw new CustomExceptionWithMessage('custom.otp_invalid');
        }

        $driver->update(['phone' => $verification->value]);
        $verification->update(['verified_at' => now()]);

        return new DriverResource($driver);
    }

    public function storeOrUpdateToken(Driver $user, array $data): void
    {
        $user->fcmTokens()->updateOrCreate(
            ['device_id' => $data['deviceId']],
            ['fcm_token' => $data['fcmToken']]
        );
    }
}
