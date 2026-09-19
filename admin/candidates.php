<?php
require_once __DIR__ . '/../config.php';
check_admin_auth();

$message = '';
$error = '';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Get file photo to remove
    $stmt = $pdo->prepare("SELECT foto FROM paslon WHERE id = ?");
    $stmt->execute([$id]);
    $paslon = $stmt->fetch();

    if ($paslon) {
        if (!empty($paslon['foto']) && file_exists(__DIR__ . '/../' . $paslon['foto'])) {
            @unlink(__DIR__ . '/../' . $paslon['foto']);
        }

        $stmtDel = $pdo->prepare("DELETE FROM paslon WHERE id = ?");
        $stmtDel->execute([$id]);

        $_SESSION['flash_message'] = "Paslon berhasil dihapus!";
        redirect('candidates.php');
    }
}

// Handle Form Submit (Add / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nomor_urut  = (int)($_POST['nomor_urut'] ?? 0);
    $nama_ketua  = trim($_POST['nama_ketua'] ?? '');
    $nama_wakil  = trim($_POST['nama_wakil'] ?? '');
    $visi        = trim($_POST['visi'] ?? '');
    $misi        = trim($_POST['misi'] ?? '');

    if ($nomor_urut <= 0 || empty($nama_ketua)) {
        $error = "Nomor urut dan Nama Ketua OSIS wajib diisi!";
    } else {
        // Check uniqueness of nomor_urut
        $stmtCheck = $pdo->prepare("SELECT id FROM paslon WHERE nomor_urut = ? AND id != ?");
        $stmtCheck->execute([$nomor_urut, $id]);
        if ($stmtCheck->fetch()) {
            $error = "Nomor urut {$nomor_urut} sudah digunakan oleh paslon lain!";
        } else {
            // Photo upload handling
            $fotoPath = '';
            if ($id > 0) {
                // Get existing photo path
                $stmtExisting = $pdo->prepare("SELECT foto FROM paslon WHERE id = ?");
                $stmtExisting->execute([$id]);
                $existing = $stmtExisting->fetch();
                $fotoPath = $existing['foto'] ?? '';
            }

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['foto']['tmp_name'];
                $fileName = $_FILES['foto']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($fileExt, $allowed)) {
                    $error = "Format foto hanya boleh JPG, JPEG, PNG, atau WEBP!";
                } else {
                    $uploadDir = __DIR__ . '/../uploads/candidates/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $newFileName = 'paslon_' . $nomor_urut . '_' . time() . '.' . $fileExt;
                    $targetFile = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmp, $targetFile)) {
                        // Delete old photo if editing
                        if (!empty($fotoPath) && file_exists(__DIR__ . '/../' . $fotoPath)) {
                            @unlink(__DIR__ . '/../' . $fotoPath);
                        }
                        $fotoPath = 'uploads/candidates/' . $newFileName;
                    } else {
                        $error = "Gagal mengunggah foto. Periksa izin folder uploads.";
                    }
                }
            } elseif ($id == 0 && empty($fotoPath)) {
                $error = "Foto pasangan calon wajib diunggah untuk paslon baru!";
            }

            if (empty($error)) {
                if ($id > 0) {
                    // Update
                    $stmtUpd = $pdo->prepare("UPDATE paslon SET nomor_urut = ?, nama_ketua = ?, nama_wakil = ?, foto = ?, visi = ?, misi = ? WHERE id = ?");
                    $stmtUpd->execute([$nomor_urut, $nama_ketua, $nama_wakil, $fotoPath, $visi, $misi, $id]);
                    $_SESSION['flash_message'] = "Paslon nomor urut {$nomor_urut} berhasil diperbarui!";
                } else {
                    // Insert
                    $stmtIns = $pdo->prepare("INSERT INTO paslon (nomor_urut, nama_ketua, nama_wakil, foto, visi, misi) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtIns->execute([$nomor_urut, $nama_ketua, $nama_wakil, $fotoPath, $visi, $misi]);
                    $_SESSION['flash_message'] = "Paslon baru nomor urut {$nomor_urut} berhasil ditambahkan!";
                }
                redirect('candidates.php');
            }
        }
    }
}

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Fetch edit target if editing
$editPaslon = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmtEdit = $pdo->prepare("SELECT * FROM paslon WHERE id = ?");
    $stmtEdit->execute([(int)$_GET['id']]);
    $editPaslon = $stmtEdit->fetch();
}

