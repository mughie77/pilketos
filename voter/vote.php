<?php
require_once __DIR__ . '/../config.php';
check_voter_auth();

$voterId = $_SESSION['voter_id'];

// Check current voter status from DB to prevent double voting
$stmtCheck = $pdo->prepare("SELECT * FROM pemilih WHERE id = ?");
$stmtCheck->execute([$voterId]);
$voter = $stmtCheck->fetch();

if (!$voter || $voter['status_memilih'] == 1) {
    // Already voted or invalid, logout
    unset($_SESSION['voter_logged_in']);
    unset($_SESSION['voter_id']);
    redirect('/voter/login.php');
}

$error = '';
$success = false;

// Process Vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paslon_id'])) {
    $paslonId = (int)$_POST['paslon_id'];

    // Verify paslon exists
    $stmtPaslon = $pdo->prepare("SELECT id, nomor_urut, nama_ketua FROM paslon WHERE id = ?");
    $stmtPaslon->execute([$paslonId]);
    $paslon = $stmtPaslon->fetch();

    if ($paslon) {
        $now = date('Y-m-d H:i:s');
        $stmtVote = $pdo->prepare("UPDATE pemilih SET status_memilih = 1, paslon_id = ?, waktu_memilih = ? WHERE id = ? AND status_memilih = 0");
        $stmtVote->execute([$paslonId, $now, $voterId]);

        if ($stmtVote->rowCount() > 0) {
            $success = true;
            // Clear session after successful vote
            unset($_SESSION['voter_logged_in']);
            unset($_SESSION['voter_id']);
            unset($_SESSION['voter_nisn']);
            unset($_SESSION['voter_nama']);
        } else {
            $error = 'Gagal menyimpan pilihan. Anda mungkin sudah menggunakan hak pilih.';
        }
    } else {
        $error = 'Pasangan calon yang Anda pilih tidak valid!';
    }
}

