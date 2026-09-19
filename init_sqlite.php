<?php
// Script to initialize SQLite database for development/testing if MySQL server is unavailable in sandbox
require_once __DIR__ . '/config.php';

if ($db_driver === 'sqlite') {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            nama TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS paslon (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nomor_urut INTEGER NOT NULL UNIQUE,
            nama_ketua TEXT NOT NULL,
            nama_wakil TEXT DEFAULT NULL,
            foto TEXT NOT NULL,
            visi TEXT DEFAULT NULL,
            misi TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS pemilih (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nisn TEXT NOT NULL UNIQUE,
            nama TEXT NOT NULL,
            status_memilih INTEGER DEFAULT 0,
            paslon_id INTEGER DEFAULT NULL,
            waktu_memilih DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (paslon_id) REFERENCES paslon(id) ON DELETE SET NULL
        );
    ");

    // Insert default admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, nama) VALUES ('admin', ?, 'Administrator Utama')");
        $stmt->execute([$hash]);
    }

    echo "SQLite database initialized successfully.\n";
} else {
    echo "Configured for MySQL.\n";
}
