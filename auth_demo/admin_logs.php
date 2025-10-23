<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
if(session_status()===PHP_SESSION_NONE) session_start();
require_admin();

$pdo = getPDO();

// Fetch summary counts
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$lockedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE locked_until IS NOT NULL")->fetchColumn();
$failedAttempts = $pdo->query("SELECT COUNT(*) FROM auth_logs WHERE event_type LIKE '%fail%'")->fetchColumn();
$successfulLogins = $pdo->query("SELECT COUNT(*) FROM auth_logs WHERE event_type NOT LIKE '%fail%'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Inter,sans-serif;background:#f0f2f5;margin:0;padding:0;}
.sidebar{width:220px;background:#007bff;color:#fff;min-height:100vh;padding-top:20px;position:fixed;}
.sidebar h2{text-align:center;margin-bottom:30px;font-weight:600;}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:12px 20px;margin:5px 0;border-radius:6px;transition:background 0.3s;}
.sidebar a:hover{background:#0056b3;}
.main{margin-left:220px;padding:20px;}
.cards{display:flex;flex-wrap:wrap;gap:20px;margin-bottom:30px;}
.card{flex:1;min-width:180px;background:#fff;padding:20px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);text-align:center;}
.card h3{margin-bottom:10px;color:#555;}
.card span{font-size:24px;font-weight:600;color:#111;}
a.page-link{display:inline-block;margin-top:10px;color:#007bff;text-decoration:none;font-weight:500;}
a.page-link:hover{text-decoration:underline;}
@media(max-width:768px){.sidebar{position:relative;width:100%;min-height:auto;}.main{margin-left:0;}}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_logs.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
    <a href="Login_Info.php"><i class="fa-solid fa-list"></i> Login Info</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">
<h2>Admin Dashboard</h2>

<div class="cards">
    <div class="card">
        <h3>Total Users</h3>
        <span><?=htmlspecialchars($totalUsers)?></span>
        <br><a class="page-link" href="users.php">Manage Users</a>
    </div>
    <div class="card">
        <h3>Locked Users</h3>
        <span><?=htmlspecialchars($lockedUsers)?></span>
        <br><a class="page-link" href="users.php">View Locked</a>
    </div>
    <div class="card">
        <h3>Failed Logins</h3>
        <span><?=htmlspecialchars($failedAttempts)?></span>
        <br><a class="page-link" href="Login_Info.php">View Logs</a>
    </div>
    <div class="card">
        <h3>Successful Logins</h3>
        <span><?=htmlspecialchars($successfulLogins)?></span>
        <br><a class="page-link" href="Login_Info.php">View Logs</a>
    </div>
</div>
</div>

</body>
</html>
