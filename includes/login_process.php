<?php
// ============================================================
// CDAS — Login Process Handler
// ============================================================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/cdas/index.php');
}

$email    = clean($_POST['email']    ?? '');
$password = $_POST['password']       ?? '';
$role     = clean($_POST['role']     ?? '');

// Validate inputs
if (empty($email) || empty($password) || empty($role)) {
    redirect('/cdas/index.php?error=1');
}

// Fetch user by email and role
$stmt = $conn->prepare(
    "SELECT u.*, un.unit_name 
     FROM users u
     LEFT JOIN units un ON u.unit_id = un.unit_id
     WHERE u.email = ? AND u.role = ? AND u.status = 'active'
     LIMIT 1"
);
$stmt->bind_param('ss', $email, $role);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    redirect('/cdas/index.php?error=1');
}

$loginOk = false;

// Check if stored password is a valid bcrypt hash
if (strlen($user['password']) >= 60 && $user['password'][0] === '$') {
    // Proper bcrypt hash — verify normally
    $loginOk = password_verify($password, $user['password']);
} else {
    // Plain text in DB (sample/dev mode) — compare directly then upgrade
    if ($password === $user['password']) {
        $loginOk = true;
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $upd = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $upd->bind_param('si', $newHash, $user['user_id']);
        $upd->execute();
        $upd->close();
    }
}

if (!$loginOk) {
    redirect('/cdas/index.php?error=1');
}

// Set session
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];
$_SESSION['unit_id']   = $user['unit_id'];
$_SESSION['unit_name'] = $user['unit_name'] ?? 'N/A';

// Log the login activity
logActivity($conn, $user['user_id'], 'LOGIN', null, null, 'User logged in successfully.');

// Redirect based on role
switch ($user['role']) {
    case 'superadmin':
        redirect('/cdas/modules/superadmin/dashboard.php');
        break;
    case 'admin':
        redirect('/cdas/modules/admin/dashboard.php');
        break;
    case 'user':
    default:
        redirect('/cdas/modules/user/dashboard.php');
        break;
}
