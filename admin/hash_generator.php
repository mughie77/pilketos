<?php
require_once __DIR__ . '/../config.php';
check_admin_auth();

$inputText = $_POST['input_text'] ?? '';
$verifyHash = $_POST['verify_hash'] ?? '';
$verifyResult = null;

$hashes = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($inputText)) {
    $hashes = [
        'PHP password_hash() (Bcrypt / Default)' => password_hash($inputText, PASSWORD_DEFAULT),
        'SHA-256' => hash('sha256', $inputText),
        'SHA-512' => hash('sha512', $inputText),
        'MD5'     => md5($inputText),
    ];

    if (!empty($verifyHash)) {
        if (password_verify($inputText, $verifyHash)) {
            $verifyResult = [
                'status' => 'match',
                'message' => 'Teks cocok dengan hash (menggunakan password_verify)!'
            ];
        } elseif (hash('sha256', $inputText) === strtolower($verifyHash) || hash('sha512', $inputText) === strtolower($verifyHash) || md5($inputText) === strtolower($verifyHash)) {
            $verifyResult = [
                'status' => 'match',
                'message' => 'Teks cocok dengan hash (menggunakan Perbandingan Hash Direct)!'
            ];
        } else {
            $verifyResult = [
                'status' => 'mismatch',
                'message' => 'Teks TIDAK COCOK dengan hash yang dimasukkan.'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash Generator - Admin Panel</title>
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
                    <div class="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-key text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-slate-900 block leading-none">Hash Generator Utility</span>
                        <span class="text-xs text-slate-500">Generator & Verifikator Password Hash</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-lg transition flex items-center space-x-1 border border-slate-200">
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
            <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl font-medium transition">
                <i class="fa-solid fa-gauge w-5 text-center"></i>
                <span>Dashboard</span>
            </a>
            <a href="candidates.php" class="flex items-center space-x-3 px-4 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl font-medium transition">
                <i class="fa-solid fa-users-gear w-5 text-center"></i>
                <span>Manajemen Paslon</span>
            </a>
            <a href="voters.php" class="flex items-center space-x-3 px-4 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl font-medium transition">
                <i class="fa-solid fa-address-card w-5 text-center"></i>
                <span>Manajemen Pemilih</span>
            </a>
            <a href="hash_generator.php" class="flex items-center space-x-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium shadow-md shadow-indigo-600/20 transition">
                <i class="fa-solid fa-key w-5 text-center"></i>
                <span>Hash Generator</span>
            </a>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 space-y-8">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid fa-shield-halved text-indigo-600"></i>
                        <span>Generate & Verifikasi Hash</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Gunakan alat ini untuk membuat hash password baru (misalnya untuk memperbarui password admin di database) atau memverifikasi hash yang ada.</p>
                </div>

                <form action="hash_generator.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Teks / Password Utama</label>
                        <input type="text" name="input_text" required value="<?= sanitize($inputText) ?>"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 text-sm outline-none transition" placeholder="Ketik teks atau password yang ingin di-hash...">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">Hash Pembanding (Opsional untuk Uji Verifikasi)</label>
                        <input type="text" name="verify_hash" value="<?= sanitize($verifyHash) ?>"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 font-mono text-xs outline-none transition" placeholder="Tempel hash lama di sini untuk memverifikasi kesesuaian...">
                    </div>

                    <button type="submit" class="py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition text-sm flex items-center space-x-2">
                        <i class="fa-solid fa-gears"></i>
                        <span>Generate Hash</span>
                    </button>
                </form>

                <?php if ($verifyResult): ?>
                    <div class="p-4 rounded-xl border text-xs font-medium flex items-center space-x-2 <?= $verifyResult['status'] === 'match' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-700' ?>">
                        <i class="fa-solid <?= $verifyResult['status'] === 'match' ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-red-600' ?> text-lg"></i>
                        <span><?= sanitize($verifyResult['message']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($hashes)): ?>
                    <div class="pt-4 border-t border-slate-100 space-y-4">
                        <h3 class="text-base font-bold text-slate-900">Hasil Output Hash:</h3>

                        <div class="space-y-3">
                            <?php foreach ($hashes as $algo => $hashVal): ?>
                                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider"><?= $algo ?></span>
                                        <button onclick="navigator.clipboard.writeText('<?= addslashes($hashVal) ?>'); alert('Hash berhasil disalin!');" class="text-2xs bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 px-2 py-1 rounded transition">
                                            <i class="fa-solid fa-copy"></i> Salin
                                        </button>
                                    </div>
                                    <p class="font-mono text-xs text-slate-800 break-all select-all font-semibold"><?= sanitize($hashVal) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</body>
</html>
