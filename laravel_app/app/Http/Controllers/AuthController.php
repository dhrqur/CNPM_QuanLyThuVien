<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 300;

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'tenDangNhap' => ['required', 'string', 'max:50'],
            'matKhau' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'tenDangNhap.required' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.',
            'matKhau.required' => 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.',
        ]);

        $throttleKey = $this->throttleKey((string) $credentials['tenDangNhap'], (string) $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            return back()->withErrors([
                'login' => 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau.',
            ])->withInput($request->except('matKhau'));
        }

        $user = User::where('tenDangNhap', $credentials['tenDangNhap'])->first();

        if (!$user) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return back()
                ->withErrors(['login' => 'Tên đăng nhập hoặc mật khẩu không chính xác.'])
                ->withInput($request->except('matKhau'));
        }

        $status = $user->getAccountStatus();
        if ($status === 'Bị khóa') {
            return back()
                ->withErrors(['login' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ thư viện để biết thêm chi tiết.'])
                ->withInput($request->except('matKhau'));
        }

        if ($status === 'Chờ kích hoạt') {
            return back()
                ->withErrors(['login' => 'Tài khoản đang chờ kích hoạt.'])
                ->withInput($request->except('matKhau'));
        }

        $rawPassword = (string) $credentials['matKhau'];
        $storedPassword = (string) ($user->matKhau ?? '');

        // Convert legacy plaintext password to hashed value and only verify with Hash::check.
        if (!$this->isHashedPassword($storedPassword)) {
            $user->matKhau = Hash::make($storedPassword);
            $user->save();
            $storedPassword = (string) $user->matKhau;
        }

        $matched = Hash::check($rawPassword, $storedPassword);

        if (!$matched) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return back()
                ->withErrors(['login' => 'Tên đăng nhập hoặc mật khẩu không chính xác.'])
                ->withInput($request->except('matKhau'));
        }

        if (!in_array($user->vaitro, ['Quản lý thư viện', 'Thủ thư'], true)) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            return back()
                ->withErrors(['login' => 'Tài khoản chưa được gán vai trò hợp lệ.'])
                ->withInput($request->except('matKhau'));
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, (bool) ($credentials['remember'] ?? false));
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Bạn đã đăng xuất khỏi hệ thống.');
    }

    private function throttleKey(string $username, string $ip): string
    {
        return 'web-login:'.mb_strtolower($username).'|'.$ip;
    }

    private function isHashedPassword(string $password): bool
    {
        return strpos($password, '$2y$') === 0
            || strpos($password, '$argon2i$') === 0
            || strpos($password, '$argon2id$') === 0;
    }
}
