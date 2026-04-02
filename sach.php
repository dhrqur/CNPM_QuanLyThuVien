<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện', 'Thủ thư']);

$pageTitle = 'Sách';
$flash = get_flash();

$errors = [];
$openModal = '';
$editData = null;
$detailData = null;

$search = trim($_GET['search'] ?? $_POST['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? $_POST['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function back_url_sach($search, $page) {
    return "sach.php?search=" . urlencode($search) . "&p=" . intval($page);
}

function generateMaSach($conn) {
    $rs = mysqli_query($conn, "SELECT maSach FROM Sach ORDER BY maSach DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maSach'], 1));
        $newId = $num + 1;
    }
    return "S" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

function getTrangThaiSach($soLuong) {
    return ((int)$soLuong > 0) ? 'Còn' : 'Hết';
}

if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);
    $check = mysqli_query($conn, "SELECT * FROM ChiTietMuonTra WHERE maSach='$id'");
    if (mysqli_num_rows($check) > 0) {
        set_flash('danger', 'Không thể xóa sách do đang được sử dụng.');
    } else {
        mysqli_query($conn, "DELETE FROM Sach WHERE maSach='$id'");
        set_flash('success', 'Xóa sách thành công.');
    }
    redirect(back_url_sach($search, $page));
}

if (isset($_POST['add'])) {
    $maSach = generateMaSach($conn);
    $tenSach = trim($_POST['tenSach'] ?? '');
    $maTG = trim($_POST['maTG'] ?? '');
    $maNXB = trim($_POST['maNXB'] ?? '');
    $maTL = trim($_POST['maTL'] ?? '');
    $maNN = trim($_POST['maNN'] ?? '');
    $maKS = trim($_POST['maKS'] ?? '');
    $namXB = trim($_POST['namXB'] ?? '');
    $soLuong = trim($_POST['soLuong'] ?? '');
    $moTa = trim($_POST['moTa'] ?? '');

    if ($tenSach === '') $errors['tenSach'] = 'Tên sách không được để trống';
    if ($maTG === '') $errors['maTG'] = 'Vui lòng chọn tác giả';
    if ($maNXB === '') $errors['maNXB'] = 'Vui lòng chọn nhà xuất bản';
    if ($maTL === '') $errors['maTL'] = 'Vui lòng chọn thể loại';
    if ($maNN === '') $errors['maNN'] = 'Vui lòng chọn ngôn ngữ';
    if ($maKS === '') $errors['maKS'] = 'Vui lòng chọn kệ sách';
    if ($namXB === '') $errors['namXB'] = 'Năm xuất bản không được để trống';
    elseif (!is_numeric($namXB) || (int)$namXB > (int)date('Y')) $errors['namXB'] = 'Năm xuất bản không hợp lệ';
    if ($soLuong === '') $errors['soLuong'] = 'Số lượng không được để trống';
    elseif (!is_numeric($soLuong) || (int)$soLuong < 0) $errors['soLuong'] = 'Số lượng phải là số không âm';

    if (empty($errors)) {
        $tenSach = db_escape($conn, $tenSach);
        $maTG = db_escape($conn, $maTG);
        $maNXB = db_escape($conn, $maNXB);
        $maTL = db_escape($conn, $maTL);
        $maNN = db_escape($conn, $maNN);
        $maKS = db_escape($conn, $maKS);
        $namXB = db_escape($conn, $namXB);
        $soLuong = db_escape($conn, $soLuong);
        $moTa = db_escape($conn, $moTa);
        $trangThai = db_escape($conn, getTrangThaiSach($soLuong));

        mysqli_query($conn, "
            INSERT INTO Sach(maSach, maNXB, maTL, maNN, maTG, maKS, tenSach, namXB, soLuong, moTa, trangThai)
            VALUES('$maSach','$maNXB','$maTL','$maNN','$maTG','$maKS','$tenSach','$namXB','$soLuong','$moTa','$trangThai')
        ");

        set_flash('success', 'Thêm sách thành công.');
        redirect(back_url_sach($_POST['search'] ?? $search, $_POST['p'] ?? $page));
    } else {
        $openModal = 'addModal';
    }
}

if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM Sach WHERE maSach='$id'");
    $editData = mysqli_fetch_assoc($q);
    if ($editData) $openModal = 'editModal';
}

if (isset($_GET['view'])) {
    $id = db_escape($conn, $_GET['view']);
    $q = mysqli_query($conn, "
        SELECT s.*, tg.tenTG, nxb.tenNXB, tl.tenTL, nn.tenNN, ks.tenKS
        FROM Sach s
        LEFT JOIN TacGia tg ON s.maTG = tg.maTG
        LEFT JOIN NhaXuatBan nxb ON s.maNXB = nxb.maNXB
        LEFT JOIN TheLoai tl ON s.maTL = tl.maTL
        LEFT JOIN NgonNgu nn ON s.maNN = nn.maNN
        LEFT JOIN KeSach ks ON s.maKS = ks.maKS
        WHERE s.maSach='$id'
    ");
    $detailData = mysqli_fetch_assoc($q);
    if ($detailData) $openModal = 'detailModal';
}

if (isset($_POST['update'])) {
    $maSach = trim($_POST['maSach'] ?? '');
    $tenSach = trim($_POST['tenSach'] ?? '');
    $maTG = trim($_POST['maTG'] ?? '');
    $maNXB = trim($_POST['maNXB'] ?? '');
    $maTL = trim($_POST['maTL'] ?? '');
    $maNN = trim($_POST['maNN'] ?? '');
    $maKS = trim($_POST['maKS'] ?? '');
    $namXB = trim($_POST['namXB'] ?? '');
    $soLuong = trim($_POST['soLuong'] ?? '');
    $moTa = trim($_POST['moTa'] ?? '');

    if ($tenSach === '') $errors['tenSach'] = 'Tên sách không được để trống';
    if ($maTG === '') $errors['maTG'] = 'Vui lòng chọn tác giả';
    if ($maNXB === '') $errors['maNXB'] = 'Vui lòng chọn nhà xuất bản';
    if ($maTL === '') $errors['maTL'] = 'Vui lòng chọn thể loại';
    if ($maNN === '') $errors['maNN'] = 'Vui lòng chọn ngôn ngữ';
    if ($maKS === '') $errors['maKS'] = 'Vui lòng chọn kệ sách';
    if ($namXB === '') $errors['namXB'] = 'Năm xuất bản không được để trống';
    elseif (!is_numeric($namXB) || (int)$namXB > (int)date('Y')) $errors['namXB'] = 'Năm xuất bản không hợp lệ';
    if ($soLuong === '') $errors['soLuong'] = 'Số lượng không được để trống';
    elseif (!is_numeric($soLuong) || (int)$soLuong < 0) $errors['soLuong'] = 'Số lượng phải là số không âm';

    $editData = compact('maSach','tenSach','maTG','maNXB','maTL','maNN','maKS','namXB','soLuong','moTa');

    if (empty($errors)) {
        $maSach = db_escape($conn, $maSach);
        $tenSach = db_escape($conn, $tenSach);
        $maTG = db_escape($conn, $maTG);
        $maNXB = db_escape($conn, $maNXB);
        $maTL = db_escape($conn, $maTL);
        $maNN = db_escape($conn, $maNN);
        $maKS = db_escape($conn, $maKS);
        $namXB = db_escape($conn, $namXB);
        $soLuong = db_escape($conn, $soLuong);
        $moTa = db_escape($conn, $moTa);
        $trangThai = db_escape($conn, getTrangThaiSach($soLuong));

        mysqli_query($conn, "
            UPDATE Sach
            SET tenSach='$tenSach', maTG='$maTG', maNXB='$maNXB', maTL='$maTL', maNN='$maNN', maKS='$maKS',
                namXB='$namXB', soLuong='$soLuong', moTa='$moTa', trangThai='$trangThai'
            WHERE maSach='$maSach'
        ");

        set_flash('success', 'Cập nhật sách thành công.');
        redirect(back_url_sach($_POST['search'] ?? $search, $_POST['p'] ?? $page));
    } else {
        $openModal = 'editModal';
    }
}

$searchEsc = db_escape($conn, $search);
$countRs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM Sach s
    LEFT JOIN TacGia tg ON s.maTG = tg.maTG
    LEFT JOIN NhaXuatBan nxb ON s.maNXB = nxb.maNXB
    LEFT JOIN TheLoai tl ON s.maTL = tl.maTL
    LEFT JOIN NgonNgu nn ON s.maNN = nn.maNN
    LEFT JOIN KeSach ks ON s.maKS = ks.maKS
    WHERE s.maSach LIKE '%$searchEsc%'
       OR s.tenSach LIKE '%$searchEsc%'
       OR tg.tenTG LIKE '%$searchEsc%'
       OR nxb.tenNXB LIKE '%$searchEsc%'
       OR tl.tenTL LIKE '%$searchEsc%'
       OR nn.tenNN LIKE '%$searchEsc%'
       OR ks.tenKS LIKE '%$searchEsc%'
");
$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$result = mysqli_query($conn, "
    SELECT s.*, tg.tenTG, nxb.tenNXB, tl.tenTL, nn.tenNN, ks.tenKS
    FROM Sach s
    LEFT JOIN TacGia tg ON s.maTG = tg.maTG
    LEFT JOIN NhaXuatBan nxb ON s.maNXB = nxb.maNXB
    LEFT JOIN TheLoai tl ON s.maTL = tl.maTL
    LEFT JOIN NgonNgu nn ON s.maNN = nn.maNN
    LEFT JOIN KeSach ks ON s.maKS = ks.maKS
    WHERE s.maSach LIKE '%$searchEsc%'
       OR s.tenSach LIKE '%$searchEsc%'
       OR tg.tenTG LIKE '%$searchEsc%'
       OR nxb.tenNXB LIKE '%$searchEsc%'
       OR tl.tenTL LIKE '%$searchEsc%'
       OR nn.tenNN LIKE '%$searchEsc%'
       OR ks.tenKS LIKE '%$searchEsc%'
    ORDER BY s.maSach ASC
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
                    <h2 class="page-title">Quản lý sách</h2>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm sách
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm sách..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Tên sách</th>
                            <th>Tác giả</th>
                            <th>NXB</th>
                            <th>Thể loại</th>
                            <th>Năm XB</th>
                            <th>SL</th>
                            <th>Ngôn ngữ</th>
                            <th>Kệ</th>
                            <th>Trạng thái</th>
                            <th width="240">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="11" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= e($row['maSach']) ?></td>
                            <td><?= e($row['tenSach']) ?></td>
                            <td><?= e($row['tenTG']) ?></td>
                            <td><?= e($row['tenNXB']) ?></td>
                            <td><?= e($row['tenTL']) ?></td>
                            <td><?= e($row['namXB']) ?></td>
                            <td><?= e($row['soLuong']) ?></td>
                            <td><?= e($row['tenNN']) ?></td>
                            <td><?= e($row['tenKS']) ?></td>
                            <td>
                                <?php if ($row['trangThai'] === 'Còn'): ?>
                                    <span class="badge-soft-success">Còn</span>
                                <?php else: ?>
                                    <span class="badge-soft-danger">Hết</span>
                                <?php endif; ?>
                            </td>
                            <td class="action-btns">
                                <a href="?view=<?= urlencode($row['maSach']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                <a href="?edit=<?= urlencode($row['maSach']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('sach.php?delete=<?= urlencode($row['maSach']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sách</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên sách</label>
                            <input type="text" name="tenSach" class="form-control <?= isset($errors['tenSach']) ? 'is-invalid' : '' ?>" value="<?= e(old('tenSach')) ?>">
                            <?php if(isset($errors['tenSach'])): ?><div class="invalid-feedback"><?= e($errors['tenSach']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tác giả</label>
                            <select name="maTG" class="form-select <?= isset($errors['maTG']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn tác giả --</option>
                                <?php $tgRs = mysqli_query($conn, "SELECT * FROM TacGia ORDER BY tenTG ASC"); while($tg = mysqli_fetch_assoc($tgRs)): ?>
                                <option value="<?= e($tg['maTG']) ?>" <?= old('maTG') === $tg['maTG'] ? 'selected' : '' ?>><?= e($tg['tenTG']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maTG'])): ?><div class="invalid-feedback"><?= e($errors['maTG']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nhà xuất bản</label>
                            <select name="maNXB" class="form-select <?= isset($errors['maNXB']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn NXB --</option>
                                <?php $nxbRs = mysqli_query($conn, "SELECT * FROM NhaXuatBan ORDER BY tenNXB ASC"); while($nxb = mysqli_fetch_assoc($nxbRs)): ?>
                                <option value="<?= e($nxb['maNXB']) ?>" <?= old('maNXB') === $nxb['maNXB'] ? 'selected' : '' ?>><?= e($nxb['tenNXB']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maNXB'])): ?><div class="invalid-feedback"><?= e($errors['maNXB']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Thể loại</label>
                            <select name="maTL" class="form-select <?= isset($errors['maTL']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn thể loại --</option>
                                <?php $tlRs = mysqli_query($conn, "SELECT * FROM TheLoai ORDER BY tenTL ASC"); while($tl = mysqli_fetch_assoc($tlRs)): ?>
                                <option value="<?= e($tl['maTL']) ?>" <?= old('maTL') === $tl['maTL'] ? 'selected' : '' ?>><?= e($tl['tenTL']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maTL'])): ?><div class="invalid-feedback"><?= e($errors['maTL']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ngôn ngữ</label>
                            <select name="maNN" class="form-select <?= isset($errors['maNN']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn ngôn ngữ --</option>
                                <?php $nnRs = mysqli_query($conn, "SELECT * FROM NgonNgu ORDER BY tenNN ASC"); while($nn = mysqli_fetch_assoc($nnRs)): ?>
                                <option value="<?= e($nn['maNN']) ?>" <?= old('maNN') === $nn['maNN'] ? 'selected' : '' ?>><?= e($nn['tenNN']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maNN'])): ?><div class="invalid-feedback"><?= e($errors['maNN']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kệ sách</label>
                            <select name="maKS" class="form-select <?= isset($errors['maKS']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn kệ sách --</option>
                                <?php $ksRs = mysqli_query($conn, "SELECT * FROM KeSach ORDER BY tenKS ASC"); while($ks = mysqli_fetch_assoc($ksRs)): ?>
                                <option value="<?= e($ks['maKS']) ?>" <?= old('maKS') === $ks['maKS'] ? 'selected' : '' ?>><?= e($ks['tenKS']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maKS'])): ?><div class="invalid-feedback"><?= e($errors['maKS']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Năm xuất bản</label>
                            <input type="number" name="namXB" class="form-control <?= isset($errors['namXB']) ? 'is-invalid' : '' ?>" value="<?= e(old('namXB')) ?>">
                            <?php if(isset($errors['namXB'])): ?><div class="invalid-feedback"><?= e($errors['namXB']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Số lượng</label>
                            <input type="number" name="soLuong" class="form-control <?= isset($errors['soLuong']) ? 'is-invalid' : '' ?>" value="<?= e(old('soLuong')) ?>">
                            <?php if(isset($errors['soLuong'])): ?><div class="invalid-feedback"><?= e($errors['soLuong']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12">
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa sách</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maSach" value="<?= e($editData['maSach']) ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên sách</label>
                            <input type="text" name="tenSach" class="form-control <?= isset($errors['tenSach']) ? 'is-invalid' : '' ?>" value="<?= e($editData['tenSach']) ?>">
                            <?php if(isset($errors['tenSach'])): ?><div class="invalid-feedback"><?= e($errors['tenSach']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tác giả</label>
                            <select name="maTG" class="form-select <?= isset($errors['maTG']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn tác giả --</option>
                                <?php $tgRs = mysqli_query($conn, "SELECT * FROM TacGia ORDER BY tenTG ASC"); while($tg = mysqli_fetch_assoc($tgRs)): ?>
                                <option value="<?= e($tg['maTG']) ?>" <?= $editData['maTG'] === $tg['maTG'] ? 'selected' : '' ?>><?= e($tg['tenTG']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maTG'])): ?><div class="invalid-feedback"><?= e($errors['maTG']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nhà xuất bản</label>
                            <select name="maNXB" class="form-select <?= isset($errors['maNXB']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn NXB --</option>
                                <?php $nxbRs = mysqli_query($conn, "SELECT * FROM NhaXuatBan ORDER BY tenNXB ASC"); while($nxb = mysqli_fetch_assoc($nxbRs)): ?>
                                <option value="<?= e($nxb['maNXB']) ?>" <?= $editData['maNXB'] === $nxb['maNXB'] ? 'selected' : '' ?>><?= e($nxb['tenNXB']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maNXB'])): ?><div class="invalid-feedback"><?= e($errors['maNXB']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Thể loại</label>
                            <select name="maTL" class="form-select <?= isset($errors['maTL']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn thể loại --</option>
                                <?php $tlRs = mysqli_query($conn, "SELECT * FROM TheLoai ORDER BY tenTL ASC"); while($tl = mysqli_fetch_assoc($tlRs)): ?>
                                <option value="<?= e($tl['maTL']) ?>" <?= $editData['maTL'] === $tl['maTL'] ? 'selected' : '' ?>><?= e($tl['tenTL']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maTL'])): ?><div class="invalid-feedback"><?= e($errors['maTL']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Ngôn ngữ</label>
                            <select name="maNN" class="form-select <?= isset($errors['maNN']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn ngôn ngữ --</option>
                                <?php $nnRs = mysqli_query($conn, "SELECT * FROM NgonNgu ORDER BY tenNN ASC"); while($nn = mysqli_fetch_assoc($nnRs)): ?>
                                <option value="<?= e($nn['maNN']) ?>" <?= $editData['maNN'] === $nn['maNN'] ? 'selected' : '' ?>><?= e($nn['tenNN']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maNN'])): ?><div class="invalid-feedback"><?= e($errors['maNN']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kệ sách</label>
                            <select name="maKS" class="form-select <?= isset($errors['maKS']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn kệ sách --</option>
                                <?php $ksRs = mysqli_query($conn, "SELECT * FROM KeSach ORDER BY tenKS ASC"); while($ks = mysqli_fetch_assoc($ksRs)): ?>
                                <option value="<?= e($ks['maKS']) ?>" <?= $editData['maKS'] === $ks['maKS'] ? 'selected' : '' ?>><?= e($ks['tenKS']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maKS'])): ?><div class="invalid-feedback"><?= e($errors['maKS']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Năm xuất bản</label>
                            <input type="number" name="namXB" class="form-control <?= isset($errors['namXB']) ? 'is-invalid' : '' ?>" value="<?= e($editData['namXB']) ?>">
                            <?php if(isset($errors['namXB'])): ?><div class="invalid-feedback"><?= e($errors['namXB']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Số lượng</label>
                            <input type="number" name="soLuong" class="form-control <?= isset($errors['soLuong']) ? 'is-invalid' : '' ?>" value="<?= e($editData['soLuong']) ?>">
                            <?php if(isset($errors['soLuong'])): ?><div class="invalid-feedback"><?= e($errors['soLuong']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-12">
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

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết sách</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if ($detailData): ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mã sách</label>
                            <div class="form-control bg-light"><?= e($detailData['maSach']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tên sách</label>
                            <div class="form-control bg-light"><?= e($detailData['tenSach']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tác giả</label>
                            <div class="form-control bg-light"><?= e($detailData['tenTG']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nhà xuất bản</label>
                            <div class="form-control bg-light"><?= e($detailData['tenNXB']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Thể loại</label>
                            <div class="form-control bg-light"><?= e($detailData['tenTL']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngôn ngữ</label>
                            <div class="form-control bg-light"><?= e($detailData['tenNN']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kệ sách</label>
                            <div class="form-control bg-light"><?= e($detailData['tenKS']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Năm xuất bản</label>
                            <div class="form-control bg-light"><?= e($detailData['namXB']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số lượng</label>
                            <div class="form-control bg-light"><?= e($detailData['soLuong']) ?></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <div class="form-control bg-light"><?= e($detailData['trangThai']) ?></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Mô tả</label>
                            <textarea class="form-control bg-light" rows="4" readonly><?= e($detailData['moTa']) ?></textarea>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-4" data-bs-dismiss="modal">Đóng</button>
            </div>
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