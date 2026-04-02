<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện', 'Thủ thư']);

$pageTitle = 'Nhà xuất bản';
$flash = get_flash();

$errors = [];
$openModal = '';
$editData = null;

$search = trim($_GET['search'] ?? $_POST['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? $_POST['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function generateMaNXB($conn) {
    $rs = mysqli_query($conn, "SELECT maNXB FROM NhaXuatBan ORDER BY maNXB DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maNXB'], 3));
        $newId = $num + 1;
    }
    return "NXB" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

function back_url($search, $page) {
    return "nhaxuatban.php?search=" . urlencode($search) . "&p=" . intval($page);
}

function is_valid_phone($phone) {
    return preg_match('/^\d{10,12}$/', $phone);
}

function is_valid_email_format($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);
    $check = mysqli_query($conn, "SELECT * FROM Sach WHERE maNXB='$id'");

    if (mysqli_num_rows($check) > 0) {
        set_flash('danger', 'Không thể xóa nhà xuất bản do đang được sử dụng.');
    } else {
        mysqli_query($conn, "DELETE FROM NhaXuatBan WHERE maNXB='$id'");
        set_flash('success', 'Xóa nhà xuất bản thành công.');
    }

    redirect(back_url($search, $page));
}

if (isset($_POST['add'])) {
    $maNXB = generateMaNXB($conn);
    $tenNXB = trim($_POST['tenNXB'] ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $soDT = trim($_POST['soDT'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($tenNXB === '') $errors['tenNXB'] = 'Tên nhà xuất bản không được để trống';
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
        $checkPhone = mysqli_query($conn, "SELECT * FROM NhaXuatBan WHERE soDT='$phoneEsc'");
        if (mysqli_num_rows($checkPhone) > 0) {
            $errors['soDT'] = 'Số điện thoại đã tồn tại';
        }
    }

    if ($email !== '' && is_valid_email_format($email)) {
        $emailEsc = db_escape($conn, $email);
        $checkEmail = mysqli_query($conn, "SELECT * FROM NhaXuatBan WHERE email='$emailEsc'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = 'Email đã tồn tại';
        }
    }

    if (empty($errors)) {
        $tenNXB = db_escape($conn, $tenNXB);
        $diaChi = db_escape($conn, $diaChi);
        $soDT = db_escape($conn, $soDT);
        $email = db_escape($conn, $email);

        mysqli_query($conn, "
            INSERT INTO NhaXuatBan(maNXB, tenNXB, diaChi, soDT, email)
            VALUES('$maNXB','$tenNXB','$diaChi','$soDT','$email')
        ");

        set_flash('success', 'Thêm nhà xuất bản thành công.');
        redirect(back_url($search, $page));
    } else {
        $openModal = 'addModal';
    }
}

if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM NhaXuatBan WHERE maNXB='$id'");
    $editData = mysqli_fetch_assoc($q);
    if ($editData) $openModal = 'editModal';
}

if (isset($_POST['update'])) {
    $maNXB = trim($_POST['maNXB'] ?? '');
    $tenNXB = trim($_POST['tenNXB'] ?? '');
    $diaChi = trim($_POST['diaChi'] ?? '');
    $soDT = trim($_POST['soDT'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($tenNXB === '') $errors['tenNXB'] = 'Tên nhà xuất bản không được để trống';
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

    $maNXBEsc = db_escape($conn, $maNXB);

    if ($soDT !== '' && is_valid_phone($soDT)) {
        $phoneEsc = db_escape($conn, $soDT);
        $checkPhone = mysqli_query($conn, "SELECT * FROM NhaXuatBan WHERE soDT='$phoneEsc' AND maNXB<>'$maNXBEsc'");
        if (mysqli_num_rows($checkPhone) > 0) {
            $errors['soDT'] = 'Số điện thoại đã tồn tại';
        }
    }

    if ($email !== '' && is_valid_email_format($email)) {
        $emailEsc = db_escape($conn, $email);
        $checkEmail = mysqli_query($conn, "SELECT * FROM NhaXuatBan WHERE email='$emailEsc' AND maNXB<>'$maNXBEsc'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors['email'] = 'Email đã tồn tại';
        }
    }

    $editData = compact('maNXB', 'tenNXB', 'diaChi', 'soDT', 'email');

    if (empty($errors)) {
        $maNXB = db_escape($conn, $maNXB);
        $tenNXB = db_escape($conn, $tenNXB);
        $diaChi = db_escape($conn, $diaChi);
        $soDT = db_escape($conn, $soDT);
        $email = db_escape($conn, $email);

        mysqli_query($conn, "
            UPDATE NhaXuatBan
            SET tenNXB='$tenNXB', diaChi='$diaChi', soDT='$soDT', email='$email'
            WHERE maNXB='$maNXB'
        ");

        set_flash('success', 'Cập nhật nhà xuất bản thành công.');
        redirect(back_url($search, $page));
    } else {
        $openModal = 'editModal';
    }
}

$searchEsc = db_escape($conn, $search);

$countRs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM NhaXuatBan
    WHERE maNXB LIKE '%$searchEsc%'
       OR tenNXB LIKE '%$searchEsc%'
       OR email LIKE '%$searchEsc%'
");

$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$result = mysqli_query($conn, "
    SELECT * FROM NhaXuatBan
    WHERE maNXB LIKE '%$searchEsc%'
       OR tenNXB LIKE '%$searchEsc%'
       OR email LIKE '%$searchEsc%'
    ORDER BY maNXB ASC
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
                    <h2 class="page-title">Quản lý nhà xuất bản</h2>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm nhà xuất bản
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm nhà xuất bản..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã NXB</th>
                            <th>Tên NXB</th>
                            <th>Địa chỉ</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= e($row['maNXB']) ?></td>
                            <td><?= e($row['tenNXB']) ?></td>
                            <td><?= e($row['diaChi']) ?></td>
                            <td><?= e($row['soDT']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td class="action-btns">
                                <a href="?edit=<?= urlencode($row['maNXB']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('nhaxuatban.php?delete=<?= urlencode($row['maNXB']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm nhà xuất bản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà xuất bản</label>
                        <input type="text" name="tenNXB" class="form-control <?= isset($errors['tenNXB']) ? 'is-invalid' : '' ?>" value="<?= e(old('tenNXB')) ?>">
                        <?php if(isset($errors['tenNXB'])): ?><div class="invalid-feedback"><?= e($errors['tenNXB']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="diaChi" class="form-control <?= isset($errors['diaChi']) ? 'is-invalid' : '' ?>" value="<?= e(old('diaChi')) ?>">
                        <?php if(isset($errors['diaChi'])): ?><div class="invalid-feedback"><?= e($errors['diaChi']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="soDT" class="form-control <?= isset($errors['soDT']) ? 'is-invalid' : '' ?>" value="<?= e(old('soDT')) ?>">
                        <?php if(isset($errors['soDT'])): ?><div class="invalid-feedback"><?= e($errors['soDT']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= e(old('email')) ?>">
                        <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa nhà xuất bản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maNXB" value="<?= e($editData['maNXB']) ?>">
                    <div class="mb-3">
                        <label class="form-label">Tên nhà xuất bản</label>
                        <input type="text" name="tenNXB" class="form-control <?= isset($errors['tenNXB']) ? 'is-invalid' : '' ?>" value="<?= e($editData['tenNXB']) ?>">
                        <?php if(isset($errors['tenNXB'])): ?><div class="invalid-feedback"><?= e($errors['tenNXB']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="diaChi" class="form-control <?= isset($errors['diaChi']) ? 'is-invalid' : '' ?>" value="<?= e($editData['diaChi']) ?>">
                        <?php if(isset($errors['diaChi'])): ?><div class="invalid-feedback"><?= e($errors['diaChi']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số điện thoại</label>
                        <input type="text" name="soDT" class="form-control <?= isset($errors['soDT']) ? 'is-invalid' : '' ?>" value="<?= e($editData['soDT']) ?>">
                        <?php if(isset($errors['soDT'])): ?><div class="invalid-feedback"><?= e($errors['soDT']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= e($editData['email']) ?>">
                        <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
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