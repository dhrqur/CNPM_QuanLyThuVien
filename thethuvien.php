<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện', 'Thủ thư']);

$pageTitle = 'Thẻ thư viện';
$flash = get_flash();

$errors = [];
$openModal = '';
$editData = null;

$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function back_url_ttv($search, $page) {
    return "thethuvien.php?search=" . urlencode($search) . "&p=" . intval($page);
}

function generateMaTTV($conn) {
    $rs = mysqli_query($conn, "SELECT maTTV FROM TheThuVien ORDER BY maTTV DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maTTV'], 3));
        $newId = $num + 1;
    }
    return "TTV" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

function getTrangThaiThe($ngayHetHan) {
    return (strtotime($ngayHetHan) >= strtotime(date('Y-m-d'))) ? 'Còn hạn' : 'Hết hạn';
}

mysqli_query($conn, "UPDATE TheThuVien SET trangThai = CASE WHEN ngayHetHan >= CURDATE() THEN 'Còn hạn' ELSE 'Hết hạn' END");

if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);
    $rowQ = mysqli_query($conn, "SELECT * FROM TheThuVien WHERE maTTV='$id'");
    $row = mysqli_fetch_assoc($rowQ);

    if ($row && $row['trangThai'] === 'Còn hạn') {
        set_flash('danger', 'Không thể xóa thẻ thư viện khi thẻ còn hoạt động.');
    } else {
        mysqli_query($conn, "DELETE FROM TheThuVien WHERE maTTV='$id'");
        set_flash('success', 'Xóa thẻ thư viện thành công.');
    }
    redirect(back_url_ttv($search, $page));
}

if (isset($_POST['add'])) {
    $maTTV = generateMaTTV($conn);
    $maDG = trim($_POST['maDG'] ?? '');
    $ngayCap = trim($_POST['ngayCap'] ?? '');
    $ngayHetHan = trim($_POST['ngayHetHan'] ?? '');

    if ($maDG === '') $errors['maDG'] = 'Vui lòng chọn độc giả';
    if ($ngayCap === '') $errors['ngayCap'] = 'Ngày cấp không được để trống';
    if ($ngayHetHan === '') $errors['ngayHetHan'] = 'Ngày hết hạn không được để trống';

    if ($maDG !== '') {
        $maDGEsc = db_escape($conn, $maDG);
        $checkExist = mysqli_query($conn, "SELECT * FROM TheThuVien WHERE maDG='$maDGEsc'");
        if (mysqli_num_rows($checkExist) > 0) $errors['maDG'] = 'Độc giả này đã có thẻ thư viện';
    }

    if (empty($errors)) {
        $maDGEsc = db_escape($conn, $maDG);
        $ngayCapEsc = db_escape($conn, $ngayCap);
        $ngayHetHanEsc = db_escape($conn, $ngayHetHan);
        $trangThai = db_escape($conn, getTrangThaiThe($ngayHetHan));

        mysqli_query($conn, "
            INSERT INTO TheThuVien(maTTV, maDG, ngayCap, ngayHetHan, trangThai)
            VALUES('$maTTV','$maDGEsc','$ngayCapEsc','$ngayHetHanEsc','$trangThai')
        ");

        set_flash('success', 'Thêm thẻ thư viện thành công.');
        redirect(back_url_ttv($_POST['search'] ?? $search, $_POST['p'] ?? $page));
    } else {
        $openModal = 'addModal';
    }
}

if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM TheThuVien WHERE maTTV='$id'");
    $editData = mysqli_fetch_assoc($q);
    if ($editData) $openModal = 'editModal';
}

