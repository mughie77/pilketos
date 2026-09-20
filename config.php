<?php
// Configuration & Database Connection Handler

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_driver = getenv('DB_DRIVER') ?: 'mysql'; // Default mysql, fallback to sqlite for testing
$db_host   = getenv('DB_HOST') ?: 'localhost';
$db_name   = getenv('DB_NAME') ?: 'e_voting_osis';
$db_user   = getenv('DB_USER') ?: 'root';
$db_pass   = getenv('DB_PASS') ?: '';

try {
    if ($db_driver === 'sqlite') {
        $db_path = __DIR__ . '/e_voting_osis.sqlite';
        $pdo = new PDO("sqlite:" . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec("PRAGMA foreign_keys = ON;");
    } else {
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    }
} catch (PDOException $e) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Connection Failed: ' . $e->getMessage()]);
        exit;
    }
    die("Database Connection Error: " . $e->getMessage());
}

// Automatic Base URL Detection Helper
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Get root directory relative to script filename
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $appDir  = rtrim(str_replace('\\', '/', __DIR__), '/');

    $subDir = '';
    if (!empty($docRoot) && strpos($appDir, $docRoot) === 0) {
        $subDir = substr($appDir, strlen($docRoot));
    }

    $baseUrl = rtrim($protocol . $host . $subDir, '/');
    $path = ltrim($path, '/');

    return $path ? $baseUrl . '/' . $path : $baseUrl . '/';
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header("Location: " . $path);
    } else {
        header("Location: " . base_url($path));
    }
    exit;
}

function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        redirect('admin/login.php');
    }
}

function check_voter_auth() {
    if (!isset($_SESSION['voter_logged_in']) || $_SESSION['voter_logged_in'] !== true) {
        redirect('voter/login.php');
    }
}