// Fetch all candidates
$candidates = $pdo->query("SELECT * FROM paslon ORDER BY nomor_urut ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Paslon - E-Voting OSIS</title>
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
                        <i class="fa-solid fa-users-gear text-white"></i>
                    </div>
                    <div>
                        <span class="font-bold text-lg text-slate-900 block leading-none">Manajemen Paslon</span>
                        <span class="text-xs text-slate-500">Kelola Pasangan Calon Ketua & Wakil OSIS</span>
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
            <a href="candidates.php" class="flex items-center space-x-3 px-4 py-3 bg-indigo-600 text-white rounded-xl font-medium shadow-md shadow-indigo-600/20 transition">
                <i class="fa-solid fa-users-gear w-5 text-center"></i>
                <span>Manajemen Paslon</span>
            </a>
            <a href="voters.php" class="flex items-center space-x-3 px-4 py-3 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl font-medium transition">
                <i class="fa-solid fa-address-card w-5 text-center"></i>
                <span>Manajemen Pemilih</span>
            </a>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 space-y-8">

            <?php if (!empty($message)): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl text-sm flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-circle-check text-lg"></i>
                        <span><?= sanitize($message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-5 py-4 rounded-xl text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <span><?= sanitize($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Paslon (Add / Edit) -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center space-x-2">
                        <i class="fa-solid <?= $editPaslon ? 'fa-pen-to-square text-amber-500' : 'fa-plus-circle text-indigo-600' ?>"></i>
                        <span><?= $editPaslon ? 'Edit Data Paslon' : 'Tambah Paslon Baru' ?></span>
                    </h2>
                    <?php if ($editPaslon): ?>
                        <a href="candidates.php" class="text-xs text-slate-500 hover:text-slate-900 underline">Batal Edit</a>
                    <?php endif; ?>
                </div>

                <form action="candidates.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" value="<?= $editPaslon['id'] ?? 0 ?>">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Nomor Urut *</label>
                            <input type="number" min="1" name="nomor_urut" required value="<?= sanitize($editPaslon['nomor_urut'] ?? '') ?>"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 outline-none transition" placeholder="Contoh: 1">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Nama Calon Ketua *</label>
                            <input type="text" name="nama_ketua" required value="<?= sanitize($editPaslon['nama_ketua'] ?? '') ?>"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 outline-none transition" placeholder="Nama lengkap calon ketua">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Nama Calon Wakil (Opsional)</label>
                            <input type="text" name="nama_wakil" value="<?= sanitize($editPaslon['nama_wakil'] ?? '') ?>"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 outline-none transition" placeholder="Nama lengkap calon wakil">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                            Upload Foto Paslon <?= $editPaslon ? '(Biarkan kosong jika tidak ingin mengubah)' : '*' ?>
                        </label>
                        <div class="flex items-center space-x-4">
                            <?php if ($editPaslon && !empty($editPaslon['foto'])): ?>
                                <img src="../<?= sanitize($editPaslon['foto']) ?>" alt="Foto current" class="w-16 h-16 object-cover rounded-xl border border-slate-200">
                            <?php endif; ?>
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" <?= $editPaslon ? '' : 'required' ?>
                                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 transition cursor-pointer">
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WEBP. Maksimum disarankan 2MB.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Visi Paslon</label>
                            <textarea name="visi" rows="4" class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 text-sm outline-none transition" placeholder="Tuliskan visi pasangan calon..."><?= sanitize($editPaslon['visi'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">Misi Paslon</label>
                            <textarea name="misi" rows="4" class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-indigo-600 focus:bg-white rounded-xl text-slate-900 text-sm outline-none transition" placeholder="Tuliskan poin-poin misi pasangan calon..."><?= sanitize($editPaslon['misi'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="submit" class="py-3 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md shadow-indigo-600/20 transition">
                            <i class="fa-solid fa-floppy-disk mr-2"></i>
                            <span><?= $editPaslon ? 'Simpan Perubahan' : 'Tambah Paslon' ?></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Candidate List Cards -->
            <div>
                <h3 class="text-xl font-bold text-slate-900 mb-4">Daftar Pasangan Calon (<?= count($candidates) ?>)</h3>

                <?php if (empty($candidates)): ?>
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-12 text-center text-slate-400">
                        <i class="fa-solid fa-users-slash text-4xl mb-3 text-slate-300"></i>
                        <p>Belum ada data pasangan calon yang didaftarkan.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($candidates as $c): ?>
                            <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden flex flex-col justify-between shadow-sm">
                                <div>
                                    <!-- Header Badge -->
                                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
                                        <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-600 border border-indigo-200 font-bold rounded-lg text-sm">
                                            Nomor Urut 0<?= $c['nomor_urut'] ?>
                                        </span>
                                        <div class="flex items-center space-x-2">
                                            <a href="candidates.php?action=edit&id=<?= $c['id'] ?>" class="text-xs bg-amber-50 text-amber-600 hover:bg-amber-100 px-3 py-1.5 rounded-lg border border-amber-200 transition">
                                                <i class="fa-solid fa-pen"></i> Edit
                                            </a>
                                            <a href="candidates.php?action=delete&id=<?= $c['id'] ?>" onclick="return confirm('Yakin ingin menghapus paslon ini?')" class="text-xs bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg border border-red-200 transition">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </div>

                                    <div class="p-6 space-y-4">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-24 h-24 rounded-2xl overflow-hidden border border-slate-200 flex-shrink-0 bg-slate-100">
                                                <img src="../<?= sanitize($c['foto']) ?>" alt="Foto Paslon" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-bold text-slate-900"><?= sanitize($c['nama_ketua']) ?></h4>
                                                <?php if (!empty($c['nama_wakil'])): ?>
                                                    <p class="text-xs text-indigo-600 font-medium">Wakil: <?= sanitize($c['nama_wakil']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($c['visi'])): ?>
                                            <div>
                                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Visi:</span>
                                                <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200/80 leading-relaxed"><?= nl2br(sanitize($c['visi'])) ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($c['misi'])): ?>
                                            <div>
                                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Misi:</span>
                                                <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200/80 leading-relaxed"><?= nl2br(sanitize($c['misi'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