if (isset($_POST['update'])) {
    $maTTV = trim($_POST['maTTV'] ?? '');
    $maDG = trim($_POST['maDG'] ?? '');
    $ngayCap = trim($_POST['ngayCap'] ?? '');
    $ngayHetHan = trim($_POST['ngayHetHan'] ?? '');

    if ($maDG === '') $errors['maDG'] = 'Vui lòng chọn độc giả';
    if ($ngayCap === '') $errors['ngayCap'] = 'Ngày cấp không được để trống';
    if ($ngayHetHan === '') $errors['ngayHetHan'] = 'Ngày hết hạn không được để trống';

    if ($maDG !== '' && $maTTV !== '') {
        $maDGEsc = db_escape($conn, $maDG);
        $maTTVEsc = db_escape($conn, $maTTV);
        $checkExist = mysqli_query($conn, "SELECT * FROM TheThuVien WHERE maDG='$maDGEsc' AND maTTV<>'$maTTVEsc'");
        if (mysqli_num_rows($checkExist) > 0) $errors['maDG'] = 'Độc giả này đã có thẻ thư viện';
    }

    $editData = compact('maTTV','maDG','ngayCap','ngayHetHan');

    if (empty($errors)) {
        $maTTVEsc = db_escape($conn, $maTTV);
        $maDGEsc = db_escape($conn, $maDG);
        $ngayCapEsc = db_escape($conn, $ngayCap);
        $ngayHetHanEsc = db_escape($conn, $ngayHetHan);
        $trangThai = db_escape($conn, getTrangThaiThe($ngayHetHan));

        mysqli_query($conn, "
            UPDATE TheThuVien
            SET maDG='$maDGEsc', ngayCap='$ngayCapEsc', ngayHetHan='$ngayHetHanEsc', trangThai='$trangThai'
            WHERE maTTV='$maTTVEsc'
        ");

        set_flash('success', 'Cập nhật thẻ thư viện thành công.');
        redirect(back_url_ttv($_POST['search'] ?? $search, $_POST['p'] ?? $page));
    } else {
        $openModal = 'editModal';
    }
}

$docGiaList = mysqli_query($conn, "SELECT * FROM DocGia ORDER BY tenDG ASC");

$searchEsc = db_escape($conn, $search);
$countRs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM TheThuVien ttv
    LEFT JOIN DocGia dg ON ttv.maDG = dg.maDG
    WHERE ttv.maTTV LIKE '%$searchEsc%'
       OR dg.tenDG LIKE '%$searchEsc%'
       OR ttv.trangThai LIKE '%$searchEsc%'
");
$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

$result = mysqli_query($conn, "
    SELECT ttv.*, dg.tenDG
    FROM TheThuVien ttv
    LEFT JOIN DocGia dg ON ttv.maDG = dg.maDG
    WHERE ttv.maTTV LIKE '%$searchEsc%'
       OR dg.tenDG LIKE '%$searchEsc%'
       OR ttv.trangThai LIKE '%$searchEsc%'
    ORDER BY ttv.maTTV ASC
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
                    <h2 class="page-title">Quản lý thẻ thư viện</h2>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm thẻ thư viện
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm thẻ thư viện..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã thẻ</th>
                            <th>Độc giả</th>
                            <th>Ngày cấp</th>
                            <th>Ngày hết hạn</th>
                            <th>Trạng thái</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= e($row['maTTV']) ?></td>
                            <td><?= e($row['tenDG']) ?></td>
                            <td><?= e($row['ngayCap']) ?></td>
                            <td><?= e($row['ngayHetHan']) ?></td>
                            <td>
                                <?php if ($row['trangThai'] === 'Còn hạn'): ?>
                                    <span class="badge-soft-success">Còn hạn</span>
                                <?php else: ?>
                                    <span class="badge-soft-danger">Hết hạn</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-btns">
                                <a href="?edit=<?= urlencode($row['maTTV']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('thethuvien.php?delete=<?= urlencode($row['maTTV']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
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
                    <h5 class="modal-title">Thêm thẻ thư viện</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Độc giả</label>
                        <select name="maDG" class="form-select <?= isset($errors['maDG']) ? 'is-invalid' : '' ?>">
                            <option value="">-- Chọn độc giả --</option>
                            <?php
                            $docGiaAdd = mysqli_query($conn, "SELECT * FROM DocGia ORDER BY tenDG ASC");
                            while($dg = mysqli_fetch_assoc($docGiaAdd)):
                            ?>
                            <option value="<?= e($dg['maDG']) ?>" <?= old('maDG') === $dg['maDG'] ? 'selected' : '' ?>><?= e($dg['tenDG']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <?php if(isset($errors['maDG'])): ?><div class="invalid-feedback"><?= e($errors['maDG']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày cấp</label>
                        <input type="date" name="ngayCap" class="form-control <?= isset($errors['ngayCap']) ? 'is-invalid' : '' ?>" value="<?= e(old('ngayCap')) ?>">
                        <?php if(isset($errors['ngayCap'])): ?><div class="invalid-feedback"><?= e($errors['ngayCap']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày hết hạn</label>
                        <input type="date" name="ngayHetHan" class="form-control <?= isset($errors['ngayHetHan']) ? 'is-invalid' : '' ?>" value="<?= e(old('ngayHetHan')) ?>">
                        <?php if(isset($errors['ngayHetHan'])): ?><div class="invalid-feedback"><?= e($errors['ngayHetHan']) ?></div><?php endif; ?>
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
                    <h5 class="modal-title">Sửa thẻ thư viện</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maTTV" value="<?= e($editData['maTTV']) ?>">
                    <div class="mb-3">
                        <label class="form-label">Độc giả</label>
                        <select name="maDG" class="form-select <?= isset($errors['maDG']) ? 'is-invalid' : '' ?>">
                            <option value="">-- Chọn độc giả --</option>
                            <?php
                            $docGiaEdit = mysqli_query($conn, "SELECT * FROM DocGia ORDER BY tenDG ASC");
                            while($dg = mysqli_fetch_assoc($docGiaEdit)):
                            ?>
                            <option value="<?= e($dg['maDG']) ?>" <?= $editData['maDG'] === $dg['maDG'] ? 'selected' : '' ?>><?= e($dg['tenDG']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <?php if(isset($errors['maDG'])): ?><div class="invalid-feedback"><?= e($errors['maDG']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày cấp</label>
                        <input type="date" name="ngayCap" class="form-control <?= isset($errors['ngayCap']) ? 'is-invalid' : '' ?>" value="<?= e($editData['ngayCap']) ?>">
                        <?php if(isset($errors['ngayCap'])): ?><div class="invalid-feedback"><?= e($errors['ngayCap']) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày hết hạn</label>
                        <input type="date" name="ngayHetHan" class="form-control <?= isset($errors['ngayHetHan']) ? 'is-invalid' : '' ?>" value="<?= e($editData['ngayHetHan']) ?>">
                        <?php if(isset($errors['ngayHetHan'])): ?><div class="invalid-feedback"><?= e($errors['ngayHetHan']) ?></div><?php endif; ?>
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