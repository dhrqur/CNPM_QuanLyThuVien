<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Hệ thống Quản lý thư viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-visual d-flex flex-column justify-content-between">
            <div>
                <div class="hero-pill"><i class="bi bi-stars"></i> NHÓM 7 - 74DCHT23</div>
                <h1 class="display-6 fw-bold">HỆ THỐNG</h1>
                <h2 class="display-6 fw-bold">Quản lý thư viện</h2>
            </div>
        </div>

        <div class="auth-form">
            <h2 class="fw-bold mb-2">Chào mừng quay lại</h2>
            <p class="text-muted mb-4">Vui lòng nhập tài khoản để vào trang quản trị.</p>

            @if ($errors->has('login'))
                <div class="alert alert-danger rounded-4">{{ $errors->first('login') }}</div>
            @endif

            <form method="POST" action="{{ route('login.perform') }}" class="d-grid gap-3">
                @csrf

                <div>
                    <label class="form-label">Tên đăng nhập</label>
                    <div class="input-group">
                        <span class="input-group-text rounded-start-4 border-end-0 bg-white"><i class="bi bi-person"></i></span>
                        <input
                            type="text"
                            name="tenDangNhap"
                            value="{{ old('tenDangNhap') }}"
                            class="form-control rounded-end-4 border-start-0"
                            placeholder="Nhập tên đăng nhập"
                            required
                        >
                    </div>
                </div>

                <div>
                    <label class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text rounded-start-4 border-end-0 bg-white"><i class="bi bi-lock"></i></span>
                        <input
                            type="password"
                            name="matKhau"
                            class="form-control rounded-end-4 border-start-0"
                            placeholder="Nhập mật khẩu"
                            required
                        >
                    </div>
                </div>

                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remember"
                        id="remember"
                        value="1"
                        @checked(old('remember'))
                    >
                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                </div>

                <button type="submit" class="btn btn-primary-custom py-3 fw-semibold">Đăng nhập</button>
            </form>

            <div class="mt-4 small text-muted">
                Tài khoản mẫu theo dữ liệu hiện tại: <strong>admin</strong> / <strong>123456</strong>
            </div>
        </div>
    </div>
</div>
</body>
</html>
