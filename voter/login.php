<?php
require_once __DIR__ . '/../config.php';

$error = '';

if (isset($_SESSION['voter_logged_in']) && $_SESSION['voter_logged_in'] === true) {
    redirect('voter/vote.php');
}

// Function to perform voter login
function perform_voter_login($nisn, $pdo, &$error) {
    $stmt = $pdo->prepare("SELECT * FROM pemilih WHERE nisn = ?");
    $stmt->execute([$nisn]);
    $voter = $stmt->fetch();

    if ($voter) {
        if ($voter['status_memilih'] == 1) {
            $error = 'Hak pilih dengan NISN ' . htmlspecialchars($nisn) . ' sudah digunakan!';
            return false;
        } else {
            $_SESSION['voter_logged_in'] = true;
            $_SESSION['voter_id'] = $voter['id'];
            $_SESSION['voter_nisn'] = $voter['nisn'];
            $_SESSION['voter_nama'] = $voter['nama'];
            redirect('voter/vote.php');
        }
    } else {
        $error = 'NISN (' . htmlspecialchars($nisn) . ') tidak terdaftar dalam DPT. Hubungi panitia!';
        return false;
    }
}

// Handle GET auto-login via QR code scan or URL query param
if (isset($_GET['nisn']) && !empty(trim($_GET['nisn']))) {
    perform_voter_login(trim($_GET['nisn']), $pdo, $error);
}

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');
    if (empty($nisn)) {
        $error = 'Silakan masukkan NISN Anda!';
    } else {
        perform_voter_login($nisn, $pdo, $error);
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
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Light ambient background glow -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-100 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-100 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Title -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl shadow-lg shadow-indigo-500/20 mb-3 transform hover:scale-105 transition duration-300">
                <i class="fa-solid fa-vote-yea text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-wide">Bilik Suara Digital OSIS</h1>
            <p class="text-slate-500 text-sm mt-1">Scan Kartu Pemilih atau Masukkan NISN Anda</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/50">
            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-base flex-shrink-0"></i>
                    <span><?= sanitize($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- QR Code Camera Scanner Toggle Button -->
            <div class="mb-6">
                <button type="button" id="toggleScannerBtn" onclick="toggleQrScanner()"
                    class="w-full py-3 px-4 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-bold rounded-xl transition duration-200 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-qrcode text-lg text-indigo-600"></i>
                    <span id="scannerBtnText">Scan QR Code dengan Kamera</span>
                </button>

                <!-- Camera Container -->
                <div id="qrScannerContainer" class="hidden mt-4 bg-slate-900 rounded-2xl p-3 overflow-hidden shadow-inner border border-slate-800">
                    <div id="qr-reader" class="w-full rounded-xl overflow-hidden text-white text-xs"></div>
                    <p class="text-3xs text-center text-slate-400 mt-2">Arahkan kamera ke QR Code pada Kartu Pemilih</p>
                </div>
            </div>

            <div class="relative flex py-2 items-center mb-4">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="flex-shrink mx-4 text-slate-400 text-xs font-semibold uppercase">atau Ketik NISN</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>

            <form id="voterLoginForm" action="" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">NISN Siswa</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-id-card"></i>
                        </span>
                        <input type="text" id="nisnInput" name="nisn" required autofocus autocomplete="off"
                            class="w-full pl-10 pr-10 py-3.5 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white focus:ring-2 focus:ring-indigo-600/10 rounded-xl text-slate-900 font-mono placeholder-slate-400 text-base transition outline-none"
                            placeholder="Contoh: 0051234567">
                        <span id="validSpinner" class="hidden absolute inset-y-0 right-0 flex items-center pr-3.5 text-indigo-600">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-2"><i class="fa-solid fa-bolt mr-1 text-amber-500"></i> Langsung login otomatis saat NISN yang terdaftar dimasukkan/discanned.</p>
                </div>

                <button type="submit" id="submitBtn"
                    class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition duration-200 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center space-x-2">
                    <span>Masuk ke Bilik Suara</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-6">
                <a href="<?= base_url('index.php') ?>" class="inline-flex items-center text-xs text-slate-500 hover:text-indigo-600 transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Halaman Utama / Live Count
                </a>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let isScannerActive = false;
        let isSubmitting = false;

        const nisnInput = document.getElementById('nisnInput');
        const loginForm = document.getElementById('voterLoginForm');
        const validSpinner = document.getElementById('validSpinner');

        // Automatic login on typing valid NISN
        let checkTimeout = null;
        nisnInput.addEventListener('input', function() {
            const val = this.value.trim();
            if (val.length >= 4 && !isSubmitting) {
                clearTimeout(checkTimeout);
                checkTimeout = setTimeout(() => {
                    checkAndAutoLogin(val);
                }, 300);
            }
        });

        function checkAndAutoLogin(nisn) {
            if (isSubmitting) return;
            validSpinner.classList.remove('hidden');

            fetch(`<?= base_url('api/check_voter.php') ?>?nisn=${encodeURIComponent(nisn)}`)
                .then(res => res.json())
                .then(data => {
                    validSpinner.classList.add('hidden');
                    if (data.exists && !isSubmitting) {
                        isSubmitting = true;
                        loginForm.submit();
                    }
                })
                .catch(() => {
                    validSpinner.classList.add('hidden');
                });
        }

        // Camera QR Code Scanner Toggle
        function toggleQrScanner() {
            const container = document.getElementById('qrScannerContainer');
            const btnText = document.getElementById('scannerBtnText');

            if (!isScannerActive) {
                container.classList.remove('hidden');
                btnText.textContent = "Tutup Kamera QR";
                isScannerActive = true;

                html5QrCode = new Html5Qrcode("qr-reader");
                html5QrCode.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    onQrCodeSuccess,
                    onQrCodeError
                ).catch(err => {
                    alert("Gagal mengakses kamera. Pastikan izin kamera telah diberikan.");
                    toggleQrScanner();
                });
            } else {
                stopQrScanner();
            }
        }

        function stopQrScanner() {
            if (html5QrCode && isScannerActive) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    document.getElementById('qrScannerContainer').classList.add('hidden');
                    document.getElementById('scannerBtnText').textContent = "Scan QR Code dengan Kamera";
                    isScannerActive = false;
                }).catch(() => {
                    document.getElementById('qrScannerContainer').classList.add('hidden');
                    document.getElementById('scannerBtnText').textContent = "Scan QR Code dengan Kamera";
                    isScannerActive = false;
                });
            }
        }

        function onQrCodeSuccess(decodedText, decodedResult) {
            let scannedNisn = decodedText.trim();
            // Parse if URL was encoded in QR
            if (scannedNisn.includes('nisn=')) {
                try {
                    const url = new URL(scannedNisn);
                    scannedNisn = url.searchParams.get('nisn') || scannedNisn;
                } catch(e){}
            }

            nisnInput.value = scannedNisn;
            stopQrScanner();

            // Auto submit immediately upon QR scan
            if (!isSubmitting) {
                isSubmitting = true;
                loginForm.submit();
            }
        }

        function onQrCodeError(errorMessage) {
            // Ignore frame scan errors
        }
    </script>
</body>
</html>
