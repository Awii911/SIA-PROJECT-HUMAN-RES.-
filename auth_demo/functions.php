<?php
require_once __DIR__ . '/config.php';

function write_file_log($line) {
    $fname = LOG_DIR . '/auth-' . date('Y-m-d') . '.log';
    $entry = '[' . date('Y-m-d H:i:s') . '] ' . $line . PHP_EOL;
    file_put_contents($fname, $entry, FILE_APPEND | LOCK_EX);
}

function record_event($user_id, $username, $event_type, $message = '') {
    $pdo = getPDO();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $pdo->prepare('INSERT INTO auth_logs (user_id, username, event_type, event_msg, ip_address, user_agent) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$user_id, $username, $event_type, $message, $ip, $ua]);

    write_file_log(
        "u=" . ($username ?: '') .
        " event=" . $event_type .
        " msg=" . trim(str_replace("\n", " ", $message)) .
        " ip={$ip} ua=" . substr($ua, 0, 150)
    );
}

function check_lockout($user) {
    if (!$user) return false;
    if ($user['locked_until'] !== null) {
        $lockedUntil = strtotime($user['locked_until']);
        if ($lockedUntil > time()) {
            return true;
        } else {
            $pdo = getPDO();
            $stmt = $pdo->prepare('UPDATE users SET locked_until = NULL, failed_attempts = 0 WHERE id = ?');
            $stmt->execute([$user['id']]);
            record_event($user['id'], $user['username'], 'unlocked', 'Lock expired, auto-unlocked');
            return false;
        }
    }
    return false;
}

function handle_failed_login($user) {
    $pdo = getPDO();
    $now = date('Y-m-d H:i:s');
    if (!$user) {
        record_event(null, null, 'login_failure', 'Unknown username');
        return;
    }

    $failed = $user['failed_attempts'] + 1;
    $stmt = $pdo->prepare('UPDATE users SET failed_attempts = ?, last_failed_at = ? WHERE id = ?');
    $stmt->execute([$failed, $now, $user['id']]);
    record_event($user['id'], $user['username'], 'login_failure', "failed_attempts={$failed}");

    if ($failed >= MAX_FAILED_ATTEMPTS) {
        $lockedUntil = date('Y-m-d H:i:s', time() + LOCK_DURATION_SECONDS);
        $stmt = $pdo->prepare('UPDATE users SET locked_until = ?, warning_count = warning_count + 1 WHERE id = ?');
        $stmt->execute([$lockedUntil, $user['id']]);
        record_event($user['id'], $user['username'], 'locked', "locked_until={$lockedUntil}");
    }
}

function reset_successful_login($userId, $username) {
    $pdo = getPDO();
    $stmt = $pdo->prepare('UPDATE users SET failed_attempts = 0, last_failed_at = NULL, locked_until = NULL WHERE id = ?');
    $stmt->execute([$userId]);
    record_event($userId, $username, 'login_success', 'User logged in successfully');
}

function require_admin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $r = $stmt->fetch();
    if (!$r || $r['role'] !== 'admin') {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function unlock_user($userId) {
    $pdo = getPDO();
    $stmt = $pdo->prepare('UPDATE users SET locked_until = NULL, failed_attempts = 0 WHERE id = ?');
    $stmt->execute([$userId]);
    record_event($userId, null, 'unlocked', 'Unlocked by admin');
}
