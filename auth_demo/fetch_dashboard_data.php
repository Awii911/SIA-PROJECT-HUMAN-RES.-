<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/functions.php';
if(session_status()===PHP_SESSION_NONE) session_start();
require_admin();

$pdo = getPDO();

function fetchData($pdo){
    // same logic as above
    $users = $pdo->query("SELECT id, username, failed_attempts, locked_until, warning_count FROM users ORDER BY warning_count DESC, username LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
    $logs = $pdo->query("SELECT l.*, u.username AS uid_username FROM auth_logs l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.created_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

    $stats = $pdo->query("SELECT event_type, COUNT(*) AS count FROM auth_logs GROUP BY event_type")->fetchAll(PDO::FETCH_ASSOC);
    $chartData = ['success'=>0,'fail'=>0];
    foreach($stats as $s){
        $type = strtolower($s['event_type']);
        if(strpos($type,'fail')!==false) $chartData['fail']=(int)$s['count'];
        else $chartData['success']=(int)$s['count'];
    }

    $trend = $pdo->query("
        SELECT DATE(created_at) as day,
            SUM(CASE WHEN event_type LIKE '%fail%' THEN 1 ELSE 0 END) as fail_count,
            SUM(CASE WHEN event_type NOT LIKE '%fail%' THEN 1 ELSE 0 END) as success_count
        FROM auth_logs
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY day ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $trend_labels=[];$trend_success=[];$trend_fail=[];
    foreach($trend as $t){$trend_labels[]=$t['day'];$trend_success[]=(int)$t['success_count'];$trend_fail[]=(int)$t['fail_count'];}

    return ['users'=>$users,'logs'=>$logs,'chartData'=>$chartData,'trend_labels'=>$trend_labels,'trend_success'=>$trend_success,'trend_fail'=>$trend_fail];
}

header('Content-Type: application/json');
echo json_encode(fetchData($pdo));
