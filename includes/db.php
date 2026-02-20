<?php
// ============================================================
// CDAS — Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'cdas_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');

// App-wide constants
define('UPLOAD_DIR',    __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20 MB
define('ALLOWED_TYPES', ['pdf', 'docx', 'jpg', 'jpeg', 'png']);
define('APP_NAME',      'CDAS — MARINA');
define('APP_VERSION',   '1.0.0');
