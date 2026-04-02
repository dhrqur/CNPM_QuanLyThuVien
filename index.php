<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Dashboard';

/* Đồng bộ trạng thái */
mysqli_query($conn, "
    UPDATE TheThuVien
    SET trangThai = CASE
        WHEN ngayHetHan >= CURDATE() THEN 'Còn hạn'
        ELSE 'Hết hạn'
    END
");

mysqli_query($conn, "
    UPDATE MuonTra
    SET trangThai = CASE
        WHEN ngayTra IS NOT NULL THEN 'Đã trả'
        WHEN ngayTra IS NULL AND hanTra < CURDATE() THEN 'Quá hạn'
        ELSE 'Đang mượn'
    END
");

/* Thống kê */
$totalBooksResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Sach");
$totalBooks = (int) mysqli_fetch_assoc($totalBooksResult)['total'];

$borrowingBooksResult = mysqli_query($conn, "
    SELECT COALESCE(SUM(ct.soLuong),0) AS total
    FROM ChiTietMuonTra ct
    INNER JOIN MuonTra mt ON mt.maMT = ct.maMT
    WHERE mt.trangThai = 'Đang mượn'
");
$borrowingBooks = (int) mysqli_fetch_assoc($borrowingBooksResult)['total'];

$totalReadersResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM DocGia");
$totalReaders = (int) mysqli_fetch_assoc($totalReadersResult)['total'];

$totalCopiesResult = mysqli_query($conn, "SELECT COALESCE(SUM(soLuong),0) AS total FROM Sach");
$totalCopies = (int) mysqli_fetch_assoc($totalCopiesResult)['total'];

$topBorrowers = mysqli_query($conn, "
    SELECT dg.tenDG, COUNT(ct.maSach) AS tongLuot
    FROM MuonTra mt
    INNER JOIN DocGia dg ON dg.maDG = mt.maDG
    LEFT JOIN ChiTietMuonTra ct ON ct.maMT = mt.maMT
    GROUP BY dg.maDG, dg.tenDG
    ORDER BY tongLuot DESC, dg.tenDG ASC
    LIMIT 5
");

$topBooks = mysqli_query($conn, "
    SELECT s.tenSach, COALESCE(SUM(ct.soLuong),0) AS tongMuon
    FROM Sach s
    LEFT JOIN ChiTietMuonTra ct ON ct.maSach = s.maSach
    GROUP BY s.maSach, s.tenSach
    ORDER BY tongMuon DESC, s.tenSach ASC
    LIMIT 5
");

$overdues = mysqli_query($conn, "
    SELECT mt.maMT, dg.tenDG, mt.hanTra, mt.trangThai
    FROM MuonTra mt
    INNER JOIN DocGia dg ON dg.maDG = mt.maDG
    WHERE mt.ngayTra IS NULL AND mt.hanTra < CURDATE()
    ORDER BY mt.hanTra ASC
    LIMIT 10
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

        <section class="stats-grid">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Tổng đầu sách</div>
                        <div class="stat-value"><?= $totalBooks ?></div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-book"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Sách đang mượn</div>
                        <div class="stat-value"><?= $borrowingBooks ?></div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Tổng độc giả</div>
                        <div class="stat-value"><?= $totalReaders ?></div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Tổng bản sách</div>
                        <div class="stat-value"><?= $totalCopies ?></div>
                    </div>
                    <div class="stat-icon"><i class="bi bi-collection"></i></div>
                </div>
            </div>
        </section>

        <section class="grid-two mb-4">
            <div class="content-card p-4">
                <div class="section-title mb-3">Biểu đồ tổng quan</div>
                <canvas id="borrowChart" height="120"></canvas>
            </div>

            <div class="content-card p-4">
                <div class="section-title mb-3">Sách được mượn nhiều</div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Tên sách</th>
                                <th class="text-end">Lượt mượn</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($book = mysqli_fetch_assoc($topBooks)): ?>
                            <tr>
                                <td><?= e($book['tenSach']) ?></td>
                                <td class="text-end"><?= (int)$book['tongMuon'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="grid-two-equal">
            <div class="content-card p-4">
                <div class="section-title mb-3">Độc giả mượn nhiều sách</div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Độc giả</th>
                                <th class="text-end">Lượt mượn</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($reader = mysqli_fetch_assoc($topBorrowers)): ?>
                            <tr>
                                <td><?= e($reader['tenDG']) ?></td>
                                <td class="text-end"><?= (int)$reader['tongLuot'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card p-4">
                <div class="section-title mb-3">Danh sách quá hạn</div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Độc giả</th>
                                <th>Hạn trả</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($overdues) === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Hiện chưa có phiếu quá hạn.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($item = mysqli_fetch_assoc($overdues)): ?>
                                <tr>
                                    <td><?= e($item['maMT']) ?></td>
                                    <td><?= e($item['tenDG']) ?></td>
                                    <td><?= e($item['hanTra']) ?></td>
                                    <td><span class="badge text-bg-danger">Quá hạn</span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartCtx = document.getElementById('borrowChart');
            new Chart(chartCtx, {
                type: 'bar',
                data: {
                    labels: ['Tổng đầu sách', 'Sách đang mượn', 'Tổng độc giả', 'Tổng bản sách'],
                    datasets: [{
                        label: 'Số lượng',
                        data: [<?= $totalBooks ?>, <?= $borrowingBooks ?>, <?= $totalReaders ?>, <?= $totalCopies ?>],
                        backgroundColor: ['#f97316','#fb923c','#fdba74','#fed7aa'],
                        borderRadius: 12
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true },
                        x: { grid: { display: false } }
                    }
                }
            });
        </script>
    </main>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>