<?php
declare(strict_types=1);

/*
 * IP checker logging endpoint.
 * Logs are stored OUTSIDE the public web root:
 *   /var/www/private-logs/visitors.txt
 *
 * Create that directory and grant your PHP-FPM user write access.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok' => false, 'error' => 'POST required']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$ipVersion = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
    ? 'IPv4'
    : (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? 'IPv6' : 'Unknown');

$input = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($input)) {
    $input = [];
}

$entry = [
    'time_utc' => gmdate('c'),
    'ip' => $ip,
    'ip_version' => $ipVersion,
    'user_agent' => substr((string)($input['user_agent'] ?? ''), 0, 1000),
    'language' => substr((string)($input['language'] ?? ''), 0, 100),
    'request_uri' => substr((string)($_SERVER['REQUEST_URI'] ?? ''), 0, 500),
];

// Do not trust X-Forwarded-For / X-Real-IP by default.
// REMOTE_ADDR is the connecting peer and is the safer default.

$logDir = dirname(__DIR__) . '/private-logs';
$logFile = $logDir . '/visitors.txt';

if (!is_dir($logDir) && !@mkdir($logDir, 0750, true) && !is_dir($logDir)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Log directory unavailable']);
    exit;
}

$line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
if ($line === false || file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not write visit log']);
    exit;
}

echo json_encode([
    'ok' => true,
    'ip' => $ip,
    'ip_version' => $ipVersion
], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
