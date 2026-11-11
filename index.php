
<?php
require_once "config/auth.php";
require_once "config/koneksi.php";

// STATISTIK DASHBOARD
$total_pelanggan = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_pelanggan")
)['total'];

$total_produk = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_produk")
)['total'];

$total_transaksi = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_transaksi")
)['total'];

$total_omzet = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COALESCE(SUM(total_belanja), 0) AS total FROM tbl_transaksi")
)['total'];

// PENJUALAN HARI INI
$penjualan_hari_ini = mysqli_fetch_assoc(
    mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS jumlah,
                COALESCE(SUM(total_belanja), 0) AS omzet
         FROM tbl_transaksi
         WHERE DATE(tanggal) = CURDATE()"
    )
);

// TRANSAKSI TERBARU
$transaksi_terbaru = mysqli_query(
    $koneksi,
    "SELECT t.id_transaksi, t.tanggal, t.total_belanja,
            p.nama_pelanggan
     FROM tbl_transaksi t
     LEFT JOIN tbl_pelanggan p
       ON t.id_pelanggan = p.id_pelanggan
     ORDER BY t.id_transaksi DESC
     LIMIT 5"
);

$nama_user = $_SESSION['nama'] ?? 'Admin';

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard - Toko Lefina</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --orange: #f39b38;
    --orange-dark: #e88924;
    --dark: #252b3b;
    --muted: #8b93a7;
    --background: #f6f7fb;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--background);
    color: #303747;
}

/* SIDEBAR */
.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #fff;
    padding: 25px 18px;
    border-right: 1px solid #edf0f5;
    z-index: 1000;
    transition: .3s;
    overflow-y: auto;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 10px;
    margin-bottom: 42px;
}

