<?php
require_once __DIR__ . '/helpers.php';
$user = current_user();
$currentFile = basename($_SERVER['PHP_SELF']);

function nav_active($file, $currentFile) {
    return $file === $currentFile ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-title">Hệ thống</div>
        <div class="brand-sub">Quản lý thư viện</div>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?= nav_active('index.php', $currentFile) ?>"><i class="bi bi-grid"></i> Dashboard</a>
        <a href="sach.php" class="nav-item <?= nav_active('sach.php', $currentFile) ?>"><i class="bi bi-book"></i> Quản lý Sách</a>
        <a href="docgia.php" class="nav-item <?= nav_active('docgia.php', $currentFile) ?>"><i class="bi bi-people"></i> Quản lý Độc giả</a>
        <a href="muontra.php" class="nav-item <?= nav_active('muontra.php', $currentFile) ?>"><i class="bi bi-arrow-left-right"></i> Quản lý Mượn trả</a>
        <a href="tacgia.php" class="nav-item <?= nav_active('tacgia.php', $currentFile) ?>"><i class="bi bi-pen"></i> Quản lý Tác giả</a>
        <a href="theloai.php" class="nav-item <?= nav_active('theloai.php', $currentFile) ?>"><i class="bi bi-tags"></i> Quản lý Thể loại</a>
        <a href="nhaxuatban.php" class="nav-item <?= nav_active('nhaxuatban.php', $currentFile) ?>"><i class="bi bi-building"></i> Quản lý Nhà xuất bản</a>
        <a href="ngonngu.php" class="nav-item <?= nav_active('ngonngu.php', $currentFile) ?>"><i class="bi bi-translate"></i> Quản lý Ngôn ngữ</a>
        <a href="kesach.php" class="nav-item <?= nav_active('kesach.php', $currentFile) ?>"><i class="bi bi-bookshelf"></i> Quản lý Kệ sách</a>
        <a href="thethuvien.php" class="nav-item <?= nav_active('thethuvien.php', $currentFile) ?>"><i class="bi bi-person-vcard"></i> Quản lý Thẻ thư viện</a>

        <?php if (is_manager()): ?>
            <a href="nhanvien.php" class="nav-item <?= nav_active('nhanvien.php', $currentFile) ?>"><i class="bi bi-person-badge"></i> Quản lý Nhân viên</a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div>Đăng nhập bởi</div>
        <strong><?= e($user['tenNV'] ?? '') ?></strong>
    </div>

    <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
</aside>