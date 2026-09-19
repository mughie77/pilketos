<?php
require_once __DIR__ . '/../config.php';

$error = '';

if (isset($_SESSION['voter_logged_in']) && $_SESSION['voter_logged_in'] === true) {
    redirect('/voter/vote.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');

    if (empty($nisn)) {
        $error = 'Silakan masukkan NISN Anda!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pemilih WHERE nisn = ?");
        $stmt->execute([$nisn]);
        $voter = $stmt->fetch();

        if ($voter) {
            if ($voter['status_memilih'] == 1) {
                $error = 'Hak pilih dengan NISN ini sudah digunakan!';
            } else {
                $_SESSION['voter_logged_in'] = true;
                $_SESSION['voter_id'] = $voter['id'];
                $_SESSION['voter_nisn'] = $voter['nisn'];
                $_SESSION['voter_nama'] = $voter['nama'];

                redirect('/voter/vote.php');
            }
        } else {
            $error = 'NISN tidak terdaftar dalam DPT. Silakan hubungi panitia OSIS!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemilih - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Animated background accents -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-600/30 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-600/30 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-2xl shadow-lg shadow-purple-500/30 mb-4 transform hover:scale-105 transition duration-300">
                <i class="fa-solid fa-vote-yea text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide">Bilik Suara Digital OSIS</h1>
            <p class="text-slate-400 text-sm mt-1">Masukkan NISN Anda untuk memulai pemilihan</p>
        </div>

        <!-- Form Card -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-2xl p-8 shadow-2xl">
            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl text-sm flex items-center space-x-2 animate-bounce">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= sanitize($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">NISN Siswa</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-id-card"></i>
                        </span>
                        <input type="text" name="nisn" required autofocus autocomplete="off"
                            class="w-full pl-10 pr-4 py-3.5 bg-slate-900/80 border border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl text-white font-mono placeholder-slate-500 text-base transition outline-none"
                            placeholder="Contoh: 0051234567">
                    </div>
                    <p class="text-xs text-slate-400 mt-2"><i class="fa-solid fa-info-circle mr-1"></i> Tidak memerlukan password. Gunakan NISN resmi sekolah.</p>
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl shadow-lg shadow-purple-500/25 transition duration-200 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center space-x-2">
                    <span>Masuk ke Bilik Suara</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-6 text-center border-t border-slate-700/60 pt-6">
                <a href="../index.php" class="inline-flex items-center text-xs text-slate-400 hover:text-indigo-400 transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Halaman Utama / Live Count
                </a>
            </div>
        </div>
    </div>
</body>
</html>
