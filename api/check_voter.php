<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$nisn = trim($_GET['nisn'] ?? '');

if (empty($nisn)) {
    echo json_encode(['exists' => false, 'can_vote' => false, 'status_memilih' => 0]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, nisn, status_memilih FROM pemilih WHERE nisn = ?");
$stmt->execute([$nisn]);
$voter = $stmt->fetch();

if ($voter) {
    echo json_encode([
        'exists' => true,
        'status_memilih' => (int)$voter['status_memilih'],
        'can_vote' => ((int)$voter['status_memilih'] === 0)
    ]);
} else {
    echo json_encode([
        'exists' => false,
        'can_vote' => false,
        'status_memilih' => 0
    ]);
}
