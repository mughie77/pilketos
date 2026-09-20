<?php
require_once __DIR__ . '/config.php';

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
    <title>Hash Generator - Pemilihan OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between relative selection:bg-indigo-500 selection:text-white">

    <!-- Subtle Background Accent Shapes -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-64 bg-gradient-to-b from-indigo-50/80 via-purple-50/40 to-transparent pointer-events-none"></div>

    <!-- Header Navigation -->
    <header class="bg-white/80 backdrop-blur-xl border-b border-slate-200/80 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-key text-xl text-white"></i>
                </div>
                <div>
                    <span class="font-extrabold text-lg text-slate-900 tracking-tight">Hash Generator Utility</span>
                    <p class="text-xs text-slate-500 font-medium">Generator & Verifikator Hash Publik</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="<?= base_url('index.php') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition border border-slate-200 flex items-center space-x-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Live Count</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 relative z-10">
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-shield-halved text-indigo-600"></i>
                    <span>Generate & Verifikasi Hash Online</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">Alat utilitas terbuka untuk membuat hash teks/password (password_hash Bcrypt, SHA-256, SHA-512, MD5) serta memverifikasi kesesuaian hash.</p>
            </div>

            <form action="<?= base_url('hash_generator.php') ?>" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Teks / Password Utama *</label>
                    <input type="text" name="input_text" required value="<?= sanitize($inputText) ?>" autofocus
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 text-sm outline-none transition" placeholder="Ketik teks atau password yang ingin di-hash...">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">Hash Pembanding (Opsional untuk Uji Verifikasi)</label>
                    <input type="text" name="verify_hash" value="<?= sanitize($verifyHash) ?>"
                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 font-mono text-xs outline-none transition" placeholder="Tempel hash lama di sini untuk memverifikasi kesesuaian...">
                </div>

                <button type="submit" class="py-3.5 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-gears"></i>
                    <span>Generate & Verifikasi Hash</span>
                </button>
            </form>

            <?php if ($verifyResult): ?>
                <div class="p-4 rounded-xl border text-xs font-medium flex items-center space-x-2 <?= $verifyResult['status'] === 'match' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-700' ?>">
                    <i class="fa-solid <?= $verifyResult['status'] === 'match' ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-red-600' ?> text-lg"></i>
                    <span><?= sanitize($verifyResult['message']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($hashes)): ?>
                <div class="pt-6 border-t border-slate-100 space-y-4">
                    <h2 class="text-base font-bold text-slate-900">Hasil Output Hash:</h2>

                    <div class="space-y-3">
                        <?php foreach ($hashes as $algo => $hashVal): ?>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider"><?= $algo ?></span>
                                    <button onclick="navigator.clipboard.writeText('<?= addslashes($hashVal) ?>'); alert('Hash berhasil disalin!');" class="text-2xs bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-lg transition font-medium">
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

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
        Hash Generator Utility &bull; Pemilihan Ketua OSIS Webbased
    </footer>
</body>
</html>
