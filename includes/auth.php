<?php
// ============================================================
// CDAS — Auth & Session Helpers
// ============================================================
session_start();

/**
 * Redirect helper
 */
function redirect(string $path): void {
    header("Location: $path");
    exit();
}

/**
 * Check if user is logged in; redirect to login if not.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        redirect('/cdas/index.php');
    }
}

/**
 * Require a specific role. Pass an array of allowed roles.
 */
function requireRole(array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        redirect('/cdas/unauthorized.php');
    }
}

/**
 * Return currently logged-in user data from session.
 */
function currentUser(): array {
    return [
        'user_id'   => $_SESSION['user_id']   ?? null,
        'full_name' => $_SESSION['full_name']  ?? '',
        'email'     => $_SESSION['email']      ?? '',
        'role'      => $_SESSION['role']       ?? '',
        'unit_id'   => $_SESSION['unit_id']    ?? null,
        'unit_name' => $_SESSION['unit_name']  ?? '',
    ];
}

/**
 * Log an activity to the database.
 */
function logActivity(
    mysqli $conn,
    int    $userId,
    string $action,
    string $targetType = null,
    int    $targetId   = null,
    string $details    = null
): void {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, action, target_type, target_id, details, ip_address)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('issiis', $userId, $action, $targetType, $targetId, $details, $ip);
    $stmt->execute();
    $stmt->close();
}

/**
 * Send a notification to a user.
 */
function sendNotification(
    mysqli $conn,
    int    $userId,
    string $title,
    string $message,
    string $type  = 'system',
    int    $refId = null
): void {
    $stmt = $conn->prepare(
        "INSERT INTO notifications (user_id, title, message, type, ref_id)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('isssi', $userId, $title, $message, $type, $refId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Hash a plain-text password.
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify a plain-text password against a stored hash.
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Sanitize user input.
 */
function clean(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Format bytes to human-readable string.
 */
function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow   = min($pow, count($units) - 1);
    return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}
