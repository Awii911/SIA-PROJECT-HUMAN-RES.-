<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// CSRF check
if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
    $_SESSION['error'] = "Invalid session token.";
    header("Location: register.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

// Check password match
if ($password !== $confirm) {
    $_SESSION['error'] = "Passwords do not match.";
    $_SESSION['old_username'] = $username;
    header("Location: register.php");
    exit;
}

// Check username length
if (strlen($username) < 3) {
    $_SESSION['error'] = "Username must be at least 3 characters.";
    $_SESSION['old_username'] = $username;
    header("Location: register.php");
    exit;
}

// **Server-side Password Strength Validation**
$score = 0;
if (strlen($password) >= 6) $score++;
if (preg_match('/[A-Z]/', $password)) $score++;
if (preg_match('/[0-9]/', $password)) $score++;
if (preg_match('/[\W_]/', $password)) $score++;

if ($score <= 2) {
    $_SESSION['error'] = "Password is too weak. Please use at least 6 characters, including an uppercase letter, a number, and a symbol.";
    $_SESSION['old_username'] = $username;
    header("Location: register.php");
    exit;
}

// Verify Google reCAPTCHA
$recaptcha_secret = "6LeFeM4rAAAAAHcJh8H7tYxBuqC8tV1Cel02Wvp3"; // replace with your secret key
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
$captcha_success = json_decode($verify)->success ?? false;

if (!$captcha_success) {
    $_SESSION['error'] = "Please complete the CAPTCHA.";
    $_SESSION['old_username'] = $username;
    header("Location: register.php");
    exit;
}

// Database connection
$pdo = getPDO();

// Check if username exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    $_SESSION['error'] = "Username already exists.";
    $_SESSION['old_username'] = $username;
    header("Location: register.php");
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO users (username, password, attempts, locked_until) VALUES (?, ?, 0, NULL)");
$stmt->execute([$username, $hash]);

$_SESSION['success'] = "Account created successfully! You can now log in below.";
$_SESSION['old_username'] = $username;
header("Location: register.php");
exit;