.brand-icon {
    width: 43px;
    height: 43px;
    border-radius: 13px;
    background: linear-gradient(135deg, #ffb65e, #ed8a22);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 23px;
    box-shadow: 0 5px 13px rgba(243,155,56,.25);
}

.brand-name {
    font-size: 19px;
    font-weight: 800;
    color: #303747;
    letter-spacing: -.5px;
}

.brand-name span {
    color: var(--orange);
}

.brand-caption {
    font-size: 10px;
    color: #9ba2b1;
    margin-top: 3px;
}

.menu-label {
    font-size: 10px;
    font-weight: 700;
    color: #a4aaba;
    letter-spacing: 1.3px;
    padding: 0 13px;
    margin-bottom: 12px;
    margin-top: 25px;
}

.nav-link-custom {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 12px 14px;
    color: #737d91;
    text-decoration: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 5px;
    transition: .2s;
}

.nav-link-custom i {
    font-size: 18px;
}

.nav-link-custom:hover {
    background: #fff5e9;
    color: var(--orange-dark);
}

.nav-link-custom.active {
    background: linear-gradient(100deg, #fff1df, #fff8ef);
    color: #e58a26;
    box-shadow: inset 3px 0 #f39b38;
}

.sidebar-bottom {
    margin-top: 40px;
    border-top: 1px solid #edf0f5;
    padding-top: 20px;
}

.user-mini {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: #f8f9fc;
    border-radius: 12px;
}

.user-avatar {
    width: 37px;
    height: 37px;
    background: #fff0dd;
    color: #e58a26;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
}

.user-name {
    font-size: 12px;
    font-weight: 700;
}

.user-role {
    font-size: 10px;
    color: #9ba2b1;
}

/* MAIN */
.main {
    margin-left: 260px;
    padding: 28px 35px;
    min-height: 100vh;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-title {
    font-size: 22px;
    font-weight: 800;
    color: #303747;
}

.page-subtitle {
    font-size: 12px;
    color: var(--muted);
    margin-top: 5px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.date-label {
    background: white;
    border: 1px solid #edf0f5;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 12px;
    color: #697286;
}

.menu-toggle {
    display: none;
    background: white;
    border: 1px solid #eee;
    padding: 8px 12px;
    border-radius: 9px;
    font-size: 20px;
}

/* WELCOME */
.welcome-card {
    background: linear-gradient(110deg, #f39b38, #f8b45e);
    border-radius: 18px;
    padding: 28px 32px;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: 0 10px 25px rgba(243,155,56,.16);
}

.welcome-card::after {
    content: '';
    position: absolute;
    width: 210px;
    height: 210px;
    border: 35px solid rgba(255,255,255,.12);
    border-radius: 50%;
    right: 60px;
    top: -110px;
}

.welcome-card::before {
    content: '';
    position: absolute;
    width: 130px;
    height: 130px;
    background: rgba(255,255,255,.10);
    border-radius: 50%;
    right: -30px;
    bottom: -70px;
}

.welcome-content {
    position: relative;
    z-index: 2;
}

.welcome-title {
    font-size: 23px;
    font-weight: 800;
    margin-bottom: 8px;
}

.welcome-text {
    font-size: 12px;
    opacity: .92;
}

.welcome-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: white;
    color: #e58a26;
    text-decoration: none;
    border-radius: 9px;
    padding: 11px 16px;
    margin-top: 20px;
    font-size: 12px;
    font-weight: 700;
    transition: .2s;
}

.welcome-button:hover {
    background: #fff6eb;
    color: #d77c18;
    transform: translateY(-2px);
}

/* STATISTIK */
.section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 17px;
}

.section-heading h5 {
    font-size: 15px;
    font-weight: 800;
    margin: 0;
}

.section-heading small {
    font-size: 11px;
    color: var(--muted);
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    border: 1px solid #f0f1f5;
    height: 100%;
    transition: .25s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(30,40,70,.06);
}

.stat-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.icon-orange {
    background: #fff0dd;
    color: #ed9635;
}

.icon-blue {
    background: #e8f1ff;
    color: #5089df;
}

.icon-green {
    background: #e4f8ed;
    color: #3db477;
}

.icon-purple {
    background: #f0eaff;
    color: #8d6add;
}

.stat-label {
    font-size: 12px;
    color: #929aab;
    font-weight: 500;
    margin-bottom: 7px;
}

.stat-number {
    font-size: 24px;
    font-weight: 800;
    color: #303747;
    letter-spacing: -.7px;
    overflow-wrap: anywhere;
}

.stat-footer {
    font-size: 10px;
    color: #a1a8b7;
    margin-top: 8px;
}

/* INFORMASI PENJUALAN */
.sales-card {
    background: white;
    border-radius: 15px;
    border: 1px solid #f0f1f5;
    padding: 23px;
    height: 100%;
}

.sales-icon {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #fff0dd;
    color: #ed9635;
    font-size: 20px;
}

.sales-number {
    font-size: 22px;
    font-weight: 800;
    margin-top: 18px;
}

.sales-caption {
    font-size: 11px;
    color: #9ba2b1;
    margin-top: 5px;
}

/* TRANSAKSI TERBARU */
.table-card {
    background: white;
    border: 1px solid #f0f1f5;
    border-radius: 15px;
    padding: 22px;
    margin-top: 25px;
}

.table-modern {
    margin-bottom: 0;
    vertical-align: middle;
}

.table-modern thead th {
    background: #fafbfe;
    color: #9ba2b1;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .5px;
    padding: 14px;
    border: none;
    white-space: nowrap;
}

.table-modern tbody td {
    padding: 15px 14px;
    font-size: 12px;
    border-color: #f2f3f7;
}

.table-modern tbody tr:hover {
    background: #fffbf5;
}

.badge-id {
    background: #fff2e1;
    color: #e58a26;
    padding: 7px 10px;
    border-radius: 7px;
    font-weight: 700;
}

.badge-success-soft {
    background: #e5f7ed;
    color: #32a76b;
    padding: 6px 10px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 700;
}

.btn-view {
    background: #fff4e6;
    color: #df8829;
    text-decoration: none;
    border-radius: 7px;
    padding: 7px 11px;
    font-size: 11px;
    font-weight: 700;
}

.btn-view:hover {
    background: #f39b38;
    color: white;
}

.quick-card {
    background: white;
    border: 1px solid #f0f1f5;
    border-radius: 15px;
    padding: 22px;
    height: 100%;
    margin-top: 25px;
}

.quick-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 0;
    text-decoration: none;
    color: #4d5669;
    border-bottom: 1px solid #f1f2f6;
    font-size: 12px;
    font-weight: 600;
}

.quick-link:last-child {
    border-bottom: none;
}

.quick-link:hover {
    color: var(--orange-dark);
}

.quick-icon {
    width: 35px;
    height: 35px;
    border-radius: 9px;
    background: #fff3e4;
    color: #e58a26;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.footer {
    text-align: center;
    padding: 25px 0 5px;
    font-size: 10px;
    color: #a3aaba;
}

/* MOBILE */
.overlay {
    display: none;
}

@media (max-width: 991px) {
    .sidebar {
        left: -270px;
    }

    .sidebar.open {
        left: 0;
    }

    .main {
        margin-left: 0;
        padding: 22px;
    }

    .menu-toggle {
        display: inline-block;
    }

    .overlay.show {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(20,25,40,.35);
        z-index: 999;
    }
}

@media (max-width: 576px) {
    .main {
        padding: 17px;
    }

    .page-title {
        font-size: 18px;
    }

    .date-label {
        display: none;
    }

    .welcome-card {
        padding: 23px;
    }

    .welcome-title {
        font-size: 19px;
    }

    .stat-number {
        font-size: 21px;
    }
}
</style>
</head>

<body>

<div class="overlay" id="overlay" onclick="toggleMenu()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">

    <div class="brand">
        <div class="brand-icon">
            <i class="bi bi-shop"></i>
        </div>

        <div>
            <div class="brand-name">Toko <span>Lefina.</span></div>
            <div class="brand-caption">RETAIL MANAGEMENT SYSTEM</div>
        </div>
    </div>

    <div class="menu-label">MENU UTAMA</div>

    <a href="index.php" class="nav-link-custom active">
        <i class="bi bi-grid-1x2-fill"></i>
        Dashboard
    </a>

    <a href="pages/transaksi.php" class="nav-link-custom">
        <i class="bi bi-cart3"></i>
        Transaksi Penjualan
    </a>

    <div class="menu-label">MANAJEMEN DATA</div>

    <a href="pages/produk.php" class="nav-link-custom">
        <i class="bi bi-box-seam"></i>
        Data Produk
    </a>

    <a href="pages/kategori.php" class="nav-link-custom">
        <i class="bi bi-tags"></i>
        Kategori Produk
    </a>

    <a href="pages/pelanggan.php" class="nav-link-custom">
        <i class="bi bi-people"></i>
        Data Pelanggan
    </a>

    <div class="menu-label">LAPORAN</div>

    <a href="pages/laporan.php" class="nav-link-custom">
        <i class="bi bi-file-earmark-bar-graph"></i>
        Laporan Penjualan
    </a>

    <a href="pages/log.php" class="nav-link-custom">
        <i class="bi bi-clock-history"></i>
        Log Aktivitas
    </a>

    <div class="sidebar-bottom">

        <div class="user-mini">
            <div class="user-avatar">
                <?= strtoupper(substr($nama_user, 0, 1)) ?>
            </div>

            <div>
                <div class="user-name">
                    <?= htmlspecialchars($nama_user) ?>
                </div>
                <div class="user-role">Administrator</div>
            </div>
        </div>

        <a href="logout.php" class="nav-link-custom mt-3 text-danger"
           onclick="return confirm('Apakah kamu yakin ingin keluar?')">
            <i class="bi bi-box-arrow-right"></i>
            Keluar Aplikasi
        </a>

    </div>

</aside>

<!-- MAIN CONTENT -->
<main class="main">

    <!-- TOPBAR -->
    <div class="topbar">

        <div class="d-flex align-items-center gap-3">

            <button class="menu-toggle" onclick="toggleMenu()">
                <i class="bi bi-list"></i>
            </button>

            <div>
                <div class="page-title">Dashboard</div>
                <div class="page-subtitle">
                    Ringkasan aktivitas dan penjualan toko
                </div>
            </div>

        </div>

        <div class="topbar-right">
            <div class="date-label">
                <i class="bi bi-calendar3 me-2"></i>
                <?= date('d F Y') ?>
            </div>
        </div>

    </div>

    <!-- WELCOME -->
    <section class="welcome-card">

        <div class="welcome-content">

            <div class="welcome-title">
                Selamat datang, <?= htmlspecialchars($nama_user) ?>! 👋
            </div>

            <div class="welcome-text">
                Senang melihatmu kembali. Pantau perkembangan penjualan
                Toko Lefina melalui dashboard ini.
            </div>

            <a href="pages/transaksi.php" class="welcome-button">
                <i class="bi bi-plus-circle"></i>
                Buat Transaksi Baru
            </a>

        </div>

    </section>

    <!-- STATISTIK -->
    <div class="section-heading">
        <h5>Ringkasan Bisnis</h5>
        <small>Data keseluruhan toko</small>
    </div>

    <div class="row g-3">

        <div class="col-6 col-xl-3">
            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-orange">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <i class="bi bi-three-dots text-secondary"></i>
                </div>

                <div class="stat-label">Total Pelanggan</div>

                <div class="stat-number">
                    <?= number_format($total_pelanggan, 0, ',', '.') ?>
                </div>

                <div class="stat-footer">
                    <i class="bi bi-person-check me-1"></i>
                    Pelanggan terdaftar
                </div>

            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-blue">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <i class="bi bi-three-dots text-secondary"></i>
                </div>

                <div class="stat-label">Total Produk</div>

                <div class="stat-number">
                    <?= number_format($total_produk, 0, ',', '.') ?>
                </div>

                <div class="stat-footer">
                    <i class="bi bi-box me-1"></i>
                    Produk tersedia dalam data
                </div>

            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-green">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <i class="bi bi-three-dots text-secondary"></i>
                </div>

                <div class="stat-label">Total Transaksi</div>

                <div class="stat-number">
                    <?= number_format($total_transaksi, 0, ',', '.') ?>
                </div>

                <div class="stat-footer">
                    <i class="bi bi-check-circle me-1"></i>
                    Transaksi tercatat
                </div>

            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="stat-card">

                <div class="stat-top">
                    <div class="stat-icon icon-purple">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <i class="bi bi-three-dots text-secondary"></i>
                </div>

                <div class="stat-label">Total Omzet</div>

                <div class="stat-number" style="font-size:19px;">
                    <?= rupiah($total_omzet) ?>
                </div>

                <div class="stat-footer">
                    <i class="bi bi-graph-up-arrow me-1"></i>
                    Akumulasi penjualan
                </div>

            </div>
        </div>

    </div>

    <!-- PENJUALAN HARI INI -->
    <div class="section-heading mt-4">
        <h5>Aktivitas Hari Ini</h5>
        <small>Ringkasan penjualan hari ini</small>
    </div>

    <div class="row g-3">

        <div class="col-md-6">
            <div class="sales-card">

                <div class="sales-icon">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>

                <div class="sales-number">
                    <?= number_format($penjualan_hari_ini['jumlah'], 0, ',', '.') ?>
                </div>

                <div class="sales-caption">
                    Transaksi hari ini
                </div>

            </div>
        </div>

        <div class="col-md-6">
            <div class="sales-card">

                <div class="sales-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div class="sales-number">
                    <?= rupiah($penjualan_hari_ini['omzet']) ?>
                </div>

                <div class="sales-caption">
                    Omzet penjualan hari ini
                </div>

            </div>
        </div>

    </div>

    <!-- TRANSAKSI DAN AKSES CEPAT -->
    <div class="row">

        <div class="col-lg-8">

            <div class="table-card">

                <div class="section-heading">

                    <div>
                        <h5>Transaksi Terbaru</h5>
                        <small>5 transaksi terakhir yang tercatat</small>
                    </div>

                    <a href="pages/laporan.php" class="btn-view">
                        Lihat Laporan
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>

                </div>

                <div class="table-responsive">

                    <table class="table table-modern">

                        <thead>
                            <tr>
                                <th>ID TRANSAKSI</th>
                                <th>TANGGAL</th>
                                <th>PELANGGAN</th>
                                <th>TOTAL</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (mysqli_num_rows($transaksi_terbaru) > 0): ?>

                            <?php while ($t = mysqli_fetch_assoc($transaksi_terbaru)): ?>

                                <tr>

                                    <td>
                                        <span class="badge-id">
                                            #<?= $t['id_transaksi'] ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= date('d/m/Y', strtotime($t['tanggal'])) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($t['nama_pelanggan'] ?? 'Pelanggan') ?>
                                    </td>

                                    <td class="fw-bold">
                                        <?= rupiah($t['total_belanja']) ?>
                                    </td>

                                    <td>
                                        <span class="badge-success-soft">
                                            Selesai
                                        </span>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Belum ada transaksi.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <!-- AKSES CEPAT -->
        <div class="col-lg-4">

            <div class="quick-card">

                <div class="section-heading">
                    <h5>Akses Cepat</h5>
                </div>

                <a href="pages/transaksi.php" class="quick-link">
                    <div class="quick-icon">
                        <i class="bi bi-cart-plus"></i>
                    </div>
                    <span>Buat Transaksi Baru</span>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="pages/produk.php" class="quick-link">
                    <div class="quick-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <span>Kelola Produk</span>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="pages/pelanggan.php" class="quick-link">
                    <div class="quick-icon">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <span>Tambah Pelanggan</span>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

                <a href="pages/laporan.php" class="quick-link">
                    <div class="quick-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <span>Lihat Laporan</span>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>

            </div>

        </div>

    </div>

    <div class="footer">
        © <?= date('Y') ?> Toko Lefina. All Rights Reserved.
        <br>
        Retail Management System
    </div>

</main>

<script>
function toggleMenu() {
    document.getElementById("sidebar").classList.toggle("open");
    document.getElementById("overlay").classList.toggle("show");
}
</script>

</body>
</html>
