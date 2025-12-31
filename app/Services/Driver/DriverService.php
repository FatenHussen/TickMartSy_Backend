<?php

namespace App\Services\Driver;

use App\Exceptions\CustomExceptionWithMessage;
use App\Exceptions\NotFoundException;
use App\Http\Resources\Driver\DriverResource;
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
}
