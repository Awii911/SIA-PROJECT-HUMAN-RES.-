<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle sign out
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Inter,sans-serif;background:#f0f2f5;margin:0;padding:0;}
.sidebar{width:220px;background:#007bff;color:#fff;min-height:100vh;padding-top:20px;position:fixed;}
.sidebar h2{text-align:center;margin-bottom:30px;font-weight:600;}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:12px 20px;margin:5px 0;border-radius:6px;transition:background 0.3s;}
.sidebar a:hover{background:#0056b3;}
.main{margin-left:220px;padding:20px;}
.card{background:#fff;padding:20px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;max-width:500px;margin:auto;margin-top:100px;}
.card h1{color:#111;margin-bottom:15px;}
.card p{color:#555;margin-bottom:25px;}
.button{display:inline-block;padding:10px 25px;background:#007bff;color:#fff;border-radius:6px;text-decoration:none;font-weight:500;transition:0.3s;}
.button:hover{background:#0056b3;}
@media(max-width:768px){.sidebar{position:relative;width:100%;min-height:auto;}.main{margin-left:0;}}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Dashboard</h2>
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> Home</a>
    <a href="login_info.php"><i class="fa-solid fa-list"></i> Login Info</a>
    <a href="?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
</div>

<div class="main">
    <div class="card">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
        <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['role'] ?? 'user') ?></strong>.</p>
        <a href="?action=logout" class="button"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
    </div>
</div>

</body>
</html>
