<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

try {
    // Total Voters Count
    $stmtTotalPemilih = $pdo->query("SELECT COUNT(*) FROM pemilih");
    $totalPemilih = (int)$stmtTotalPemilih->fetchColumn();

    // Voted Count
    $stmtSudah = $pdo->query("SELECT COUNT(*) FROM pemilih WHERE status_memilih = 1");
    $totalSudah = (int)$stmtSudah->fetchColumn();

    $totalBelum = $totalPemilih - $totalSudah;
    $persentase = $totalPemilih > 0 ? round(($totalSudah / $totalPemilih) * 100, 1) : 0;

    // Paslon Results
    $sql = "SELECT p.id, p.nomor_urut, p.nama_ketua, p.nama_wakil, p.foto, p.visi, p.misi,
                   COUNT(v.id) as total_suara
            FROM paslon p
            LEFT JOIN pemilih v ON v.paslon_id = p.id AND v.status_memilih = 1
            GROUP BY p.id, p.nomor_urut, p.nama_ketua, p.nama_wakil, p.foto, p.visi, p.misi
            ORDER BY p.nomor_urut ASC";
    $candidates = $pdo->query($sql)->fetchAll();

    $results = [];
    foreach ($candidates as $c) {
        $votes = (int)$c['total_suara'];
        $percent = $totalSudah > 0 ? round(($votes / $totalSudah) * 100, 1) : 0;

        $results[] = [
            'id' => $c['id'],
            'nomor_urut' => $c['nomor_urut'],
            'nama_ketua' => $c['nama_ketua'],
            'nama_wakil' => $c['nama_wakil'],
            'foto' => $c['foto'],
            'visi' => $c['visi'],
            'misi' => $c['misi'],
            'total_suara' => $votes,
            'persentase' => $percent
        ];
    }

    echo json_encode([
        'status' => 'success',
        'timestamp' => date('H:i:s'),
        'stats' => [
            'total_pemilih' => $totalPemilih,
            'total_sudah'   => $totalSudah,
            'total_belum'   => $totalBelum,
            'persentase'    => $persentase
        ],
        'candidates' => $results
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
