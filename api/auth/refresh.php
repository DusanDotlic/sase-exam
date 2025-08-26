<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Lib\Auth\JwtService;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$refresh = $_COOKIE['refresh_token'] ?? '';
if ($refresh === '') {
    http_response_code(401);
    echo json_encode(['error' => 'Missing refresh token']);
    exit;
}

$jwt = new JwtService(cfg('jwt_secret'), cfg('jwt_iss'), cfg('jwt_aud'), cfg('jwt_access_ttl'), cfg('jwt_refresh_ttl'));

try {
    $decoded = $jwt->verifyRefresh($refresh);
    if (!isset($decoded->sub)) {
        throw new Exception('Invalid refresh token');
    }
    $userId = (string)$decoded->sub;
} catch (\Throwable $e) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or expired refresh token']);
    exit;
}

// Issue new pair (access + refresh) and set cookies
$new = $jwt->issueTokens($userId);

setcookie('access_token', $new['access_token'], [
    'expires'  => time() + (int)cfg('jwt_access_ttl'),
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

setcookie('refresh_token', $new['refresh_token'], [
    'expires'  => time() + (int)cfg('jwt_refresh_ttl'),
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

echo json_encode(['success' => true, 'message' => 'Token refreshed']);
