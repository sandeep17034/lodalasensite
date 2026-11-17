
<?php
date_default_timezone_set('Asia/Singapore');

// Get visitor IP
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// Detect proxy headers (informational only)
$forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
$real_ip = $_SERVER['HTTP_X_REAL_IP'] ?? '';

$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$method = $_SERVER['REQUEST_METHOD'] ?? 'Unknown';
$time = date('Y-m-d H:i:s');

// Validate IP
$ip_version = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
    ? 'IPv4'
    : (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 'IPv6' : 'Unknown');

// Prepare log
$log = [
    'time' => $time,
    'ip' => $ip,
    'ip_version' => $ip_version,
    'forwarded_for' => $forwarded,
    'real_ip' => $real_ip,
    'user_agent' => $user_agent,
    'method' => $method
];

// Append to text file
file_put_contents(
    __DIR__ . '/visitors.txt',
    json_encode($log, JSON_UNESCAPED_SLASHES) . PHP_EOL,
    FILE_APPEND | LOCK_EX
);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>IP Checker</title>
<style>
body {
    background: #101827;
    color: #fff;
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}
.card {
    background: #1d293b;
    padding: 30px;
    border-radius: 14px;
    width: 90%;
    max-width: 430px;
    box-sizing: border-box;
}
h2 { margin-top: 0; color: #60a5fa; }
.ip {
    font-size: 27px;
    font-weight: bold;
    overflow-wrap: anywhere;
    margin: 20px 0;
}
.info {
    border-top: 1px solid #374151;
    padding: 12px 0;
    font-size: 14px;
}
.label { color: #9ca3af; }
</style>
</head>
<body>
<div class="card">
    <h2>Your IP Information</h2>
    <div class="ip"><?= htmlspecialchars($ip) ?></div>

    <div class="info">
        <span class="label">IP Version:</span>
        <?= htmlspecialchars($ip_version) ?>
    </div>

    <div class="info">
        <span class="label">User Agent:</span><br>
        <?= htmlspecialchars($user_agent) ?>
    </div>

    <div class="info">
        <span class="label">Request Method:</span>
        <?= htmlspecialchars($method) ?>
    </div>

    <div class="info">
        <span class="label">Server Time:</span>
        <?= htmlspecialchars($time) ?>
    </div>
</div>
</body>
</html>