// Fetch all Candidates
$candidates = $pdo->query("SELECT * FROM paslon ORDER BY nomor_urut ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilik Suara - E-Voting OSIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between relative">

    <?php if ($success): ?>
        <!-- Success Modal Overlay -->
        <div class="fixed inset-0 z-50 bg-slate-950/90 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-slate-800 border border-emerald-500/30 rounded-3xl p-8 max-w-md w-full text-center space-y-6 shadow-2xl animate-fade-in">
                <div class="w-20 h-20 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center mx-auto border-2 border-emerald-500/40 text-4xl">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-white">Suara Berhasil Disimpan!</h2>
                    <p class="text-slate-300 text-sm mt-2">Terima kasih telah berpartisipasi dalam Pemilihan Ketua OSIS. Pilihan Anda sangat berarti untuk kemajuan sekolah.</p>
                </div>
                <div class="pt-4">
                    <a href="../index.php" class="inline-flex items-center justify-center w-full py-3.5 px-6 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg transition">
                        <span>Lihat Hasil Perhitungan Suara</span>
                        <i class="fa-solid fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Top Bar -->
    <header class="bg-slate-800/80 backdrop-blur-md border-b border-slate-700/60 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-600/30">
                    <i class="fa-solid fa-box-archive text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">Bilik Suara Pemilihan OSIS</h1>
                    <p class="text-xs text-slate-400">Pilih pasangan calon sesuai hati nurani Anda</p>
                </div>
            </div>

            <div class="flex items-center space-x-3 bg-slate-900/60 border border-slate-700/60 px-3.5 py-1.5 rounded-xl">
                <i class="fa-solid fa-user-check text-emerald-400 text-sm"></i>
                <div class="text-left">
                    <span class="block text-xs font-semibold text-white leading-none"><?= sanitize($voter['nama']) ?></span>
                    <span class="text-2xs font-mono text-slate-400">NISN: <?= sanitize($voter['nisn']) ?></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1">

        <?php if (!empty($error)): ?>
            <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-400 px-5 py-4 rounded-2xl text-sm flex items-center space-x-3">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                <span><?= sanitize($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="text-center mb-10">
            <span class="inline-block px-3.5 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full text-xs font-semibold uppercase tracking-wider mb-2">
                Surat Suara Digital
            </span>
            <h2 class="text-3xl font-extrabold text-white">Daftar Pasangan Calon Ketua & Wakil OSIS</h2>
            <p class="text-slate-400 text-sm max-w-xl mx-auto mt-2">Klik tombol "Gunakan Hak Pilih" pada pasangan calon pilihan Anda. Anda hanya dapat memilih satu kali.</p>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="bg-slate-800/60 border border-slate-700/60 rounded-3xl p-12 text-center text-slate-400 max-w-md mx-auto">
                <i class="fa-solid fa-user-slash text-5xl mb-4 text-slate-500"></i>
                <p>Belum ada Pasangan Calon yang terdaftar.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($candidates as $c): ?>
                    <div class="bg-slate-800/90 border border-slate-700/70 hover:border-indigo-500/50 rounded-3xl overflow-hidden flex flex-col justify-between shadow-xl hover:shadow-2xl hover:shadow-indigo-500/10 transition duration-300 transform hover:-translate-y-1 group">

                        <div>
                            <!-- Number Header -->
                            <div class="bg-gradient-to-r from-indigo-900/60 to-purple-900/40 p-4 border-b border-slate-700/60 flex items-center justify-between">
                                <span class="text-xs font-semibold text-indigo-300 uppercase tracking-widest">Kandidat</span>
                                <span class="w-10 h-10 bg-indigo-600 text-white font-extrabold text-xl rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-600/30">
                                    0<?= $c['nomor_urut'] ?>
                                </span>
                            </div>

                            <div class="p-6 space-y-5">
                                <!-- Candidate Image -->
                                <div class="w-full h-64 bg-slate-900 rounded-2xl overflow-hidden border border-slate-700 relative group-hover:border-indigo-500/50 transition">
                                    <img src="../<?= sanitize($c['foto']) ?>" alt="Foto <?= sanitize($c['nama_ketua']) ?>" class="w-full h-full object-cover object-top group-hover:scale-105 transition duration-500">
                                </div>

                                <!-- Candidate Names -->
                                <div>
                                    <h3 class="text-xl font-bold text-white group-hover:text-indigo-400 transition"><?= sanitize($c['nama_ketua']) ?></h3>
                                    <?php if (!empty($c['nama_wakil'])): ?>
                                        <p class="text-xs font-semibold text-slate-400 mt-0.5">Wakil: <span class="text-slate-200"><?= sanitize($c['nama_wakil']) ?></span></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Visi & Misi Toggle Accordion/Preview -->
                                <div class="space-y-3 pt-2">
                                    <?php if (!empty($c['visi'])): ?>
                                        <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-700/50">
                                            <span class="text-2xs font-bold text-indigo-400 uppercase tracking-wider block mb-1"><i class="fa-solid fa-eye mr-1"></i> Visi:</span>
                                            <p class="text-xs text-slate-300 line-clamp-3 leading-relaxed"><?= sanitize($c['visi']) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($c['misi'])): ?>
                                        <div class="bg-slate-900/60 p-3.5 rounded-xl border border-slate-700/50">
                                            <span class="text-2xs font-bold text-purple-400 uppercase tracking-wider block mb-1"><i class="fa-solid fa-bullseye mr-1"></i> Misi:</span>
                                            <p class="text-xs text-slate-300 line-clamp-3 leading-relaxed"><?= sanitize($c['misi']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Vote Action Button -->
                        <div class="p-6 pt-0">
                            <button onclick="confirmVote('<?= $c['id'] ?>', '<?= addslashes(sanitize($c['nama_ketua'])) ?>', '0<?= $c['nomor_urut'] ?>')"
                                class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition duration-200 flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-check-circle"></i>
                                <span>Pilih Paslon 0<?= $c['nomor_urut'] ?></span>
                            </button>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="hidden fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-3xl p-6 sm:p-8 max-w-md w-full space-y-6 shadow-2xl">
            <div class="text-center">
                <div class="w-16 h-16 bg-amber-500/20 text-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-amber-500/30 text-2xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Konfirmasi Pilihan Anda</h3>
                <p class="text-slate-300 text-sm mt-2">
                    Apakah Anda yakin ingin memilih pasangan calon nomor <strong id="modalNomor" class="text-indigo-400 font-bold"></strong> (<span id="modalNama" class="text-white font-semibold"></span>)?
                </p>
                <p class="text-xs text-amber-400/90 mt-3 bg-amber-500/10 p-2.5 rounded-xl border border-amber-500/20">
                    <i class="fa-solid fa-lock mr-1"></i> Pilihan Anda bersifat final dan tidak dapat diubah kembali.
                </p>
            </div>

            <form action="" method="POST" class="flex space-x-3">
                <input type="hidden" name="paslon_id" id="modalPaslonId" value="">
                <button type="button" onclick="closeModal()" class="w-1/2 py-3 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold rounded-xl text-sm transition">
                    Batal
                </button>
                <button type="submit" class="w-1/2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/30 transition">
                    Ya, Pilihan Saya
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-400">
        E-Voting Pemilihan Ketua OSIS &bull; Berkelanjutan, Jujur & Transparan
    </footer>

    <script>
        function confirmVote(id, nama, nomor) {
            document.getElementById('modalPaslonId').value = id;
            document.getElementById('modalNama').innerText = nama;
            document.getElementById('modalNomor').innerText = nomor;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }
    </script>
</body>
</html>
