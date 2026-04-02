<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện']);

$pageTitle = 'Nhân viên';
$flash = get_flash();

$errors = [];
$openModal = '';
$editData = null;

$search = trim($_GET['search'] ?? $_POST['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? $_POST['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function generateMaNV($conn) {
    $rs = mysqli_query($conn, "SELECT maNV FROM NhanVien ORDER BY maNV DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maNV'], 2));
        $newId = $num + 1;
    }
    return "NV" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

function back_url($search, $page) {
    return "nhanvien.php?search=" . urlencode($search) . "&p=" . intval($page);
}

function is_valid_phone($phone) {
    return preg_match('/^\d{10,12}$/', $phone);
}

function is_valid_email_format($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);

    $check = mysqli_query($conn, "SELECT * FROM MuonTra WHERE maNV='$id'");
    if (mysqli_num_rows($check) > 0) {
        set_flash('danger', 'Không thể xóa nhân viên do đã phát sinh phiếu mượn trả.');
    } else {
        mysqli_query($conn, "DELETE FROM NhanVien WHERE maNV='$id'");
        set_flash('success', 'Xóa nhân viên thành công.');
    }

    redirect(back_url($search, $page));
}

if (isset($_POST['add'])) {
    $maNV = generateMaNV($conn);
    $tenNV = trim($_POST['tenNV'] ?? '');
    $ngaySinh = trim($_POST['ngaySinh'] ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $gioiTinh = trim($_POST['gioiTinh'] ?? '');
    $soDT = trim($_POST['soDT'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $vaitro = trim($_POST['vaitro'] ?? '');
    $tenDangNhap = trim($_POST['tenDangNhap'] ?? '');
    $matKhau = trim($_POST['matKhau'] ?? '');

    if ($tenNV === '') $errors['tenNV'] = 'Tên nhân viên không được để trống';
    if ($ngaySinh === '') $errors['ngaySinh'] = 'Ngày sinh không được để trống';
    if ($diaChi === '') $errors['diaChi'] = 'Địa chỉ không được để trống';
    if ($gioiTinh === '') $errors['gioiTinh'] = 'Vui lòng chọn giới tính';

    if ($soDT === '') {
        $errors['soDT'] = 'Số điện thoại không được để trống';
    } elseif (!is_valid_phone($soDT)) {
        $errors['soDT'] = 'Số điện thoại phải là số và có từ 10 đến 12 chữ số';
    }

    if ($email === '') {
        $errors['email'] = 'Email không được để trống';
    } elseif (!is_valid_email_format($email)) {
        $errors['email'] = 'Email không đúng định dạng';
    }

    if ($vaitro === '') {
        $errors['vaitro'] = 'Vui lòng chọn vai trò';
    } elseif (!in_array($vaitro, ['Quản lý thư viện', 'Thủ thư'], true)) {
        $errors['vaitro'] = 'Vai trò không hợp lệ';
    }

    if ($tenDangNhap === '') $errors['tenDangNhap'] = 'Tên đăng nhập không được để trống';

    if ($matKhau === '') {
        $errors['matKhau'] = 'Mật khẩu không được để trống';
    } elseif (strlen($matKhau) < 6) {
        $errors['matKhau'] = 'Mật khẩu tối thiểu 6 ký tự';
    }

    if ($tenDangNhap !== '') {
        $userEsc = db_escape($conn, $tenDangNhap);
        $checkUser = mysqli_query($conn, "SELECT * FROM NhanVien WHERE tenDangNhap='$userEsc'");
        if (mysqli_num_rows($checkUser) > 0) {
            $errors['tenDangNhap'] = 'Tên đăng nhập đã tồn tại';
        }
    }

    if ($soDT !== '' && is_valid_phone($soDT)) {
        $phoneEsc = db_escape($conn, $soDT);
        $checkPhone = mysqli_query($conn, "SELECT * FROM NhanVien WHERE soDT='$phoneEsc'");
        if (mysqli_num_rows($checkPhone) > 0) {
            $errors['soDT'] = 'Số điện thoại đã tồn tại';
        }
    }

    if ($email !== '' && is_valid_email_format($email)) {
        $emailEsc = db_escape($conn, $email);
        $checkEmail = mysqli_query($conn, "SELECT * FROM NhanVien WHERE email='$emailEsc'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = 'Email đã tồn tại';
        }
    }

    if (empty($errors)) {
        $tenNV = db_escape($conn, $tenNV);
        $ngaySinh = db_escape($conn, $ngaySinh);
        $diaChi = db_escape($conn, $diaChi);
        $gioiTinh = db_escape($conn, $gioiTinh);
        $soDT = db_escape($conn, $soDT);
        $email = db_escape($conn, $email);
        $vaitro = db_escape($conn, $vaitro);
        $tenDangNhap = db_escape($conn, $tenDangNhap);
        $matKhauHash = db_escape($conn, password_hash($matKhau, PASSWORD_DEFAULT));

        mysqli_query($conn, "
            INSERT INTO NhanVien(maNV, tenNV, ngaySinh, diaChi, gioiTinh, soDT, email, vaitro, tenDangNhap, matKhau)
            VALUES('$maNV','$tenNV','$ngaySinh','$diaChi','$gioiTinh','$soDT','$email','$vaitro','$tenDangNhap','$matKhauHash')
        ");

        set_flash('success', 'Thêm nhân viên thành công.');
        redirect(back_url($search, $page));
    } else {
        $openModal = 'addModal';
    }
}

if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM NhanVien WHERE maNV='$id'");
    $editData = mysqli_fetch_assoc($q);
    if ($editData) $openModal = 'editModal';
}

if (isset($_POST['update'])) {
    $maNV = trim($_POST['maNV'] ?? '');
    $tenNV = trim($_POST['tenNV'] ?? '');
    $ngaySinh = trim($_POST['ngaySinh'] ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $gioiTinh = trim($_POST['gioiTinh'] ?? '');
    $soDT = trim($_POST['soDT'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $vaitro = trim($_POST['vaitro'] ?? '');
    $tenDangNhap = trim($_POST['tenDangNhap'] ?? '');
    $matKhau = trim($_POST['matKhau'] ?? '');

    if ($tenNV === '') $errors['tenNV'] = 'Tên nhân viên không được để trống';
    if ($ngaySinh === '') $errors['ngaySinh'] = 'Ngày sinh không được để trống';
    if ($diaChi === '') $errors['diaChi'] = 'Địa chỉ không được để trống';
    if ($gioiTinh === '') $errors['gioiTinh'] = 'Vui lòng chọn giới tính';

    if ($soDT === '') {
        $errors['soDT'] = 'Số điện thoại không được để trống';
    } elseif (!is_valid_phone($soDT)) {
        $errors['soDT'] = 'Số điện thoại phải là số và có từ 10 đến 12 chữ số';
    }

    if ($email === '') {
        $errors['email'] = 'Email không được để trống';
    } elseif (!is_valid_email_format($email)) {
        $errors['email'] = 'Email không đúng định dạng';
    }

    if ($vaitro === '') {
        $errors['vaitro'] = 'Vui lòng chọn vai trò';
    } elseif (!in_array($vaitro, ['Quản lý thư viện', 'Thủ thư'], true)) {
        $errors['vaitro'] = 'Vai trò không hợp lệ';
    }

    if ($tenDangNhap === '') $errors['tenDangNhap'] = 'Tên đăng nhập không được để trống';

    if ($matKhau !== '' && strlen($matKhau) < 6) {
        $errors['matKhau'] = 'Mật khẩu tối thiểu 6 ký tự';
    }

    $maNVEsc = db_escape($conn, $maNV);

    if ($tenDangNhap !== '') {
        $userEsc = db_escape($conn, $tenDangNhap);
        $checkUser = mysqli_query($conn, "SELECT * FROM NhanVien WHERE tenDangNhap='$userEsc' AND maNV<>'$maNVEsc'");
        if (mysqli_num_rows($checkUser) > 0) {
            $errors['tenDangNhap'] = 'Tên đăng nhập đã tồn tại';
        }
    }

    if ($soDT !== '' && is_valid_phone($soDT)) {
        $phoneEsc = db_escape($conn, $soDT);
        $checkPhone = mysqli_query($conn, "SELECT * FROM NhanVien WHERE soDT='$phoneEsc' AND maNV<>'$maNVEsc'");
        if (mysqli_num_rows($checkPhone) > 0) {
            $errors['soDT'] = 'Số điện thoại đã tồn tại';
        }
    }

    if ($email !== '' && is_valid_email_format($email)) {
        $emailEsc = db_escape($conn, $email);
        $checkEmail = mysqli_query($conn, "SELECT * FROM NhanVien WHERE email='$emailEsc' AND maNV<>'$maNVEsc'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = 'Email đã tồn tại';
        }
    }

    $editData = [
        'maNV' => $maNV,
        'tenNV' => $tenNV,
        'ngaySinh' => $ngaySinh,
        'diaChi' => $diaChi,
        'gioiTinh' => $gioiTinh,
        'soDT' => $soDT,
        'email' => $email,
        'vaitro' => $vaitro,
        'tenDangNhap' => $tenDangNhap
    ];

    if (empty($errors)) {
        $tenNV = db_escape($conn, $tenNV);
        $ngaySinh = db_escape($conn, $ngaySinh);
        $diaChi = db_escape($conn, $diaChi);
        $gioiTinh = db_escape($conn, $gioiTinh);
        $soDT = db_escape($conn, $soDT);
        $email = db_escape($conn, $email);
        $vaitro = db_escape($conn, $vaitro);
        $tenDangNhap = db_escape($conn, $tenDangNhap);

        $sql = "
            UPDATE NhanVien
            SET tenNV='$tenNV',
                ngaySinh='$ngaySinh',
                diaChi='$diaChi',
                gioiTinh='$gioiTinh',
                soDT='$soDT',
                email='$email',
                vaitro='$vaitro',
                tenDangNhap='$tenDangNhap'
        ";

        if ($matKhau !== '') {
            $matKhauHash = db_escape($conn, password_hash($matKhau, PASSWORD_DEFAULT));
            $sql .= ", matKhau='$matKhauHash'";
        }

        $sql .= " WHERE maNV='$maNVEsc'";

        mysqli_query($conn, $sql);

        set_flash('success', 'Cập nhật nhân viên thành công.');
        redirect(back_url($search, $page));
    } else {
        $openModal = 'editModal';
    }
}

$searchEsc = db_escape($conn, $search);

$countSql = "
    SELECT COUNT(*) AS total
    FROM NhanVien
    WHERE maNV LIKE '%$searchEsc%'
       OR tenNV LIKE '%$searchEsc%'
       OR tenDangNhap LIKE '%$searchEsc%'
       OR vaitro LIKE '%$searchEsc%'
";
$countRs = mysqli_query($conn, $countSql);
$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$dataSql = "
    SELECT * FROM NhanVien
    WHERE maNV LIKE '%$searchEsc%'
       OR tenNV LIKE '%$searchEsc%'
       OR tenDangNhap LIKE '%$searchEsc%'
       OR vaitro LIKE '%$searchEsc%'
    ORDER BY maNV ASC
    LIMIT $offset, $limit
";
$result = mysqli_query($conn, $dataSql);

require_once __DIR__ . '/includes/header.php';
?>
<div class="dashboard-layout">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> rounded-4"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <div class="content-toolbar">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Quản lý nhân viên</h2>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm nhân viên
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm nhân viên..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Tên nhân viên</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Tên đăng nhập</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= e($row['maNV']) ?></td>
                            <td><?= e($row['tenNV']) ?></td>
                            <td><?= e($row['soDT']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td>
                                <?php if ($row['vaitro'] === 'Quản lý thư viện'): ?>
                                    <span class="badge-soft-warning">Quản lý thư viện</span>
                                <?php else: ?>
                                    <span class="badge-soft-success">Thủ thư</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($row['tenDangNhap']) ?></td>
                            <td class="action-btns">
                                <a href="?edit=<?= urlencode($row['maNV']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('nhanvien.php?delete=<?= urlencode($row['maNV']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="mt-4">
            <ul class="pagination">
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?search=<?= urlencode($search) ?>&p=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php endif; ?>
    </main>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm nhân viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên nhân viên</label>
                            <input type="text" name="tenNV" class="form-control <?= isset($errors['tenNV']) ? 'is-invalid' : '' ?>" value="<?= e(old('tenNV')) ?>">
                            <?php if(isset($errors['tenNV'])): ?><div class="invalid-feedback"><?= e($errors['tenNV']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="ngaySinh" class="form-control <?= isset($errors['ngaySinh']) ? 'is-invalid' : '' ?>" value="<?= e(old('ngaySinh')) ?>">
                            <?php if(isset($errors['ngaySinh'])): ?><div class="invalid-feedback"><?= e($errors['ngaySinh']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="diaChi" class="form-control <?= isset($errors['diaChi']) ? 'is-invalid' : '' ?>" value="<?= e(old('diaChi')) ?>">
                            <?php if(isset($errors['diaChi'])): ?><div class="invalid-feedback"><?= e($errors['diaChi']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gioiTinh" class="form-select <?= isset($errors['gioiTinh']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?= old('gioiTinh') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= old('gioiTinh') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            </select>
                            <?php if(isset($errors['gioiTinh'])): ?><div class="invalid-feedback"><?= e($errors['gioiTinh']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="soDT" class="form-control <?= isset($errors['soDT']) ? 'is-invalid' : '' ?>" value="<?= e(old('soDT')) ?>">
                            <?php if(isset($errors['soDT'])): ?><div class="invalid-feedback"><?= e($errors['soDT']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= e(old('email')) ?>">
                            <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vai trò</label>
                            <select name="vaitro" class="form-select <?= isset($errors['vaitro']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn vai trò --</option>
                                <option value="Quản lý thư viện" <?= old('vaitro') === 'Quản lý thư viện' ? 'selected' : '' ?>>Quản lý thư viện</option>
                                <option value="Thủ thư" <?= old('vaitro') === 'Thủ thư' ? 'selected' : '' ?>>Thủ thư</option>
                            </select>
                            <?php if(isset($errors['vaitro'])): ?><div class="invalid-feedback"><?= e($errors['vaitro']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" name="tenDangNhap" class="form-control <?= isset($errors['tenDangNhap']) ? 'is-invalid' : '' ?>" value="<?= e(old('tenDangNhap')) ?>">
                            <?php if(isset($errors['tenDangNhap'])): ?><div class="invalid-feedback"><?= e($errors['tenDangNhap']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="matKhau" class="form-control <?= isset($errors['matKhau']) ? 'is-invalid' : '' ?>">
                            <?php if(isset($errors['matKhau'])): ?><div class="invalid-feedback"><?= e($errors['matKhau']) ?></div><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button name="add" class="btn btn-orange">Lưu</button>
                    <button type="button" class="btn btn-secondary rounded-4" data-bs-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa nhân viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maNV" value="<?= e($editData['maNV']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên nhân viên</label>
                            <input type="text" name="tenNV" class="form-control <?= isset($errors['tenNV']) ? 'is-invalid' : '' ?>" value="<?= e($editData['tenNV']) ?>">
                            <?php if(isset($errors['tenNV'])): ?><div class="invalid-feedback"><?= e($errors['tenNV']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="ngaySinh" class="form-control <?= isset($errors['ngaySinh']) ? 'is-invalid' : '' ?>" value="<?= e($editData['ngaySinh']) ?>">
                            <?php if(isset($errors['ngaySinh'])): ?><div class="invalid-feedback"><?= e($errors['ngaySinh']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="diaChi" class="form-control <?= isset($errors['diaChi']) ? 'is-invalid' : '' ?>" value="<?= e($editData['diaChi']) ?>">
                            <?php if(isset($errors['diaChi'])): ?><div class="invalid-feedback"><?= e($errors['diaChi']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gioiTinh" class="form-select <?= isset($errors['gioiTinh']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?= ($editData['gioiTinh'] ?? '') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= ($editData['gioiTinh'] ?? '') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            </select>
                            <?php if(isset($errors['gioiTinh'])): ?><div class="invalid-feedback"><?= e($errors['gioiTinh']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="soDT" class="form-control <?= isset($errors['soDT']) ? 'is-invalid' : '' ?>" value="<?= e($editData['soDT']) ?>">
                            <?php if(isset($errors['soDT'])): ?><div class="invalid-feedback"><?= e($errors['soDT']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= e($editData['email']) ?>">
                            <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vai trò</label>
                            <select name="vaitro" class="form-select <?= isset($errors['vaitro']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn vai trò --</option>
                                <option value="Quản lý thư viện" <?= ($editData['vaitro'] ?? '') === 'Quản lý thư viện' ? 'selected' : '' ?>>Quản lý thư viện</option>
                                <option value="Thủ thư" <?= ($editData['vaitro'] ?? '') === 'Thủ thư' ? 'selected' : '' ?>>Thủ thư</option>
                            </select>
                            <?php if(isset($errors['vaitro'])): ?><div class="invalid-feedback"><?= e($errors['vaitro']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" name="tenDangNhap" class="form-control <?= isset($errors['tenDangNhap']) ? 'is-invalid' : '' ?>" value="<?= e($editData['tenDangNhap']) ?>">
                            <?php if(isset($errors['tenDangNhap'])): ?><div class="invalid-feedback"><?= e($errors['tenDangNhap']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Mật khẩu mới (để trống nếu không đổi)</label>
                            <input type="password" name="matKhau" class="form-control <?= isset($errors['matKhau']) ? 'is-invalid' : '' ?>">
                            <?php if(isset($errors['matKhau'])): ?><div class="invalid-feedback"><?= e($errors['matKhau']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button name="update" class="btn btn-orange">Cập nhật</button>
                    <button type="button" class="btn btn-secondary rounded-4" data-bs-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if ($openModal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('<?= $openModal ?>'));
    modal.show();
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>