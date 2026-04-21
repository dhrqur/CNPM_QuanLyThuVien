<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthApiController extends BaseApiController
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 300;

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'tenDangNhap' => ['required', 'string', 'max:50'],
                'matKhau' => ['required', 'string'],
                'remember_me' => ['nullable', 'boolean'],
            ], [
                'tenDangNhap.required' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.',
                'matKhau.required' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.',
            ]);
        } catch (ValidationException $e) {
            return $this->error('Dữ liệu không hợp lệ.', 422, $e->errors());
        }

        $throttleKey = $this->throttleKey((string) $credentials['tenDangNhap'], (string) $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $retryAfter = RateLimiter::availableIn($throttleKey);

            return $this->error(
                'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau hoặc hoàn thành Captcha.',
                429,
                [
                    'captcha_required' => true,
                    'retry_after_seconds' => $retryAfter,
                ]
            );
        }

        $user = User::where('tenDangNhap', $credentials['tenDangNhap'])->first();

        if (!$user) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return $this->invalidCredentialsResponse($throttleKey);
        }

        $status = $user->getAccountStatus();
        if ($status === 'Bị khóa') {
            return $this->error(
                'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ thư viện để biết thêm chi tiết.',
                423
            );
        }

        if ($status === 'Chờ kích hoạt') {
            return $this->error('Tài khoản đang chờ kích hoạt.', 403);
        }

        $storedPassword = (string) ($user->matKhau ?? '');
        if ($storedPassword === '') {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return $this->invalidCredentialsResponse($throttleKey);
        }

        // Migrate legacy plaintext password to hash and only use Hash::check afterwards.
        if (!$this->isHashedPassword($storedPassword)) {
            $user->matKhau = Hash::make($storedPassword);
            $user->save();
            $storedPassword = (string) $user->matKhau;
        }

        if (!Hash::check((string) $credentials['matKhau'], $storedPassword)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return $this->invalidCredentialsResponse($throttleKey);
        }

        RateLimiter::clear($throttleKey);

        $remember = (bool) ($credentials['remember_me'] ?? false);
        $expiresAt = $remember ? now()->addDays(30) : now()->addHours(8);
        $token = $user->createToken('library-api', ['*'], $expiresAt)->plainTextToken;

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => [
                'maNV' => $user->maNV,
                'tenNV' => $user->tenNV,
                'tenDangNhap' => $user->tenDangNhap,
                'vaitro' => $user->vaitro,
                'trangThaiTK' => $status,
            ],
        ], 'Đăng nhập thành công.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'maNV' => optional($user)->maNV,
            'tenNV' => optional($user)->tenNV,
            'tenDangNhap' => optional($user)->tenDangNhap,
            'vaitro' => optional($user)->vaitro,
            'trangThaiTK' => optional($user)->getAccountStatus(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = optional($request->user())->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return $this->success(null, 'Đăng xuất thành công.');
    }

    private function invalidCredentialsResponse(string $throttleKey): JsonResponse
    {
        $remainingAttempts = RateLimiter::retriesLeft($throttleKey, self::MAX_ATTEMPTS);

        return $this->error(
            'Tên đăng nhập hoặc mật khẩu không chính xác.',
            401,
            [
                'remaining_attempts' => $remainingAttempts,
                'captcha_required' => $remainingAttempts <= 2,
            ]
        );
    }

    private function throttleKey(string $username, string $ip): string
    {
        return 'api-login:'.mb_strtolower($username).'|'.$ip;
    }

    private function isHashedPassword(string $password): bool
    {
        return strpos($password, '$2y$') === 0
            || strpos($password, '$argon2i$') === 0
            || strpos($password, '$argon2id$') === 0;
    }
}
