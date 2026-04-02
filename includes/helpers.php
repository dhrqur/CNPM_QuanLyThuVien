<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function current_role() {
    return $_SESSION['user']['vaitro'] ?? '';
}

function is_manager() {
    return current_role() === 'Quản lý thư viện';
}

function is_librarian() {
    return current_role() === 'Thủ thư';
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_roles(array $roles) {
    require_login();
    if (!in_array(current_role(), $roles, true)) {
        set_flash('danger', 'Bạn không có quyền thực hiện chức năng này.');
        redirect('index.php');
    }
}

function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function card_status_class($status) {
    switch ($status) {
        case 'Đã trả':
        case 'Còn':
        case 'Còn hạn':
            return 'success';
        case 'Quá hạn':
        case 'Hết':
        case 'Hết hạn':
            return 'danger';
        default:
            return 'warning';
    }
}

function db_escape($conn, $value) {
    return mysqli_real_escape_string($conn, trim((string)$value));
}

function db_one($result) {
    return mysqli_fetch_assoc($result);
}

function old($key, $default = '') {
    return $_POST[$key] ?? $default;
}
?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | Library Admin' : 'Library Admin' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body></body>