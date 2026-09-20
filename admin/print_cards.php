<?php
require_once __DIR__ . '/../config.php';
check_admin_auth();

// Fetch all voters or search filtered voters
$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM pemilih WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (nisn LIKE ? OR nama LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY nisn ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$voters = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Pemilih - SMK Negeri 2 Bondowoso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* A4 Page Formatting for Printing */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-after: always;
            }
            .voter-card {
                break-inside: avoid;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen p-4 sm:p-8">

    <!-- Top Action Bar (Hidden during printing) -->
    <div class="no-print max-w-7xl mx-auto mb-8 bg-white border border-slate-200 rounded-2xl p-4 sm:p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Cetak Kartu Pemilih Ketos OSIS</h1>
            <p class="text-xs text-slate-500 mt-1">Format A4 Portrait &bull; Total <?= count($voters) ?> kartu siap dicetak</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="<?= base_url('admin/voters.php') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition border border-slate-200 flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/20 transition flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Simpan PDF (A4)</span>
            </button>
        </div>
    </div>

    <!-- Cards Grid Container (A4 Printable Area) -->
    <div class="max-w-[210mm] mx-auto grid grid-cols-2 gap-3.5 sm:gap-4">
        <?php if (empty($voters)): ?>
            <div class="col-span-2 bg-white p-12 text-center text-slate-400 rounded-2xl border border-slate-200">
                <i class="fa-solid fa-address-card text-4xl mb-3 text-slate-300"></i>
                <p>Belum ada data pemilih untuk dicetak.</p>
            </div>
        <?php else: ?>
            <?php foreach ($voters as $v): ?>
                <div class="voter-card bg-white border-2 border-indigo-600/80 rounded-2xl p-3.5 relative flex flex-col justify-between shadow-sm overflow-hidden min-h-[170px]">
                    <!-- Header Section -->
                    <div class="border-b-2 border-indigo-600/20 pb-2 mb-2 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div>
                                <h2 class="text-xs font-black text-slate-900 leading-tight uppercase tracking-normal">
                                    Kartu <br />
                                    Pemilihan Ketua OSIS <br />
                                    <span class="text-indigo-600 font-extrabold">SMK Negeri 2 Bondowoso</span>
                                </h2>
                            </div>
                        </div>
                        <span class="text-2xs font-mono bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded-md font-bold uppercase">
                            DPT OSIS
                        </span>
                    </div>

                    <!-- Student Details & QR Code Section -->
                    <div class="flex items-center justify-between gap-2 py-1">
                        <div class="space-y-1 flex-1 min-w-0">
                            <div class="flex items-baseline">
                                <span class="text-2xs font-bold text-slate-400 uppercase w-20 flex-shrink-0">Nama Siswa</span>
                                <span class="text-2xs text-slate-400 mr-1.5">:</span>
                                <span class="text-xs font-bold text-slate-900 truncate"><?= sanitize($v['nama']) ?></span>
                            </div>

                            <div class="flex items-baseline">
                                <span class="text-2xs font-bold text-slate-400 uppercase w-20 flex-shrink-0">Kelas/Peran</span>
                                <span class="text-2xs text-slate-400 mr-1.5">:</span>
                                <span class="text-2xs font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200"><?= sanitize($v['kelas'] ?? 'Siswa') ?></span>
                            </div>

                            <div class="flex items-baseline">
                                <span class="text-2xs font-bold text-slate-400 uppercase w-20 flex-shrink-0">NISN (Login)</span>
                                <span class="text-2xs text-slate-400 mr-1.5">:</span>
                                <span class="text-xs font-mono font-extrabold text-indigo-600 tracking-wider bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100"><?= sanitize($v['nisn']) ?></span>
                            </div>
                        </div>

                        <!-- QR Code Container -->
                        <div class="flex-shrink-0 flex flex-col items-center justify-center p-1 bg-slate-50 border border-slate-200 rounded-xl">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($v['nisn']) ?>" alt="QR Login <?= sanitize($v['nisn']) ?>" class="w-14 h-14 object-contain rounded">
                            <span class="text-3xs text-slate-400 font-mono mt-0.5">Scan QR</span>
                        </div>
                    </div>

                    <!-- Footer Instructions -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between mt-1">
                        <p class="text-3xs text-slate-400 leading-tight">
                            * Gunakan NISN atau <strong class="text-slate-600">Scan QR Code</strong> diatas untuk login ke bilik suara web.
                        </p>
                        <div class="text-right flex-shrink-0">
                            <span class="text-3xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 rounded">
                                E-Voting Valid
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>
