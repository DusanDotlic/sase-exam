<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
use Lib\Auth\JwtService;

header('Content-Type: application/json');

$accessToken = $_COOKIE['access_token'] ?? null;
if (!$accessToken) { json_err(401, 'Missing access token'); }

$jwt = new JwtService(
  cfg('jwt_secret'),
  cfg('jwt_iss'),
  cfg('jwt_aud'),
  cfg('jwt_access_ttl'),
  cfg('jwt_refresh_ttl')
);

try {
  $decoded = $jwt->verifyAccess($accessToken);
  if (!isset($decoded->sub)) throw new \Exception('No subject');
} catch (\Throwable $e) {
  json_err(401, 'Invalid or expired access token');
}

$GLOBALS['auth_user_id'] = (string)$decoded->sub;
