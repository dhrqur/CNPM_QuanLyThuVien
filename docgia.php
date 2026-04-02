<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện', 'Thủ thư']);

$pageTitle = 'Độc giả';
$flash = get_flash();

$errors = [];
$openModal = '';
$editData = null;

$search = trim($_GET['search'] ?? $_POST['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? $_POST['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function back_url_docgia($search, $page) {
    return "docgia.php?search=" . urlencode($search) . "&p=" . intval($page);
}

function generateMaDG($conn) {
    $rs = mysqli_query($conn, "SELECT maDG FROM DocGia ORDER BY maDG DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maDG'], 2));
        $newId = $num + 1;
    }
    return "DG" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

function is_valid_phone($phone) {
    return preg_match('/^\d{10,12}$/', $phone);
}

function is_valid_email_format($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);

    $checkMuon = mysqli_query($conn, "SELECT * FROM MuonTra WHERE maDG='$id' AND trangThai IN ('Đang mượn','Quá hạn')");
    $checkThe = mysqli_query($conn, "SELECT * FROM TheThuVien WHERE maDG='$id' AND ngayHetHan >= CURDATE()");

    if (mysqli_num_rows($checkMuon) > 0 || mysqli_num_rows($checkThe) > 0) {
        set_flash('danger', 'Không thể xóa độc giả vì đang mượn sách hoặc thẻ còn hạn.');
    } else {
        mysqli_query($conn, "DELETE FROM DocGia WHERE maDG='$id'");
        set_flash('success', 'Xóa độc giả thành công.');
    }

    redirect(back_url_docgia($search, $page));
}

if (isset($_POST['add'])) {
    $maDG = generateMaDG($conn);
    $tenDG = trim($_POST['tenDG'] ?? '');
    $ngaySinh = trim($_POST['ngaySinh'] ?? '');
    $gioiTinh = trim($_POST['gioiTinh'] ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $soDT = trim($_POST['soDT'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($tenDG === '') $errors['tenDG'] = 'Tên độc giả không được để trống';
    if ($ngaySinh === '') $errors['ngaySinh'] = 'Ngày sinh không được để trống';
    if ($gioiTinh === '') $errors['gioiTinh'] = 'Vui lòng chọn giới tính';
    if ($diaChi === '') $errors['diaChi'] = 'Địa chỉ không được để trống';

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

    if ($soDT !== '' && is_valid_phone($soDT)) {
        $phoneEsc = db_escape($conn, $soDT);
        $checkPhone = mysqli_query($conn, "SELECT * FROM DocGia WHERE soDT='$phoneEsc'");
        if (mysqli_num_rows($checkPhone) > 0) {
            $errors['soDT'] = 'Số điện thoại đã tồn tại';
        }
    }

    if ($email !== '' && is_valid_email_format($email)) {
        $emailEsc = db_escape($conn, $email);
        $checkEmail = mysqli_query($conn, "SELECT * FROM DocGia WHERE email='$emailEsc'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = 'Email đã tồn tại';
        }
    }

    if (empty($errors)) {
        $tenDG = db_escape($conn, $tenDG);
        $ngaySinh = db_escape($conn, $ngaySinh);
        $gioiTinh = db_escape($conn, $gioiTinh);
        $diaChi = db_escape($conn, $diaChi);
        $soDT = db_escape($conn, $soDT);
        $email = db_escape($conn, $email);

        mysqli_query($conn, "
            INSERT INTO DocGia(maDG, tenDG, ngaySinh, gioiTinh, diaChi, soDT, email)
            VALUES('$maDG','$tenDG','$ngaySinh','$gioiTinh','$diaChi','$soDT','$email')
        ");

        set_flash('success', 'Thêm độc giả thành công.');
        redirect(back_url_docgia($search, $page));
    } else {
        $openModal = 'addModal';
    }
}

if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM DocGia WHERE maDG='$id'");
    $editData = mysqli_fetch_assoc($q);
    if ($editData) $openModal = 'editModal';
}

if (isset($_POST['update'])) {
    $maDG = trim($_POST['maDG'] ?? '');
    $tenDG = trim($_POST['tenDG'] ?? '');
    $ngaySinh = trim($_POST['ngaySinh'] ?? '');
    $gioiTinh = trim($_POST['gioiTinh'] ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $soDT = trim($_POST['soDT'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($tenDG === '') $errors['tenDG'] = 'Tên độc giả không được để trống';
    if ($ngaySinh === '') $errors['ngaySinh'] = 'Ngày sinh không được để trống';
    if ($gioiTinh === '') $errors['gioiTinh'] = 'Vui lòng chọn giới tính';
    if ($diaChi === '') $errors['diaChi'] = 'Địa chỉ không được để trống';

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

    $maDGEsc = db_escape($conn, $maDG);

    if ($soDT !== '' && is_valid_phone($soDT)) {
        $phoneEsc = db_escape($conn, $soDT);
        $checkPhone = mysqli_query($conn, "SELECT * FROM DocGia WHERE soDT='$phoneEsc' AND maDG<>'$maDGEsc'");
        if (mysqli_num_rows($checkPhone) > 0) {
            $errors['soDT'] = 'Số điện thoại đã tồn tại';
        }
    }

    if ($email !== '' && is_valid_email_format($email)) {
        $emailEsc = db_escape($conn, $email);
        $checkEmail = mysqli_query($conn, "SELECT * FROM DocGia WHERE email='$emailEsc' AND maDG<>'$maDGEsc'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = 'Email đã tồn tại';
        }
    }

    $editData = compact('maDG', 'tenDG', 'ngaySinh', 'gioiTinh', 'diaChi', 'soDT', 'email');

    if (empty($errors)) {
        $maDG = db_escape($conn, $maDG);
        $tenDG = db_escape($conn, $tenDG);
        $ngaySinh = db_escape($conn, $ngaySinh);
        $gioiTinh = db_escape($conn, $gioiTinh);
        $diaChi = db_escape($conn, $diaChi);
        $soDT = db_escape($conn, $soDT);
        $email = db_escape($conn, $email);

        mysqli_query($conn, "
            UPDATE DocGia
            SET tenDG='$tenDG',
                ngaySinh='$ngaySinh',
                gioiTinh='$gioiTinh',
                diaChi='$diaChi',
                soDT='$soDT',
                email='$email'
            WHERE maDG='$maDG'
        ");

        set_flash('success', 'Cập nhật độc giả thành công.');
        redirect(back_url_docgia($search, $page));
    } else {
        $openModal = 'editModal';
    }
}

$searchEsc = db_escape($conn, $search);
$countRs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM DocGia
    WHERE maDG LIKE '%$searchEsc%'
       OR tenDG LIKE '%$searchEsc%'
       OR email LIKE '%$searchEsc%'
");

$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$result = mysqli_query($conn, "
    SELECT * FROM DocGia
    WHERE maDG LIKE '%$searchEsc%'
       OR tenDG LIKE '%$searchEsc%'
       OR email LIKE '%$searchEsc%'
    ORDER BY maDG ASC
    LIMIT $offset, $limit
");

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
                    <h2 class="page-title">Quản lý độc giả</h2>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm độc giả
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm độc giả..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã DG</th>
                            <th>Tên độc giả</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Địa chỉ</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= e($row['maDG']) ?></td>
                            <td><?= e($row['tenDG']) ?></td>
                            <td><?= e($row['ngaySinh']) ?></td>
                            <td><?= e($row['gioiTinh']) ?></td>
                            <td><?= e($row['diaChi']) ?></td>
                            <td><?= e($row['soDT']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td class="action-btns">
                                <a href="?edit=<?= urlencode($row['maDG']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('docgia.php?delete=<?= urlencode($row['maDG']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
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

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm độc giả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên độc giả</label>
                            <input type="text" name="tenDG" class="form-control <?= isset($errors['tenDG']) ? 'is-invalid' : '' ?>" value="<?= e(old('tenDG')) ?>">
                            <?php if(isset($errors['tenDG'])): ?><div class="invalid-feedback"><?= e($errors['tenDG']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="ngaySinh" class="form-control <?= isset($errors['ngaySinh']) ? 'is-invalid' : '' ?>" value="<?= e(old('ngaySinh')) ?>">
                            <?php if(isset($errors['ngaySinh'])): ?><div class="invalid-feedback"><?= e($errors['ngaySinh']) ?></div><?php endif; ?>
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
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="diaChi" class="form-control <?= isset($errors['diaChi']) ? 'is-invalid' : '' ?>" value="<?= e(old('diaChi')) ?>">
                            <?php if(isset($errors['diaChi'])): ?><div class="invalid-feedback"><?= e($errors['diaChi']) ?></div><?php endif; ?>
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

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa độc giả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maDG" value="<?= e($editData['maDG']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên độc giả</label>
                            <input type="text" name="tenDG" class="form-control <?= isset($errors['tenDG']) ? 'is-invalid' : '' ?>" value="<?= e($editData['tenDG']) ?>">
                            <?php if(isset($errors['tenDG'])): ?><div class="invalid-feedback"><?= e($errors['tenDG']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="ngaySinh" class="form-control <?= isset($errors['ngaySinh']) ? 'is-invalid' : '' ?>" value="<?= e($editData['ngaySinh']) ?>">
                            <?php if(isset($errors['ngaySinh'])): ?><div class="invalid-feedback"><?= e($errors['ngaySinh']) ?></div><?php endif; ?>
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
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="diaChi" class="form-control <?= isset($errors['diaChi']) ? 'is-invalid' : '' ?>" value="<?= e($editData['diaChi']) ?>">
                            <?php if(isset($errors['diaChi'])): ?><div class="invalid-feedback"><?= e($errors['diaChi']) ?></div><?php endif; ?>
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