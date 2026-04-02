<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện', 'Thủ thư']);

$pageTitle = 'Tác giả';
$flash = get_flash();

$message = '';
$errors = [];
$openModal = '';
$editData = null;

$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function generateMaTG($conn) {
    $rs = mysqli_query($conn, "SELECT maTG FROM TacGia ORDER BY maTG DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maTG'], 2));
        $newId = $num + 1;
    }
    return "TG" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);
    $check = mysqli_query($conn, "SELECT * FROM Sach WHERE maTG='$id'");
    if (mysqli_num_rows($check) > 0) {
        $message = "<div class='alert alert-danger rounded-4'>Không thể xóa tác giả do đang được sử dụng.</div>";
    } else {
        mysqli_query($conn, "DELETE FROM TacGia WHERE maTG='$id'");
        $message = "<div class='alert alert-success rounded-4'>Xóa tác giả thành công.</div>";
    }
}

if (isset($_POST['add'])) {
    $maTG = generateMaTG($conn);
    $tenTG = trim($_POST['tenTG'] ?? '');
    $namSinh = trim($_POST['namSinh'] ?? '');
    $gioiTinh = trim($_POST['gioiTinh'] ?? '');
    $quocTich = trim($_POST['quocTich'] ?? '');
    $moTa = trim($_POST['moTa'] ?? '');

    if ($tenTG === '') $errors['tenTG'] = 'Tên tác giả không được để trống';
    if ($quocTich === '') $errors['quocTich'] = 'Quốc tịch không được để trống';

    if (empty($errors)) {
        $tenTG = db_escape($conn, $tenTG);
        $namSinh = db_escape($conn, $namSinh);
        $gioiTinh = db_escape($conn, $gioiTinh);
        $quocTich = db_escape($conn, $quocTich);
        $moTa = db_escape($conn, $moTa);

        mysqli_query($conn, "
            INSERT INTO TacGia(maTG, tenTG, namSinh, gioiTinh, quocTich, moTa)
            VALUES('$maTG','$tenTG','$namSinh','$gioiTinh','$quocTich','$moTa')
        ");
        $message = "<div class='alert alert-success rounded-4'>Thêm tác giả thành công.</div>";
    } else {
        $openModal = 'addModal';
    }
}

if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM TacGia WHERE maTG='$id'");
    $editData = mysqli_fetch_assoc($q);
    if ($editData) $openModal = 'editModal';
}

if (isset($_POST['update'])) {
    $maTG = trim($_POST['maTG'] ?? '');
    $tenTG = trim($_POST['tenTG'] ?? '');
    $namSinh = trim($_POST['namSinh'] ?? '');
    $gioiTinh = trim($_POST['gioiTinh'] ?? '');
    $quocTich = trim($_POST['quocTich'] ?? '');
    $moTa = trim($_POST['moTa'] ?? '');

    if ($tenTG === '') $errors['tenTG'] = 'Tên tác giả không được để trống';
    if ($quocTich === '') $errors['quocTich'] = 'Quốc tịch không được để trống';

    $editData = compact('maTG','tenTG','namSinh','gioiTinh','quocTich','moTa');

    if (empty($errors)) {
        $maTG = db_escape($conn, $maTG);
        $tenTG = db_escape($conn, $tenTG);
        $namSinh = db_escape($conn, $namSinh);
        $gioiTinh = db_escape($conn, $gioiTinh);
        $quocTich = db_escape($conn, $quocTich);
        $moTa = db_escape($conn, $moTa);

        mysqli_query($conn, "
            UPDATE TacGia
            SET tenTG='$tenTG', namSinh='$namSinh', gioiTinh='$gioiTinh', quocTich='$quocTich', moTa='$moTa'
            WHERE maTG='$maTG'
        ");
        $message = "<div class='alert alert-success rounded-4'>Cập nhật tác giả thành công.</div>";
        $editData = null;
    } else {
        $openModal = 'editModal';
    }
}

$searchEsc = db_escape($conn, $search);
$countRs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM TacGia
    WHERE maTG LIKE '%$searchEsc%'
       OR tenTG LIKE '%$searchEsc%'
       OR quocTich LIKE '%$searchEsc%'
");
$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

$result = mysqli_query($conn, "
    SELECT * FROM TacGia
    WHERE maTG LIKE '%$searchEsc%'
       OR tenTG LIKE '%$searchEsc%'
       OR quocTich LIKE '%$searchEsc%'
    ORDER BY maTG ASC
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

        <?= $message ?>

        <div class="content-toolbar">
            <div class="page-header">
                <div>
                    <h2 class="page-title">Quản lý tác giả</h2>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm tác giả
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tác giả..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã TG</th>
                            <th>Tên tác giả</th>
                            <th>Ngày sinh</th>
                            <th>Giới tính</th>
                            <th>Quốc tịch</th>
                            <th>Mô tả</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= e($row['maTG']) ?></td>
                            <td><?= e($row['tenTG']) ?></td>
                            <td><?= e($row['namSinh']) ?></td>
                            <td><?= e($row['gioiTinh']) ?></td>
                            <td><?= e($row['quocTich']) ?></td>
                            <td><?= e($row['moTa']) ?></td>
                            <td class="action-btns">
                                <a href="?edit=<?= urlencode($row['maTG']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('tacgia.php?delete=<?= urlencode($row['maTG']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
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
                <div class="modal-header">
                    <h5 class="modal-title">Thêm tác giả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên tác giả</label>
                            <input type="text" name="tenTG" class="form-control <?= isset($errors['tenTG']) ? 'is-invalid' : '' ?>" value="<?= e(old('tenTG')) ?>">
                            <?php if(isset($errors['tenTG'])): ?><div class="invalid-feedback"><?= e($errors['tenTG']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="namSinh" class="form-control" value="<?= e(old('namSinh')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gioiTinh" class="form-select">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?= old('gioiTinh') === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= old('gioiTinh') === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quốc tịch</label>
                            <input type="text" name="quocTich" class="form-control <?= isset($errors['quocTich']) ? 'is-invalid' : '' ?>" value="<?= e(old('quocTich')) ?>">
                            <?php if(isset($errors['quocTich'])): ?><div class="invalid-feedback"><?= e($errors['quocTich']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="moTa" class="form-control" rows="3"><?= e(old('moTa')) ?></textarea>
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
                <div class="modal-header">
                    <h5 class="modal-title">Sửa tác giả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maTG" value="<?= e($editData['maTG']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên tác giả</label>
                            <input type="text" name="tenTG" class="form-control <?= isset($errors['tenTG']) ? 'is-invalid' : '' ?>" value="<?= e($editData['tenTG']) ?>">
                            <?php if(isset($errors['tenTG'])): ?><div class="invalid-feedback"><?= e($errors['tenTG']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="namSinh" class="form-control" value="<?= e($editData['namSinh']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giới tính</label>
                            <select name="gioiTinh" class="form-select">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?= $editData['gioiTinh'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= $editData['gioiTinh'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quốc tịch</label>
                            <input type="text" name="quocTich" class="form-control <?= isset($errors['quocTich']) ? 'is-invalid' : '' ?>" value="<?= e($editData['quocTich']) ?>">
                            <?php if(isset($errors['quocTich'])): ?><div class="invalid-feedback"><?= e($errors['quocTich']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="moTa" class="form-control" rows="3"><?= e($editData['moTa']) ?></textarea>
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