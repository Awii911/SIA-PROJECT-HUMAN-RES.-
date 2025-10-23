<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/functions.php';
if(session_status()===PHP_SESSION_NONE) session_start();
require_admin();

$pdo = getPDO();
$logs = $pdo->query("
    SELECT l.*, u.username AS uid_username 
    FROM auth_logs l 
    LEFT JOIN users u ON u.id = l.user_id 
    ORDER BY l.created_at DESC 
    LIMIT 200
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Info</title>
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
<h2>Login Info</h2>

<div class="filter-wrapper">
    <input type="text" id="logSearch" placeholder="Search username/event..." oninput="filterLogs()">
    <select id="logFilter" onchange="filterLogs()">
        <option value="all">All Logs</option>
        <option value="fail">Failed</option>
        <option value="success">Success</option>
    </select>
</div>

<table id="logsTable">
<tr><th>Time</th><th>User ID</th><th>Username</th><th>Event</th><th>Message</th><th>IP</th></tr>
<?php foreach($logs as $l): ?>
<tr>
  <td><?=htmlspecialchars($l['created_at'])?></td>
  <td><?=htmlspecialchars($l['user_id'])?></td>
  <td><?=htmlspecialchars($l['username'] ?: $l['uid_username'])?></td>
  <td class="<?= strpos($l['event_type'],'fail')!==false ? 'failed' : 'success' ?>"><?=htmlspecialchars($l['event_type'])?></td>
  <td><?=htmlspecialchars($l['event_msg'])?></td>
  <td><?=htmlspecialchars($l['ip_address'])?></td>
</tr>
<?php endforeach; ?>
</table>
</div>

<script>
function filterLogs(){
    const search = document.getElementById('logSearch').value.toLowerCase();
    const filter = document.getElementById('logFilter').value;
    const rows = document.querySelectorAll('#logsTable tr:not(:first-child)');
    rows.forEach(row=>{
        const username = row.cells[2].textContent.toLowerCase();
        const event = row.cells[3].textContent.toLowerCase();
        let show = true;
        if(search && !(username.includes(search) || event.includes(search))) show=false;
        if(filter==='fail' && !event.includes('fail')) show=false;
        if(filter==='success' && event.includes('fail')) show=false;
        row.style.display = show?'':'none';
    });
}
</script>

</body>
</html>
