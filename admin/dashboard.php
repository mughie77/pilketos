<?php
require_once __DIR__ . '/../config.php';
check_admin_auth();

// Fetch summary stats
$stmtPaslon = $pdo->query("SELECT COUNT(*) FROM paslon");
$totalPaslon = $stmtPaslon->fetchColumn();

$stmtPemilih = $pdo->query("SELECT COUNT(*) FROM pemilih");
$totalPemilih = $stmtPemilih->fetchColumn();

$stmtSudah = $pdo->query("SELECT COUNT(*) FROM pemilih WHERE status_memilih = 1");
$totalSudah = $stmtSudah->fetchColumn();

$totalBelum = $totalPemilih - $totalSudah;
$persentase = $totalPemilih > 0 ? round(($totalSudah / $totalPemilih) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="bg-slate-800/80 backdrop-blur-md border-b border-slate-700/60 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <i class="fa-solid fa-chart-pie text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-white block leading-none">E-Voting Admin</span>
                        <span class="text-xs text-slate-400">Panel Kelola Pemilihan OSIS</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:flex items-center space-x-2 text-sm text-slate-300 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-700/50">
                        <i class="fa-solid fa-user-circle text-indigo-400"></i>
                        <span><?= sanitize($_SESSION['admin_name']) ?></span>
                    </div>
                    <a href="../index.php" target="_blank" class="text-xs bg-slate-700/50 hover:bg-slate-700 text-slate-200 px-3 py-2 rounded-lg transition flex items-center space-x-1.5 border border-slate-600/50">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Lihat Live Count</span>
                    </a>
                    <a href="logout.php" class="text-xs bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 px-3 py-2 rounded-lg transition flex items-center space-x-1">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-8">

        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 space-y-2">
            <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium shadow-lg shadow-indigo-600/20 transition">
                <i class="fa-solid fa-gauge w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="candidates.php" class="flex items-center space-x-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl font-medium transition">
                <i class="fa-solid fa-users-gear w-5 text-center"></i>
                <span>Manajemen Paslon</span>
            </a>
            <a href="voters.php" class="flex items-center space-x-3 px-4 py-3 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl font-medium transition">
                <i class="fa-solid fa-address-card w-5 text-center"></i>
                <span>Manajemen Pemilih</span>
            </a>
        </aside>

        <!-- Main Dashboard Content -->
        <main class="flex-1 space-y-6">
            <div class="bg-gradient-to-r from-indigo-900/40 via-purple-900/20 to-slate-800/40 border border-indigo-500/20 rounded-2xl p-6 relative overflow-hidden">
                <h2 class="text-2xl font-bold text-white mb-2">Selamat Datang, <?= sanitize($_SESSION['admin_name']) ?>!</h2>
                <p class="text-slate-300 text-sm max-w-2xl">Sistem e-voting siap digunakan. Gunakan panel ini untuk mengelola Pasangan Calon (Paslon) Ketua & Wakil OSIS serta Data Pemilih Siswa.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-5 shadow-sm hover:border-slate-600 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Paslon</span>
                        <div class="w-10 h-10 bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-white"><?= $totalPaslon ?></span>
                        <span class="text-xs text-slate-400 ml-1">Kandidat</span>
                    </div>
                </div>

                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-5 shadow-sm hover:border-slate-600 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total DPT Pemilih</span>
                        <div class="w-10 h-10 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-white"><?= $totalPemilih ?></span>
                        <span class="text-xs text-slate-400 ml-1">Siswa Terdaftar</span>
                    </div>
                </div>

                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-5 shadow-sm hover:border-slate-600 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sudah Memilih</span>
                        <div class="w-10 h-10 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-vote-yea"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-emerald-400"><?= $totalSudah ?></span>
                        <span class="text-xs text-slate-400 ml-1">(<?= $persentase ?>%)</span>
                    </div>
                </div>

                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-5 shadow-sm hover:border-slate-600 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Belum Memilih</span>
                        <div class="w-10 h-10 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-amber-400"><?= $totalBelum ?></span>
                        <span class="text-xs text-slate-400 ml-1">Siswa</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="candidates.php" class="bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 rounded-2xl p-6 transition group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center text-indigo-400 text-xl group-hover:scale-110 transition">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white group-hover:text-indigo-400 transition">Kelola Pasangan Calon</h3>
                            <p class="text-slate-400 text-xs mt-1">Tambah paslon, upload foto, isi visi dan misi kandidat ketua OSIS.</p>
                        </div>
                    </div>
                </a>

                <a href="voters.php" class="bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 rounded-2xl p-6 transition group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center text-purple-400 text-xl group-hover:scale-110 transition">
                            <i class="fa-solid fa-file-csv"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white group-hover:text-purple-400 transition">Kelola Data Pemilih & Import</h3>
                            <p class="text-slate-400 text-xs mt-1">Tambah siswa NISN secara manual atau upload batch via file CSV/Excel.</p>
                        </div>
                    </div>
                </a>
            </div>
        </main>
    </div>
</body>
</html>
