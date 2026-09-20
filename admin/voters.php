<?php
require_once __DIR__ . '/../config.php';
check_admin_auth();

$message = '';
$error = '';

// Handle Delete Single Voter
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmtDel = $pdo->prepare("DELETE FROM pemilih WHERE id = ?");
    $stmtDel->execute([$id]);
    $_SESSION['flash_message'] = "Data pemilih berhasil dihapus!";
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    redirect('admin/voters.php?page=' . $page);
}

// Handle Reset Single Voter Vote
if (isset($_GET['action']) && $_GET['action'] === 'reset_vote' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmtReset = $pdo->prepare("UPDATE pemilih SET status_memilih = 0, paslon_id = NULL, waktu_memilih = NULL WHERE id = ?");
    $stmtReset->execute([$id]);
    $_SESSION['flash_message'] = "Status suara pemilih berhasil direset!";
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    redirect('admin/voters.php?page=' . $page);
}

// Handle Batch Actions (Reset Selected or Delete Selected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'batch_action') {
    $voter_ids = $_POST['voter_ids'] ?? [];
    $batch_type = $_POST['batch_type'] ?? '';

    if (!empty($voter_ids) && is_array($voter_ids)) {
        $voter_ids = array_map('intval', $voter_ids);
        $placeholders = implode(',', array_fill(0, count($voter_ids), '?'));

        if ($batch_type === 'reset') {
            $stmt = $pdo->prepare("UPDATE pemilih SET status_memilih = 0, paslon_id = NULL, waktu_memilih = NULL WHERE id IN ($placeholders)");
            $stmt->execute($voter_ids);
            $_SESSION['flash_message'] = count($voter_ids) . " status suara pemilih terpilih berhasil direset!";
        } elseif ($batch_type === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM pemilih WHERE id IN ($placeholders)");
            $stmt->execute($voter_ids);
            $_SESSION['flash_message'] = count($voter_ids) . " data pemilih terpilih berhasil dihapus!";
        }
    } else {
        $_SESSION['flash_message'] = "Tidak ada pemilih yang dipilih!";
    }
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    redirect('admin/voters.php?page=' . $page);
}

// Handle Reset All Votes
if (isset($_POST['action']) && $_POST['action'] === 'reset_all_votes') {
    $pdo->exec("UPDATE pemilih SET status_memilih = 0, paslon_id = NULL, waktu_memilih = NULL");
    $_SESSION['flash_message'] = "Semua status suara pemilih berhasil direset!";
    redirect('admin/voters.php');
}

// Handle Clear All Voters
if (isset($_POST['action']) && $_POST['action'] === 'clear_all_voters') {
    $pdo->exec("DELETE FROM pemilih");
    $_SESSION['flash_message'] = "Seluruh data pemilih berhasil dibersihkan!";
    redirect('admin/voters.php');
}

// Handle Single Voter Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_voter') {
    $id    = (int)($_POST['id'] ?? 0);
    $nisn  = trim($_POST['nisn'] ?? '');
    $nama  = trim($_POST['nama'] ?? '');
    $kelas = trim($_POST['kelas'] ?? 'Siswa');
    if (empty($kelas)) $kelas = 'Siswa';

    if (empty($nisn) || empty($nama)) {
        $error = "NISN dan Nama Pemilih wajib diisi!";
    } else {
        // Check NISN uniqueness
        $stmtCheck = $pdo->prepare("SELECT id FROM pemilih WHERE nisn = ? AND id != ?");
        $stmtCheck->execute([$nisn, $id]);
        if ($stmtCheck->fetch()) {
            $error = "NISN '{$nisn}' telah digunakan oleh pemilih lain!";
        } else {
            if ($id > 0) {
                $stmtUpd = $pdo->prepare("UPDATE pemilih SET nisn = ?, nama = ?, kelas = ? WHERE id = ?");
                $stmtUpd->execute([$nisn, $nama, $kelas, $id]);
                $_SESSION['flash_message'] = "Data pemilih berhasil diperbarui!";
            } else {
                $stmtIns = $pdo->prepare("INSERT INTO pemilih (nisn, nama, kelas) VALUES (?, ?, ?)");
                $stmtIns->execute([$nisn, $nama, $kelas]);
                $_SESSION['flash_message'] = "Pemilih baru berhasil ditambahkan!";
            }
            redirect('admin/voters.php');
        }
    }
}

