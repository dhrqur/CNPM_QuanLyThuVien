<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_roles(['Quản lý thư viện', 'Thủ thư']);

$pageTitle = 'Mượn trả';
$flash = get_flash();

$errors = [];
$openModal = '';
$editData = null;
$editDetails = [];
$detailData = null;
$detailDetails = [];

$search = trim($_GET['search'] ?? $_POST['search'] ?? '');
$page = max(1, intval($_GET['p'] ?? $_POST['p'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

function back_url_muontra($search, $page) {
    return "muontra.php?search=" . urlencode($search) . "&p=" . intval($page);
}

function generateMaMT($conn) {
    $rs = mysqli_query($conn, "SELECT maMT FROM MuonTra ORDER BY maMT DESC LIMIT 1");
    $newId = 1;
    if ($row = mysqli_fetch_assoc($rs)) {
        $num = intval(substr($row['maMT'], 2));
        $newId = $num + 1;
    }
    return "MT" . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

function syncBookStatus($conn, $maSach) {
    $maSach = db_escape($conn, $maSach);
    $q = mysqli_query($conn, "SELECT soLuong FROM Sach WHERE maSach='$maSach'");
    if ($row = mysqli_fetch_assoc($q)) {
        $trangThai = ((int)$row['soLuong'] > 0) ? 'Còn' : 'Hết';
        mysqli_query($conn, "UPDATE Sach SET trangThai='$trangThai' WHERE maSach='$maSach'");
    }
}

function syncBorrowStatusAll($conn) {
    mysqli_query($conn, "
        UPDATE MuonTra
        SET trangThai = CASE
            WHEN ngayTra IS NOT NULL THEN 'Đã trả'
            WHEN ngayTra IS NULL AND hanTra < CURDATE() THEN 'Quá hạn'
            ELSE 'Đang mượn'
        END
    ");
}

function getBorrowBooks($conn, $maMT) {
    $maMT = db_escape($conn, $maMT);
    $data = [];
    $q = mysqli_query($conn, "
        SELECT ct.*, s.tenSach
        FROM ChiTietMuonTra ct
        LEFT JOIN Sach s ON ct.maSach = s.maSach
        WHERE ct.maMT='$maMT'
    ");
    while ($row = mysqli_fetch_assoc($q)) {
        $data[] = $row;
    }
    return $data;
}

function returnOldBooksToStock($conn, $maMT) {
    $details = getBorrowBooks($conn, $maMT);
    foreach ($details as $item) {
        $maSach = db_escape($conn, $item['maSach']);
        $soLuong = (int)$item['soLuong'];
        mysqli_query($conn, "UPDATE Sach SET soLuong = soLuong + $soLuong WHERE maSach='$maSach'");
        syncBookStatus($conn, $maSach);
    }
}

function deductBooksFromStock($conn, $maSachArr, $soLuongArr) {
    foreach ($maSachArr as $i => $maSach) {
        $maSach = db_escape($conn, $maSach);
        $soLuong = (int)$soLuongArr[$i];
        mysqli_query($conn, "UPDATE Sach SET soLuong = soLuong - $soLuong WHERE maSach='$maSach'");
        syncBookStatus($conn, $maSach);
    }
}

function insertBorrowDetails($conn, $maMT, $maSachArr, $soLuongArr) {
    $maMTEsc = db_escape($conn, $maMT);
    foreach ($maSachArr as $i => $maSach) {
        $maSachEsc = db_escape($conn, $maSach);
        $soLuong = (int)$soLuongArr[$i];
        mysqli_query($conn, "
            INSERT INTO ChiTietMuonTra(maMT, maSach, soLuong, ghiChu)
            VALUES('$maMTEsc','$maSachEsc','$soLuong','')
        ");
    }
}

function buildBookCounter($maSachArr, $soLuongArr) {
    $counter = [];
    foreach ($maSachArr as $i => $maSach) {
        $maSach = trim($maSach);
        $soLuong = (int)($soLuongArr[$i] ?? 0);
        if ($maSach !== '') {
            if (!isset($counter[$maSach])) $counter[$maSach] = 0;
            $counter[$maSach] += $soLuong;
        }
    }
    return $counter;
}

function validateBorrowForm($conn, $maDG, $maNV, $ngayMuon, $hanTra, $maSachArr, $soLuongArr, &$errors, $ignoreBorrowId = null) {
    if ($maDG === '') $errors['maDG'] = 'Vui lòng chọn độc giả';
    if ($maNV === '') $errors['maNV'] = 'Vui lòng chọn nhân viên';
    if ($ngayMuon === '') $errors['ngayMuon'] = 'Ngày mượn không được để trống';
    if ($hanTra === '') $errors['hanTra'] = 'Hạn trả không được để trống';

    if ($ngayMuon !== '' && $hanTra !== '' && strtotime($hanTra) < strtotime($ngayMuon)) {
        $errors['hanTra'] = 'Hạn trả phải bằng hoặc sau ngày mượn';
    }

    if (!is_array($maSachArr) || count($maSachArr) === 0) {
        $errors['bookRows'] = 'Phiếu mượn phải có ít nhất 1 sách';
        return;
    }

    foreach ($maSachArr as $i => $maSach) {
        $maSach = trim($maSach);
        $soLuong = trim($soLuongArr[$i] ?? '');

        if ($maSach === '') $errors["maSach_$i"] = 'Chưa chọn sách';
        if ($soLuong === '') $errors["soLuong_$i"] = 'Chưa nhập số lượng';
        elseif (!is_numeric($soLuong) || (int)$soLuong <= 0) $errors["soLuong_$i"] = 'Số lượng phải > 0';
    }

    if ($maDG !== '') {
        $maDGEsc = db_escape($conn, $maDG);
        $checkCard = mysqli_query($conn, "SELECT * FROM TheThuVien WHERE maDG='$maDGEsc' AND ngayHetHan >= CURDATE()");
        if (mysqli_num_rows($checkCard) === 0) {
            $errors['maDG'] = 'Độc giả chưa có thẻ thư viện còn hạn';
        }
    }

    if ($maDG !== '') {
        $maDGEsc = db_escape($conn, $maDG);
        $sql = "SELECT * FROM MuonTra WHERE maDG='$maDGEsc' AND trangThai IN ('Đang mượn','Quá hạn')";
        if ($ignoreBorrowId !== null) {
            $ignoreBorrowId = db_escape($conn, $ignoreBorrowId);
            $sql .= " AND maMT<>'$ignoreBorrowId'";
        }
        $checkBorrow = mysqli_query($conn, $sql);
        if (mysqli_num_rows($checkBorrow) > 0) {
            $errors['maDG'] = 'Độc giả đang có phiếu mượn chưa trả';
        }
    }

    $counter = buildBookCounter($maSachArr, $soLuongArr);
    foreach ($counter as $maSach => $qty) {
        $maSachEsc = db_escape($conn, $maSach);
        $qBook = mysqli_query($conn, "SELECT * FROM Sach WHERE maSach='$maSachEsc'");
        $book = mysqli_fetch_assoc($qBook);
        if (!$book) {
            $errors['bookRows'] = 'Có sách không tồn tại';
        } elseif ((int)$book['soLuong'] < $qty) {
            $errors['bookRows'] = 'Số lượng mượn vượt quá tồn kho của một số sách';
        }
    }
}

syncBorrowStatusAll($conn);

/* Trả sách */
if (isset($_GET['return'])) {
    $id = db_escape($conn, $_GET['return']);
    $q = mysqli_query($conn, "SELECT * FROM MuonTra WHERE maMT='$id'");
    $borrow = mysqli_fetch_assoc($q);

    if ($borrow && $borrow['trangThai'] !== 'Đã trả') {
        $details = getBorrowBooks($conn, $id);
        foreach ($details as $item) {
            $maSach = db_escape($conn, $item['maSach']);
            $soLuong = (int)$item['soLuong'];
            mysqli_query($conn, "UPDATE Sach SET soLuong = soLuong + $soLuong WHERE maSach='$maSach'");
            syncBookStatus($conn, $maSach);
        }
        mysqli_query($conn, "UPDATE MuonTra SET ngayTra=CURDATE(), trangThai='Đã trả' WHERE maMT='$id'");
        set_flash('success', 'Trả sách thành công.');
    }

    redirect(back_url_muontra($search, $page));
}

/* Gia hạn */
if (isset($_GET['extend'])) {
    $id = db_escape($conn, $_GET['extend']);
    mysqli_query($conn, "
        UPDATE MuonTra
        SET hanTra = DATE_ADD(hanTra, INTERVAL 14 DAY), trangThai='Đang mượn'
        WHERE maMT='$id' AND trangThai IN ('Đang mượn','Quá hạn')
    ");
    set_flash('success', 'Gia hạn thêm 14 ngày thành công.');
    redirect(back_url_muontra($search, $page));
}

/* Xóa phiếu */
if (isset($_GET['delete'])) {
    $id = db_escape($conn, $_GET['delete']);
    $q = mysqli_query($conn, "SELECT * FROM MuonTra WHERE maMT='$id'");
    $row = mysqli_fetch_assoc($q);

    if ($row && $row['trangThai'] === 'Đang mượn') {
        set_flash('danger', 'Không thể xóa phiếu đang mượn.');
    } else {
        mysqli_query($conn, "DELETE FROM ChiTietMuonTra WHERE maMT='$id'");
        mysqli_query($conn, "DELETE FROM MuonTra WHERE maMT='$id'");
        set_flash('success', 'Xóa phiếu mượn thành công.');
    }

    redirect(back_url_muontra($search, $page));
}

/* Thêm phiếu */
if (isset($_POST['add'])) {
    $maMT = generateMaMT($conn);
    $maDG = trim($_POST['maDG'] ?? '');
    $maNV = trim($_POST['maNV'] ?? '');
    $ngayMuon = trim($_POST['ngayMuon'] ?? '');
    $hanTra = trim($_POST['hanTra'] ?? '');
    $maSachArr = $_POST['maSach'] ?? [];
    $soLuongArr = $_POST['soLuong'] ?? [];

    validateBorrowForm($conn, $maDG, $maNV, $ngayMuon, $hanTra, $maSachArr, $soLuongArr, $errors);

    if (empty($errors)) {
        $maDGEsc = db_escape($conn, $maDG);
        $maNVEsc = db_escape($conn, $maNV);
        $ngayMuonEsc = db_escape($conn, $ngayMuon);
        $hanTraEsc = db_escape($conn, $hanTra);

        mysqli_query($conn, "
            INSERT INTO MuonTra(maMT, maDG, maNV, ngayMuon, hanTra, ngayTra, trangThai)
            VALUES('$maMT','$maDGEsc','$maNVEsc','$ngayMuonEsc','$hanTraEsc',NULL,'Đang mượn')
        ");

        insertBorrowDetails($conn, $maMT, $maSachArr, $soLuongArr);
        deductBooksFromStock($conn, $maSachArr, $soLuongArr);

        set_flash('success', 'Tạo phiếu mượn thành công.');
        redirect(back_url_muontra($_POST['search'] ?? $search, $_POST['p'] ?? $page));
    } else {
        $openModal = 'addModal';
    }
}

/* Mở modal sửa */
if (isset($_GET['edit'])) {
    $id = db_escape($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM MuonTra WHERE maMT='$id'");
    $editData = mysqli_fetch_assoc($q);
    $editDetails = getBorrowBooks($conn, $id);
    if ($editData) $openModal = 'editModal';
}

/* Xem chi tiết phiếu */
if (isset($_GET['view'])) {
    $id = db_escape($conn, $_GET['view']);
    $q = mysqli_query($conn, "
        SELECT mt.*, dg.tenDG, nv.tenNV
        FROM MuonTra mt
        LEFT JOIN DocGia dg ON mt.maDG = dg.maDG
        LEFT JOIN NhanVien nv ON mt.maNV = nv.maNV
        WHERE mt.maMT='$id'
    ");
    $detailData = mysqli_fetch_assoc($q);
    $detailDetails = getBorrowBooks($conn, $id);
    if ($detailData) $openModal = 'detailModal';
}

/* Cập nhật phiếu */
if (isset($_POST['update'])) {
    $maMT = trim($_POST['maMT'] ?? '');
    $maDG = trim($_POST['maDG'] ?? '');
    $maNV = trim($_POST['maNV'] ?? '');
    $ngayMuon = trim($_POST['ngayMuon'] ?? '');
    $hanTra = trim($_POST['hanTra'] ?? '');
    $maSachArr = $_POST['maSach'] ?? [];
    $soLuongArr = $_POST['soLuong'] ?? [];

    $maMTEsc = db_escape($conn, $maMT);

    mysqli_begin_transaction($conn);
    try {
        returnOldBooksToStock($conn, $maMT);

        validateBorrowForm($conn, $maDG, $maNV, $ngayMuon, $hanTra, $maSachArr, $soLuongArr, $errors, $maMT);

        if (!empty($errors)) {
            throw new Exception('Validation failed');
        }

        $maDGEsc = db_escape($conn, $maDG);
        $maNVEsc = db_escape($conn, $maNV);
        $ngayMuonEsc = db_escape($conn, $ngayMuon);
        $hanTraEsc = db_escape($conn, $hanTra);

        mysqli_query($conn, "
            UPDATE MuonTra
            SET maDG='$maDGEsc', maNV='$maNVEsc', ngayMuon='$ngayMuonEsc', hanTra='$hanTraEsc', ngayTra=NULL
            WHERE maMT='$maMTEsc'
        ");

        mysqli_query($conn, "DELETE FROM ChiTietMuonTra WHERE maMT='$maMTEsc'");
        insertBorrowDetails($conn, $maMT, $maSachArr, $soLuongArr);
        deductBooksFromStock($conn, $maSachArr, $soLuongArr);
        syncBorrowStatusAll($conn);

        mysqli_commit($conn);
        set_flash('success', 'Cập nhật phiếu mượn thành công.');
        redirect(back_url_muontra($_POST['search'] ?? $search, $_POST['p'] ?? $page));
    } catch (Exception $e) {
        mysqli_rollback($conn);

        $editData = [
            'maMT' => $maMT,
            'maDG' => $maDG,
            'maNV' => $maNV,
            'ngayMuon' => $ngayMuon,
            'hanTra' => $hanTra
        ];
        $editDetails = [];
        foreach ($maSachArr as $i => $ms) {
            $editDetails[] = [
                'maSach' => $ms,
                'soLuong' => $soLuongArr[$i] ?? ''
            ];
        }
        $openModal = 'editModal';
    }
}

/* Search + pagination */
$searchEsc = db_escape($conn, $search);

$countRs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM MuonTra mt
    LEFT JOIN DocGia dg ON mt.maDG = dg.maDG
    LEFT JOIN NhanVien nv ON mt.maNV = nv.maNV
    WHERE mt.maMT LIKE '%$searchEsc%'
       OR dg.tenDG LIKE '%$searchEsc%'
       OR nv.tenNV LIKE '%$searchEsc%'
       OR mt.trangThai LIKE '%$searchEsc%'
");
$totalRows = (int)mysqli_fetch_assoc($countRs)['total'];
$totalPages = max(1, (int)ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

$result = mysqli_query($conn, "
    SELECT mt.*, dg.tenDG, nv.tenNV
    FROM MuonTra mt
    LEFT JOIN DocGia dg ON mt.maDG = dg.maDG
    LEFT JOIN NhanVien nv ON mt.maNV = nv.maNV
    WHERE mt.maMT LIKE '%$searchEsc%'
       OR dg.tenDG LIKE '%$searchEsc%'
       OR nv.tenNV LIKE '%$searchEsc%'
       OR mt.trangThai LIKE '%$searchEsc%'
    ORDER BY mt.maMT DESC
    LIMIT $offset, $limit
");

/* HTML options cho JS add row */
$bookOptionsHtml = "";
$bookJsRs = mysqli_query($conn, "SELECT * FROM Sach ORDER BY tenSach ASC");
while ($b = mysqli_fetch_assoc($bookJsRs)) {
    $title = e($b['tenSach']) . " (còn " . (int)$b['soLuong'] . ")";
    $bookOptionsHtml .= '<option value="' . e($b['maSach']) . '">' . $title . '</option>';
}

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
                    <h2 class="page-title">Quản lý mượn trả</h2>
                    <p class="page-desc">Một phiếu mượn có thể gồm nhiều loại sách khác nhau.</p>
                </div>
                <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Thêm phiếu mượn
                </button>
            </div>

            <form method="GET" class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Tìm phiếu mượn..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-outline-orange" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="table-wrap">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Độc giả</th>
                            <th>Nhân viên</th>
                            <th>Sách mượn</th>
                            <th>Ngày mượn</th>
                            <th>Hạn trả</th>
                            <th>Ngày trả</th>
                            <th>Trạng thái</th>
                            <th width="320">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($result) === 0): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
                    <?php else: ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <?php $books = getBorrowBooks($conn, $row['maMT']); ?>
                            <tr>
                                <td><?= e($row['maMT']) ?></td>
                                <td><?= e($row['tenDG']) ?></td>
                                <td><?= e($row['tenNV']) ?></td>
                                <td>
                                    <?php if (count($books) > 0): ?>
                                        <?php foreach($books as $bk): ?>
                                            <div><strong><?= e($bk['tenSach']) ?></strong> × <?= (int)$bk['soLuong'] ?></div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Không có dữ liệu</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($row['ngayMuon']) ?></td>
                                <td><?= e($row['hanTra']) ?></td>
                                <td><?= e($row['ngayTra']) ?></td>
                                <td>
                                    <?php if ($row['trangThai'] === 'Đã trả'): ?>
                                        <span class="badge-soft-success">Đã trả</span>
                                    <?php elseif ($row['trangThai'] === 'Quá hạn'): ?>
                                        <span class="badge-soft-danger">Quá hạn</span>
                                    <?php else: ?>
                                        <span class="badge-soft-warning">Đang mượn</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-btns">
                                    <a href="?view=<?= urlencode($row['maMT']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                    <a href="?edit=<?= urlencode($row['maMT']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-warning btn-sm">Sửa</a>
                                    <?php if ($row['trangThai'] !== 'Đã trả'): ?>
                                        <a href="?return=<?= urlencode($row['maMT']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-success btn-sm">Trả</a>
                                        <a href="?extend=<?= urlencode($row['maMT']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>" class="btn btn-primary btn-sm">Gia hạn</a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('muontra.php?delete=<?= urlencode($row['maMT']) ?>&search=<?= urlencode($search) ?>&p=<?= $page ?>')">Xóa</button>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm phiếu mượn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
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

                        <div class="col-md-3">
                            <label class="form-label">Nhân viên</label>
                            <select name="maNV" class="form-select <?= isset($errors['maNV']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn nhân viên --</option>
                                <?php
                                $nvAdd = mysqli_query($conn, "SELECT * FROM NhanVien ORDER BY tenNV ASC");
                                while($nv = mysqli_fetch_assoc($nvAdd)):
                                ?>
                                <option value="<?= e($nv['maNV']) ?>" <?= old('maNV') === $nv['maNV'] ? 'selected' : '' ?>><?= e($nv['tenNV']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maNV'])): ?><div class="invalid-feedback"><?= e($errors['maNV']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Ngày mượn</label>
                            <input type="date" name="ngayMuon" class="form-control <?= isset($errors['ngayMuon']) ? 'is-invalid' : '' ?>" value="<?= e(old('ngayMuon', date('Y-m-d'))) ?>">
                            <?php if(isset($errors['ngayMuon'])): ?><div class="invalid-feedback"><?= e($errors['ngayMuon']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Hạn trả</label>
                            <input type="date" name="hanTra" class="form-control <?= isset($errors['hanTra']) ? 'is-invalid' : '' ?>" value="<?= e(old('hanTra', date('Y-m-d', strtotime('+14 days')))) ?>">
                            <?php if(isset($errors['hanTra'])): ?><div class="invalid-feedback"><?= e($errors['hanTra']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>Danh sách sách mượn</strong>
                            <div class="text-muted small">Một phiếu mượn có thể gồm nhiều loại sách khác nhau.</div>
                        </div>
                        <button type="button" class="btn btn-outline-orange" onclick='addBorrowRow("borrow-books-wrap-add", `<?= $bookOptionsHtml ?>`)'>
                            <i class="bi bi-plus-lg"></i> Thêm dòng sách
                        </button>
                    </div>

                    <?php if(isset($errors['bookRows'])): ?>
                        <div class="invalid-feedback mb-3" style="display:block"><?= e($errors['bookRows']) ?></div>
                    <?php endif; ?>

                    <div id="borrow-books-wrap-add">
                        <?php
                        $postedBooks = $_POST['maSach'] ?? [''];
                        $postedQtys = $_POST['soLuong'] ?? [''];
                        foreach ($postedBooks as $i => $bookVal):
                        ?>
                        <div class="borrow-book-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-7">
                                    <label class="form-label">Sách</label>
                                    <select name="maSach[]" class="form-select <?= isset($errors["maSach_$i"]) ? 'is-invalid' : '' ?>">
                                        <option value="">-- Chọn sách --</option>
                                        <?php
                                        $sachRows = mysqli_query($conn, "SELECT * FROM Sach ORDER BY tenSach ASC");
                                        while($s = mysqli_fetch_assoc($sachRows)):
                                        ?>
                                        <option value="<?= e($s['maSach']) ?>" <?= $bookVal === $s['maSach'] ? 'selected' : '' ?>>
                                            <?= e($s['tenSach']) ?> (còn <?= (int)$s['soLuong'] ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <?php if(isset($errors["maSach_$i"])): ?><div class="invalid-feedback"><?= e($errors["maSach_$i"]) ?></div><?php endif; ?>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Số lượng</label>
                                    <input type="number" min="1" name="soLuong[]" class="form-control <?= isset($errors["soLuong_$i"]) ? 'is-invalid' : '' ?>" value="<?= e($postedQtys[$i] ?? '') ?>">
                                    <?php if(isset($errors["soLuong_$i"])): ?><div class="invalid-feedback"><?= e($errors["soLuong_$i"]) ?></div><?php endif; ?>
                                </div>

                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger w-100" onclick="removeBorrowRow(this, 'borrow-books-wrap-add')">Xóa dòng</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button name="add" class="btn btn-orange">Lưu phiếu mượn</button>
                    <button type="button" class="btn btn-secondary rounded-4" data-bs-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="search" value="<?= e($search) ?>">
                <input type="hidden" name="p" value="<?= $page ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa phiếu mượn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if($editData): ?>
                    <input type="hidden" name="maMT" value="<?= e($editData['maMT']) ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
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

                        <div class="col-md-3">
                            <label class="form-label">Nhân viên</label>
                            <select name="maNV" class="form-select <?= isset($errors['maNV']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Chọn nhân viên --</option>
                                <?php
                                $nvEdit = mysqli_query($conn, "SELECT * FROM NhanVien ORDER BY tenNV ASC");
                                while($nv = mysqli_fetch_assoc($nvEdit)):
                                ?>
                                <option value="<?= e($nv['maNV']) ?>" <?= $editData['maNV'] === $nv['maNV'] ? 'selected' : '' ?>><?= e($nv['tenNV']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <?php if(isset($errors['maNV'])): ?><div class="invalid-feedback"><?= e($errors['maNV']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Ngày mượn</label>
                            <input type="date" name="ngayMuon" class="form-control <?= isset($errors['ngayMuon']) ? 'is-invalid' : '' ?>" value="<?= e($editData['ngayMuon']) ?>">
                            <?php if(isset($errors['ngayMuon'])): ?><div class="invalid-feedback"><?= e($errors['ngayMuon']) ?></div><?php endif; ?>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Hạn trả</label>
                            <input type="date" name="hanTra" class="form-control <?= isset($errors['hanTra']) ? 'is-invalid' : '' ?>" value="<?= e($editData['hanTra']) ?>">
                            <?php if(isset($errors['hanTra'])): ?><div class="invalid-feedback"><?= e($errors['hanTra']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong>Danh sách sách trong phiếu</strong>
                            <div class="text-muted small">Có thể sửa nhiều đầu sách trong cùng một phiếu.</div>
                        </div>
                        <button type="button" class="btn btn-outline-orange" onclick='addBorrowRow("borrow-books-wrap-edit", `<?= $bookOptionsHtml ?>`)'>
                            <i class="bi bi-plus-lg"></i> Thêm dòng sách
                        </button>
                    </div>

                    <?php if(isset($errors['bookRows'])): ?>
                        <div class="invalid-feedback mb-3" style="display:block"><?= e($errors['bookRows']) ?></div>
                    <?php endif; ?>

                    <div id="borrow-books-wrap-edit">
                        <?php if(count($editDetails) > 0): ?>
                            <?php foreach($editDetails as $i => $item): ?>
                            <div class="borrow-book-row">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-7">
                                        <label class="form-label">Sách</label>
                                        <select name="maSach[]" class="form-select <?= isset($errors["maSach_$i"]) ? 'is-invalid' : '' ?>">
                                            <option value="">-- Chọn sách --</option>
                                            <?php
                                            $sachRows = mysqli_query($conn, "SELECT * FROM Sach ORDER BY tenSach ASC");
                                            while($s = mysqli_fetch_assoc($sachRows)):
                                            ?>
                                            <option value="<?= e($s['maSach']) ?>" <?= $item['maSach'] === $s['maSach'] ? 'selected' : '' ?>>
                                                <?= e($s['tenSach']) ?> (còn <?= (int)$s['soLuong'] ?>)
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <?php if(isset($errors["maSach_$i"])): ?><div class="invalid-feedback"><?= e($errors["maSach_$i"]) ?></div><?php endif; ?>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Số lượng</label>
                                        <input type="number" min="1" name="soLuong[]" class="form-control <?= isset($errors["soLuong_$i"]) ? 'is-invalid' : '' ?>" value="<?= e($item['soLuong']) ?>">
                                        <?php if(isset($errors["soLuong_$i"])): ?><div class="invalid-feedback"><?= e($errors["soLuong_$i"]) ?></div><?php endif; ?>
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger w-100" onclick="removeBorrowRow(this, 'borrow-books-wrap-edit')">Xóa dòng</button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="borrow-book-row">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-7">
                                        <label class="form-label">Sách</label>
                                        <select name="maSach[]" class="form-select">
                                            <option value="">-- Chọn sách --</option>
                                            <?php
                                            $sachRows = mysqli_query($conn, "SELECT * FROM Sach ORDER BY tenSach ASC");
                                            while($s = mysqli_fetch_assoc($sachRows)):
                                            ?>
                                            <option value="<?= e($s['maSach']) ?>"><?= e($s['tenSach']) ?> (còn <?= (int)$s['soLuong'] ?>)</option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Số lượng</label>
                                        <input type="number" min="1" name="soLuong[]" class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger w-100" onclick="removeBorrowRow(this, 'borrow-books-wrap-edit')">Xóa dòng</button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button name="update" class="btn btn-orange">Cập nhật phiếu</button>
                    <button type="button" class="btn btn-secondary rounded-4" data-bs-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DETAIL MODAL -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết phiếu mượn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <?php if ($detailData): ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mã phiếu</label>
                            <div class="form-control bg-light"><?= e($detailData['maMT']) ?></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Độc giả</label>
                            <div class="form-control bg-light"><?= e($detailData['tenDG']) ?></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nhân viên</label>
                            <div class="form-control bg-light"><?= e($detailData['tenNV']) ?></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ngày mượn</label>
                            <div class="form-control bg-light"><?= e($detailData['ngayMuon']) ?></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Hạn trả</label>
                            <div class="form-control bg-light"><?= e($detailData['hanTra']) ?></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ngày trả</label>
                            <div class="form-control bg-light"><?= e($detailData['ngayTra'] ?: 'Chưa trả') ?></div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <div class="form-control bg-light"><?= e($detailData['trangThai']) ?></div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fw-bold">Danh sách sách mượn</label>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="80">STT</th>
                                        <th>Mã sách</th>
                                        <th>Tên sách</th>
                                        <th width="120">Số lượng</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($detailDetails) > 0): ?>
                                        <?php foreach ($detailDetails as $index => $item): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= e($item['maSach']) ?></td>
                                                <td><?= e($item['tenSach']) ?></td>
                                                <td><?= (int)$item['soLuong'] ?></td>
                                                <td><?= e($item['ghiChu']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Không có sách trong phiếu mượn.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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

<script>
function addBorrowRow(wrapperId, bookOptionsHtml) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;

    const row = document.createElement('div');
    row.className = 'borrow-book-row';
    row.innerHTML = `
        <div class="row g-3 align-items-end">
            <div class="col-md-7">
                <label class="form-label">Sách</label>
                <select name="maSach[]" class="form-select">
                    <option value="">-- Chọn sách --</option>
                    ${bookOptionsHtml}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Số lượng</label>
                <input type="number" min="1" name="soLuong[]" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger w-100" onclick="removeBorrowRow(this, '${wrapperId}')">Xóa dòng</button>
            </div>
        </div>
    `;
    wrapper.appendChild(row);
}

function removeBorrowRow(button, wrapperId) {
    const wrapper = document.getElementById(wrapperId);
    if (!wrapper) return;

    const rows = wrapper.querySelectorAll('.borrow-book-row');
    if (rows.length <= 1) {
        const maSachSelect = rows[0].querySelector('select[name="maSach[]"]');
        const soLuongInput = rows[0].querySelector('input[name="soLuong[]"]');
        if (maSachSelect) maSachSelect.value = '';
        if (soLuongInput) soLuongInput.value = '';
        return;
    }

    const row = button.closest('.borrow-book-row');
    if (row) row.remove();
}
</script>

<?php if ($openModal): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('<?= $openModal ?>'));
    modal.show();
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>