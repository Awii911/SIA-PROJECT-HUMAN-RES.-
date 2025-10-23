<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf = $_POST['csrf'] ?? '';

    // Store last entered username
    $_SESSION['old_username'] = $username;

    // CSRF check
    if ($csrf !== ($_SESSION['csrf'] ?? '')) {
        $_SESSION['error'] = "Invalid session token.";
        header("Location: login.php");
        exit;
    }

    // Google reCAPTCHA verification
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $secretKey = "6LeFeM4rAAAAAHcJh8H7tYxBuqC8tV1Cel02Wvp3";
    $verifyResponse = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $captchaResponse
    );
    $captcha = json_decode($verifyResponse, true);

    if (empty($captchaResponse) || !$captcha['success']) {
        $_SESSION['error'] = "Please verify the captcha.";
        $_SESSION['error_type'] = 'captcha';
        header("Location: login.php");
        exit;
    }

    $pdo = getPDO();

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    $login_success = false; // flag to log

    if (!$user) {
        $_SESSION['error'] = "Invalid username or password.";
    } elseif ($user['locked_until'] === "permanent") {
        $_SESSION['error'] = "Your account has been permanently locked.";
    } elseif (password_verify($password, $user['password'])) {
        // Successful login
        $login_success = true;

        unset($_SESSION['old_username']);
        $stmt = $pdo->prepare("UPDATE users SET attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';

        // Redirect based on role
        header($_SESSION['role'] === 'admin' ? "Location: admin_logs.php" : "Location: dashboard.php");
        exit;
    } else {
        // Failed login, handle attempts
        $attempts = $user['attempts'] + 1;
        $locked_until = null;

        if ($attempts == 4) {
            $locked_until = date("Y-m-d H:i:s", strtotime("+1 minute"));
            $_SESSION['error'] = "Wrong password. Account locked for 1 minute.";
        } elseif ($attempts == 5) {
            $locked_until = date("Y-m-d H:i:s", strtotime("+15 minutes"));
            $_SESSION['error'] = "Wrong password again. Account locked for 15 minutes.";
        } elseif ($attempts == 6) {
            $locked_until = date("Y-m-d H:i:s", strtotime("+30 minutes"));
            $_SESSION['error'] = "Wrong password again. Account locked for 30 minutes.";
        } elseif ($attempts == 7) {
            $locked_until = date("Y-m-d H:i:s", strtotime("+1 hour"));
            $_SESSION['error'] = "Wrong password again. Account locked for 1 hour.";
        } elseif ($attempts >= 8) {
            $locked_until = "permanent";
            $_SESSION['error'] = "Your account has been permanently locked.";
        } else {
            $remaining = 4 - $attempts;
            if ($remaining == 2) $_SESSION['error'] = "Invalid password. 2 attempts left.";
            elseif ($remaining == 1) $_SESSION['error'] = "Invalid password. 1 attempt left. Next wrong attempt locks account.";
            else $_SESSION['error'] = "Invalid username or password.";
        }

        $stmt = $pdo->prepare("UPDATE users SET attempts = ?, locked_until = ? WHERE id = ?");
        $stmt->execute([$attempts, $locked_until, $user['id']]);
    }

    // Log attempt in auth_logs table
    $stmt = $pdo->prepare("INSERT INTO auth_logs (user_id, event_type, event_msg, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([
        $user['id'] ?? null,
        $login_success ? 'success' : 'fail',
        $login_success ? 'Logged in successfully' : $_SESSION['error'] ?? 'Failed login attempt',
        $_SERVER['REMOTE_ADDR']
    ]);

    header("Location: login.php");
    exit;
}
