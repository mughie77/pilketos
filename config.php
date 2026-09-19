<?php
// Configuration & Database Connection Handler

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_driver = getenv('DB_DRIVER') ?: 'mysql'; // Default mysql, can fallback to sqlite for testing
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
    // If connection fails, output clear JSON or HTML message depending on context
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database Connection Failed: ' . $e->getMessage()]);
        exit;
    }
    die("Database Connection Error: " . $e->getMessage());
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        redirect('/admin/login.php');
    }
}

function check_voter_auth() {
    if (!isset($_SESSION['voter_logged_in']) || $_SESSION['voter_logged_in'] !== true) {
        redirect('/voter/login.php');
    }
}
