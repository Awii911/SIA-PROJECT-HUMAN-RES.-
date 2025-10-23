<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/functions.php';
if(session_status()===PHP_SESSION_NONE) session_start();
require_admin(); // Only admin can access

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: users.php");
    exit;
}

$pdo = getPDO();
$userId = (int)$_GET['id'];

// Check if user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if(!$user){
    $_SESSION['error'] = "User not found.";
    header("Location: users.php");
    exit;
}

// Reset attempts and unlock account
$stmt = $pdo->prepare("UPDATE users SET attempts = 0, locked_until = NULL WHERE id = ?");
$stmt->execute([$userId]);

// Optional: log this action in auth_logs
$stmt = $pdo->prepare("INSERT INTO auth_logs (user_id, event_type, event_msg, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([
    $userId,
    'admin_action',
    'Account unlocked by admin',
    $_SERVER['REMOTE_ADDR']
]);

$_SESSION['success'] = "User '{$user['username']}' has been unlocked successfully.";
header("Location: users.php");
exit;