// Handle Batch Import CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_voters') {
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['import_file']['tmp_name'];
        $fileName = $_FILES['import_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, ['csv', 'txt'])) {
            $error = "Format file import harus berupa file CSV atau TXT!";
        } else {
            $handle = fopen($fileTmp, "r");
            if ($handle !== false) {
                $importedCount = 0;
                $skippedCount = 0;
                $row = 0;

                $stmtIns = $pdo->prepare("INSERT INTO pemilih (nisn, nama, kelas) VALUES (?, ?, ?)");
                $stmtCheck = $pdo->prepare("SELECT id FROM pemilih WHERE nisn = ?");

                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $row++;
                    if ($row === 1 && (strtolower(trim($data[0] ?? '')) === 'nisn' || strtolower(trim($data[0] ?? '')) === 'username')) {
                        continue;
                    }

                    if (count($data) === 1 && strpos($data[0], ';') !== false) {
                        $data = explode(';', $data[0]);
                    }

                    $nisn  = trim($data[0] ?? '');
                    $nama  = trim($data[1] ?? $nisn);
                    $kelas = trim($data[2] ?? 'Siswa');
                    if (empty($kelas)) $kelas = 'Siswa';

                    if (!empty($nisn)) {
                        $stmtCheck->execute([$nisn]);
                        if (!$stmtCheck->fetch()) {
                            $stmtIns->execute([$nisn, $nama, $kelas]);
                            $importedCount++;
                        } else {
                            $skippedCount++;
                        }
                    }
                }
                fclose($handle);

                $_SESSION['flash_message'] = "Import Berhasil! {$importedCount} data siswa ditambahkan. {$skippedCount} data dilewati (NISN ganda).";
                redirect('admin/voters.php');
            } else {
                $error = "Gagal membaca file import.";
            }
        }
    } else {
        $error = "Pilih file CSV untuk diimpor!";
    }
}

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Fetch edit target
$editVoter = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM pemilih WHERE id = ?");
    $stmtEdit->execute([(int)$_GET['id']]);
    $editVoter = $stmtEdit->fetch();
}

// Search, Filter, and Pagination
$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['filter_status'] ?? 'all';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$whereClause = " WHERE 1=1";
$params = [];

if (!empty($search)) {
    $whereClause .= " AND (p.nisn LIKE ? OR p.nama LIKE ? OR p.kelas LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($filter_status === 'voted') {
    $whereClause .= " AND p.status_memilih = 1";
} elseif ($filter_status === 'not_voted') {
    $whereClause .= " AND p.status_memilih = 0";
}

// Get Total Voters count for pagination
$countSql = "SELECT COUNT(*) FROM pemilih p" . $whereClause;
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$total_voters = (int)$stmtCount->fetchColumn();
$total_pages = max(1, ceil($total_voters / $per_page));

if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}

// Fetch Paginated Voters
$sql = "SELECT p.*, c.nomor_urut, c.nama_ketua FROM pemilih p LEFT JOIN paslon c ON p.paslon_id = c.id" . $whereClause . " ORDER BY p.id DESC LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;

