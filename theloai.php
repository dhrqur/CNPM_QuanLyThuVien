<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện', 'Thủ thư']);

$pageTitle = 'Thể loại';
$flash = get_flash();

$message = '';
$errors = [];
$openModal = '';
$editData = null;

$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function generateMaTL($conn) {
    $rs = mysqli_query($conn, "SELECT maTL FROM TheLoai ORDER BY maTL DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maTL'], 2));
        $newId = $num + 1;
    }
    return "TL" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);
    $check = mysqli_query($conn, "SELECT * FROM Sach WHERE maTL='$id'");
    if (mysqli_num_rows($check) > 0) {
        $message = "<div class='alert alert-danger rounded-4'>Không thể xóa thể loại do đang được sử dụng.</div>";
    } else {
        mysqli_query($conn, "DELETE FROM TheLoai WHERE maTL='$id'");
        $message = "<div class='alert alert-success rounded-4'>Xóa thể loại thành công.</div>";
    }
}

if (isset($_POST['add'])) {
    $maTL = generateMaTL($conn);
    $tenTL = trim($_POST['tenTL'] ?? '');

    if ($tenTL === '') $errors['tenTL'] = 'Tên thể loại không được để trống';

    if (empty($errors)) {
        $tenTL = db_escape($conn, $tenTL);
        mysqli_query($conn, "INSERT INTO TheLoai(maTL, tenTL) VALUES('$maTL','$tenTL')");
        $message = "<div class='alert alert-success rounded-4'>Thêm thể loại thành công.</div>";
    } else {
        $openModal = 'addModal';
    }
}

if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM TheLoai WHERE maTL='$id'");
    $editData = mysqli_fetch_assoc($q);
    if ($editData) $openModal = 'editModal';
}

if (isset($_POST['update'])) {
    $maTL = trim($_POST['maTL'] ?? '');
    $tenTL = trim($_POST['tenTL'] ?? '');

    if ($tenTL === '') $errors['tenTL'] = 'Tên thể loại không được để trống';

    $editData = compact('maTL','tenTL');

    if (empty($errors)) {
        $maTL = db_escape($conn, $maTL);
        $tenTL = db_escape($conn, $tenTL);
        mysqli_query($conn, "UPDATE TheLoai SET tenTL='$tenTL' WHERE maTL='$maTL'");
        $message = "<div class='alert alert-success rounded-4'>Cập nhật thể loại thành công.</div>";
        $editData = null;
    } else {
        $openModal = 'editModal';
    }
}

$searchEsc = db_escape($conn, $search);
$countRs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM TheLoai
    WHERE maTL LIKE '%$searchEsc%'
       OR tenTL LIKE '%$searchEsc%'
");
$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

$result = mysqli_query($conn, "
    SELECT * FROM TheLoai
    WHERE maTL LIKE '%$searchEsc%'
       OR tenTL LIKE '%$searchEsc%'
    ORDER BY maTL ASC
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
                    <h2 class="page-title">Quản lý thể loại</h2>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm thể loại
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm thể loại..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã TL</th>
                            <th>Tên thể loại</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= e($row['maTL']) ?></td>
                            <td><?= e($row['tenTL']) ?></td>
                            <td class="action-btns">
                                <a href="?edit=<?= urlencode($row['maTL']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('theloai.php?delete=<?= urlencode($row['maTL']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
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
                <div class="modal-header">
                    <h5 class="modal-title">Thêm thể loại</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Tên thể loại</label>
                    <input type="text" name="tenTL" class="form-control <?= isset($errors['tenTL']) ? 'is-invalid' : '' ?>" value="<?= e(old('tenTL')) ?>">
                    <?php if(isset($errors['tenTL'])): ?><div class="invalid-feedback"><?= e($errors['tenTL']) ?></div><?php endif; ?>
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
                <div class="modal-header">
                    <h5 class="modal-title">Sửa thể loại</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maTL" value="<?= e($editData['maTL']) ?>">
                    <label class="form-label">Tên thể loại</label>
                    <input type="text" name="tenTL" class="form-control <?= isset($errors['tenTL']) ? 'is-invalid' : '' ?>" value="<?= e($editData['tenTL']) ?>">
                    <?php if(isset($errors['tenTL'])): ?><div class="invalid-feedback"><?= e($errors['tenTL']) ?></div><?php endif; ?>
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