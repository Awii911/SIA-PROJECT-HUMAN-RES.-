<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/functions.php';
if(session_status()===PHP_SESSION_NONE) session_start();
require_admin();

$pdo = getPDO();
$users = $pdo->query("SELECT id, username, attempts, locked_until, role FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Inter,sans-serif;background:#f0f2f5;margin:0;padding:0;}
.sidebar{width:220px;background:#007bff;color:#fff;min-height:100vh;padding-top:20px;position:fixed;}
.sidebar h2{text-align:center;margin-bottom:30px;font-weight:600;}
.sidebar a{display:block;color:#fff;text-decoration:none;padding:12px 20px;margin:5px 0;border-radius:6px;transition:background 0.3s;}
.sidebar a:hover{background:#0056b3;}
.main{margin-left:220px;padding:20px;}
table{width:100%;border-collapse:collapse;}
th,td{padding:10px;border:1px solid #ddd;text-align:center;}
th{background:#007bff;color:#fff;}
tr:nth-child(even){background:#f9f9f9;}
td.failed{color:red;font-weight:600;}
td.success{color:green;font-weight:600;}
a.unlock{color:#007bff;text-decoration:none;font-weight:500;}
a.unlock:hover{color:#0056b3;text-decoration:underline;}
.alert{padding:10px 15px;border-radius:6px;margin-bottom:20px;}
.alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
.alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
.filter-wrapper{margin-bottom:15px;display:flex;flex-wrap:wrap;gap:10px;}
.filter-wrapper input, .filter-wrapper select{padding:6px 10px;border-radius:6px;border:1px solid #ccc;}
@media(max-width:768px){.sidebar{position:relative;width:100%;min-height:auto;}.main{margin-left:0;}}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_logs.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="users.php"><i class="fa-solid fa-users"></i> Users</a>
    <a href="login_info.php"><i class="fa-solid fa-list"></i> Login Info</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">
<h2>Users Management</h2>

<!-- Success/Error Messages -->
<?php if(!empty($_SESSION['success'])): ?>
<div class="alert alert-success"><?=htmlspecialchars($_SESSION['success'])?></div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(!empty($_SESSION['error'])): ?>
<div class="alert alert-error"><?=htmlspecialchars($_SESSION['error'])?></div>
<?php unset($_SESSION['error']); endif; ?>

<div class="filter-wrapper">
    <input type="text" id="userSearch" placeholder="Search username..." oninput="filterUsers()">
    <select id="userFilter" onchange="filterUsers()">
        <option value="all">All Users</option>
        <option value="locked">Locked Users</option>
        <option value="failed">Failed Attempts</option>
    </select>
</div>

<table id="usersTable">
<tr><th>Username</th><th>Attempts</th><th>Locked Until</th><th>Role</th><th>Action</th></tr>
<?php foreach($users as $u): ?>
<tr class="<?= $u['locked_until'] && $u['locked_until']!=='permanent' ? 'locked' : '' ?>">
    <td><?=htmlspecialchars($u['username'])?></td>
    <td class="<?= $u['attempts']>0 ? 'failed' : 'success' ?>"><?=htmlspecialchars($u['attempts'])?></td>
    <td><?=htmlspecialchars($u['locked_until'] ?? '-')?></td>
    <td><?=htmlspecialchars($u['role'] ?? '-')?></td>
    <td>
        <?php if($u['locked_until']): ?>
            <a class="unlock" href="unlock_user.php?id=<?= $u['id'] ?>">Unlock</a>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
</div>

<script>
function filterUsers(){
    const search = document.getElementById('userSearch').value.toLowerCase();
    const filter = document.getElementById('userFilter').value;
    const rows = document.querySelectorAll('#usersTable tr:not(:first-child)');
    rows.forEach(row=>{
        const username = row.cells[0].textContent.toLowerCase();
        const attempts = parseInt(row.cells[1].textContent);
        const locked = row.cells[2].textContent !== '-';
        let show = true;
        if(search && !username.includes(search)) show=false;
        if(filter==='locked' && !locked) show=false;
        if(filter==='failed' && attempts===0) show=false;
        row.style.display = show?'':'none';
    });
}
</script>

</body>
</html>