$stmtVoters = $pdo->prepare($sql);
$stmtVoters->execute($params);
$voters = $stmtVoters->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pemilih - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-slate-200/80 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-tr from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-md shadow-purple-500/20">
                        <i class="fa-solid fa-address-card text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-slate-900 block leading-none">Manajemen Pemilih</span>
                        <span class="text-xs text-slate-500">Daftar Pemilih Tetap (DPT) & Import NISN</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= base_url('admin/print_cards.php') ?>" target="_blank" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-3.5 py-2 rounded-lg transition flex items-center space-x-1.5 shadow-sm">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak Kartu Pemilih (A4 PDF)</span>
                    </a>
                    <a href="<?= base_url('admin/dashboard.php') ?>" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-lg transition flex items-center space-x-1 border border-slate-200">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col lg:flex-row gap-8">

        <!-- Sidebar Navigation -->
        <aside class="w-full lg:w-64 space-y-2">
            <a href="<?= base_url('admin/dashboard.php') ?>" class="flex items-center space-x-3 px-4 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl font-medium transition">
                <i class="fa-solid fa-gauge w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= base_url('admin/candidates.php') ?>" class="flex items-center space-x-3 px-4 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl font-medium transition">
                <i class="fa-solid fa-users-gear w-5 text-center"></i>
                <span>Manajemen Paslon</span>
            </a>
            <a href="<?= base_url('admin/voters.php') ?>" class="flex items-center space-x-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium shadow-md shadow-indigo-600/20 transition">
                <i class="fa-solid fa-address-card w-5 text-center"></i>
                <span>Manajemen Pemilih</span>
            </a>
            <a href="<?= base_url('hash_generator.php') ?>" class="flex items-center space-x-3 px-4 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl font-medium transition">
                <i class="fa-solid fa-key w-5 text-center"></i>
                <span>Hash Generator</span>
            </a>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 space-y-8">

            <?php if (!empty($message)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <span><?= sanitize($message) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-5 py-4 rounded-xl text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span><?= sanitize($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Action Grid: Single Form & Batch Import -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Single Voter Form -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center justify-between">
                            <span class="flex items-center space-x-2">
                                <i class="fa-solid <?= $editVoter ? 'fa-pen text-amber-500' : 'fa-user-plus text-indigo-600' ?>"></i>
                                <span><?= $editVoter ? 'Edit Data Pemilih' : 'Tambah Pemilih Manual' ?></span>
                            </span>
                            <?php if ($editVoter): ?>
                                <a href="<?= base_url('admin/voters.php') ?>" class="text-xs text-slate-500 hover:text-slate-900 underline">Batal Edit</a>
                            <?php endif; ?>
                        </h3>

                        <form action="<?= base_url('admin/voters.php') ?>" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="save_voter">
                            <input type="hidden" name="id" value="<?= $editVoter['id'] ?? 0 ?>">

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">NISN (Username Login)</label>
                                <input type="text" name="nisn" required value="<?= sanitize($editVoter['nisn'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 text-sm outline-none transition" placeholder="Contoh: 0051234567">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Nama Lengkap Siswa</label>
                                <input type="text" name="nama" required value="<?= sanitize($editVoter['nama'] ?? '') ?>"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 text-sm outline-none transition" placeholder="Contoh: Ahmad Rizky">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Kelas / Peran</label>
                                <input type="text" name="kelas" value="<?= sanitize($editVoter['kelas'] ?? 'Siswa') ?>"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 text-sm outline-none transition" placeholder="Contoh: XII RPL 1 / Guru / Staf">
                            </div>

                            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition text-sm flex items-center justify-center space-x-2 shadow-sm">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span><?= $editVoter ? 'Simpan Perubahan' : 'Tambah Pemilih' ?></span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Batch Import CSV -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center space-x-2">
                            <i class="fa-solid fa-file-import text-purple-600"></i>
                            <span>Import Data Pemilih (CSV/Excel)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mb-4">Unggah file format `.csv` dengan kolom: <b>nisn, nama, kelas</b></p>

                        <form action="<?= base_url('admin/voters.php') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="import_voters">

                            <div>
                                <input type="file" name="import_file" accept=".csv, .txt" required
                                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-600 hover:file:bg-purple-100 transition cursor-pointer">
                            </div>

                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                                <p class="font-medium text-slate-700"><i class="fa-solid fa-circle-info mr-1 text-indigo-600"></i> Format contoh CSV:</p>
                                <code class="block text-slate-800 font-mono">nisn,nama,kelas<br>0051112233,Budi Santoso,XII RPL 1<br>0054445566,Siti Rahma,Guru</code>
                            </div>

                            <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl transition text-sm flex items-center justify-center space-x-2 shadow-sm">
                                <i class="fa-solid fa-upload"></i>
                                <span>Proses Import Batch</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table & Filters -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-4">

                <div class="flex flex-col md:flex-row items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Daftar Pemilih Tetap (DPT)</h3>
                        <p class="text-xs text-slate-500">Total terdaftar: <?= $total_voters ?> siswa (Halaman <?= $page ?> dari <?= $total_pages ?>)</p>
                    </div>

                    <!-- Search & Filter Controls -->
                    <form action="<?= base_url('admin/voters.php') ?>" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <div class="relative flex-1 md:flex-initial">
                            <input type="text" name="search" value="<?= sanitize($search) ?>" placeholder="Cari NISN / Nama..."
                                class="w-full md:w-48 pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-indigo-600">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        </div>

                        <select name="filter_status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-xs text-slate-800 rounded-xl px-3 py-2 focus:outline-none">
                            <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="voted" <?= $filter_status === 'voted' ? 'selected' : '' ?>>Sudah Memilih</option>
                            <option value="not_voted" <?= $filter_status === 'not_voted' ? 'selected' : '' ?>>Belum Memilih</option>
                        </select>

                        <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-xs text-slate-700 rounded-xl transition border border-slate-200">
                            Filter
                        </button>
                    </form>
                </div>

                <!-- Batch Form for Checkbox Selections & Utility Buttons -->
                <form id="batchForm" action="<?= base_url('admin/voters.php?page=' . $page) ?>" method="POST">
                    <input type="hidden" name="action" value="batch_action">
                    <input type="hidden" name="batch_type" id="batchType" value="">

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-slate-500 font-medium">Aksi Centang:</span>
                            <button type="button" onclick="submitBatch('reset')" class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg transition font-medium flex items-center space-x-1">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Reset Status Pemilih Terpilih</span>
                            </button>
                            <button type="button" onclick="submitBatch('delete')" class="text-xs bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 py-1.5 rounded-lg transition font-medium flex items-center space-x-1">
                                <i class="fa-solid fa-trash"></i>
                                <span>Hapus Pemilih Terpilih</span>
                            </button>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="confirmResetAll()" class="text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-lg transition font-medium">
                                <i class="fa-solid fa-arrows-rotate mr-1"></i> Reset SEMUA Pemilih
                            </button>
                            <button type="button" onclick="confirmClearAll()" class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-3 py-1.5 rounded-lg transition font-medium">
                                <i class="fa-solid fa-trash-can mr-1"></i> Hapus SEMUA Pemilih
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-3 w-8 text-center">
                                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">NISN (Username)</th>
                                    <th class="px-4 py-3">Nama Siswa</th>
                                    <th class="px-4 py-3">Kelas / Peran</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3">Pilihan / Waktu</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <?php if (empty($voters)): ?>
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                            Tidak ada data pemilih ditemukan.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = $offset + 1; foreach ($voters as $v): ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-3 py-3 text-center">
                                                <input type="checkbox" name="voter_ids[]" value="<?= $v['id'] ?>" class="voter-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            </td>
                                            <td class="px-4 py-3 font-medium text-slate-400"><?= $no++ ?></td>
                                            <td class="px-4 py-3 font-mono text-indigo-600 font-semibold"><?= sanitize($v['nisn']) ?></td>
                                            <td class="px-4 py-3 font-medium text-slate-900"><?= sanitize($v['nama']) ?></td>
                                            <td class="px-4 py-3 font-medium text-slate-600">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-2xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                    <?= sanitize($v['kelas'] ?? 'Siswa') ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <?php if ($v['status_memilih'] == 1): ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <i class="fa-solid fa-check mr-1 text-2xs"></i> Sudah
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                                        <i class="fa-solid fa-clock mr-1 text-2xs"></i> Belum
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-slate-500">
                                                <?php if ($v['status_memilih'] == 1): ?>
                                                    <span class="text-slate-900 font-medium">Paslon 0<?= sanitize($v['nomor_urut']) ?></span>
                                                    <span class="block text-2xs text-slate-400"><?= sanitize($v['waktu_memilih']) ?></span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-right space-x-2">
                                                <?php if ($v['status_memilih'] == 1): ?>
                                                    <a href="<?= base_url('admin/voters.php?action=reset_vote&id=' . $v['id'] . '&page=' . $page) ?>" onclick="return confirm('Reset status suara siswa ini?')" title="Reset Suara Siswa Ini" class="text-indigo-600 hover:text-indigo-800 transition px-1.5 py-1 bg-indigo-50 rounded border border-indigo-200">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('admin/voters.php?action=edit&id=' . $v['id']) ?>" title="Edit Data Siswa" class="text-amber-600 hover:text-amber-700 transition">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="<?= base_url('admin/voters.php?action=delete&id=' . $v['id'] . '&page=' . $page) ?>" onclick="return confirm('Hapus pemilih ini?')" title="Hapus Siswa" class="text-red-600 hover:text-red-700 transition">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>

                <!-- Hidden Reset/Clear All Forms -->
                <form id="resetAllForm" action="<?= base_url('admin/voters.php') ?>" method="POST" class="hidden">
                    <input type="hidden" name="action" value="reset_all_votes">
                </form>

                <form id="clearAllForm" action="<?= base_url('admin/voters.php') ?>" method="POST" class="hidden">
                    <input type="hidden" name="action" value="clear_all_voters">
                </form>

                <!-- Pagination Bar -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 text-xs">
                        <p class="text-slate-500">
                            Menampilkan <span class="font-semibold text-slate-800"><?= $offset + 1 ?></span> - <span class="font-semibold text-slate-800"><?= min($offset + $per_page, $total_voters) ?></span> dari <span class="font-semibold text-slate-800"><?= $total_voters ?></span> pemilih
                        </p>

                        <div class="flex items-center space-x-1">
                            <!-- Prev Page -->
                            <?php if ($page > 1): ?>
                                <a href="<?= base_url('admin/voters.php?page=' . ($page - 1) . '&search=' . urlencode($search) . '&filter_status=' . urlencode($filter_status)) ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition border border-slate-200 font-medium">
                                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                                </a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 bg-slate-50 text-slate-300 rounded-lg border border-slate-200 cursor-not-allowed">
                                    <i class="fa-solid fa-chevron-left mr-1"></i> Prev
                                </span>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                <?php if ($p == $page): ?>
                                    <span class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg font-bold shadow-sm"><?= $p ?></span>
                                <?php elseif ($p == 1 || $p == $total_pages || abs($p - $page) <= 2): ?>
                                    <a href="<?= base_url('admin/voters.php?page=' . $p . '&search=' . urlencode($search) . '&filter_status=' . urlencode($filter_status)) ?>" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-lg transition border border-slate-200 font-medium"><?= $p ?></a>
                                <?php elseif (abs($p - $page) == 3): ?>
                                    <span class="px-2 py-1.5 text-slate-400">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <?php if ($page < $total_pages): ?>
                                <a href="<?= base_url('admin/voters.php?page=' . ($page + 1) . '&search=' . urlencode($search) . '&filter_status=' . urlencode($filter_status)) ?>" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition border border-slate-200 font-medium">
                                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                                </a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 bg-slate-50 text-slate-300 rounded-lg border border-slate-200 cursor-not-allowed">
                                    Next <i class="fa-solid fa-chevron-right ml-1"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
        function toggleSelectAll(source) {
            const checkboxes = document.querySelectorAll('.voter-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
        }

        function submitBatch(actionType) {
            const checkedCount = document.querySelectorAll('.voter-checkbox:checked').length;
            if (checkedCount === 0) {
                alert('Pilih setidaknya satu pemilih dengan mencentang kotak.');
                return;
            }

            const message = actionType === 'reset'
                ? `Apakah Anda yakin ingin MERESET status suara ${checkedCount} pemilih terpilih?`
                : `Apakah Anda yakin ingin MENGHAPUS ${checkedCount} data pemilih terpilih?`;

            if (confirm(message)) {
                document.getElementById('batchType').value = actionType;
                document.getElementById('batchForm').submit();
            }
        }

        function confirmResetAll() {
            if (confirm('Apakah Anda yakin ingin MERESET SEMUA SUARA PEMILIH? Pemilih yang sudah memilih akan bisa memilih kembali.')) {
                document.getElementById('resetAllForm').submit();
            }
        }

        function confirmClearAll() {
            if (confirm('PERINGATAN DANGER: Seluruh data pemilih akan DIHAPUS PERMANEN. Lanjutkan?')) {
                document.getElementById('clearAllForm').submit();
            }
        }
    </script>
</body>
</html>
