<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';

if (is_logged_in()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = db_escape($conn, $_POST['tenDangNhap'] ?? '');
    $password = trim($_POST['matKhau'] ?? '');

    $sql = "SELECT * FROM NhanVien WHERE tenDangNhap = '$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    $passwordMatched = false;
    if ($user) {
        if (password_verify($password, $user['matKhau'])) {
            $passwordMatched = true;
        } elseif ($password === $user['matKhau']) {
            $passwordMatched = true;
        }
    }

    if ($passwordMatched) {
        if (!in_array($user['vaitro'], ['Quản lý thư viện', 'Thủ thư'], true)) {
            $error = 'Tài khoản chưa được gán vai trò hợp lệ.';
        } else {
            $_SESSION['user'] = $user;
            set_flash('success', 'Đăng nhập thành công.');
            redirect('index.php');
        }
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Library Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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

            <?php if ($error): ?>
                <div class="alert alert-danger rounded-4"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="d-grid gap-3">
                <div>
                    <label class="form-label">Tên đăng nhập</label>
                    <div class="input-group">
                        <span class="input-group-text rounded-start-4 border-end-0 bg-white"><i class="bi bi-person"></i></span>
                        <input type="text" name="tenDangNhap" class="form-control rounded-end-4 border-start-0" placeholder="Nhập tên đăng nhập" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text rounded-start-4 border-end-0 bg-white"><i class="bi bi-lock"></i></span>
                        <input type="password" name="matKhau" class="form-control rounded-end-4 border-start-0" placeholder="Nhập mật khẩu" required>
                    </div>
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