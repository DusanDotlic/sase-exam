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

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$email    = trim($input['email'] ?? '');
$password = (string)($input['password'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Email and password required']);
    exit;
}

// DB lookup
$stmt = pdo()->prepare("SELECT id, email, password_hash FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}

// Create tokens with JwtService
$jwt    = new JwtService(cfg('jwt_secret'), cfg('jwt_iss'), cfg('jwt_aud'), cfg('jwt_access_ttl'), cfg('jwt_refresh_ttl'));
$tokens = $jwt->issueTokens((string)$user['id']); // <- this returns ['access_token','refresh_token','expires_in']

// Set HttpOnly cookies
setcookie('access_token', $tokens['access_token'], [
    'expires'  => time() + (int)cfg('jwt_access_ttl'),
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

setcookie('refresh_token', $tokens['refresh_token'], [
    'expires'  => time() + (int)cfg('jwt_refresh_ttl'),
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user'    => ['id' => (int)$user['id'], 'email' => $user['email']],
]);
