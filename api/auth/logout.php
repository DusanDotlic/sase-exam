<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json');

// Clear cookies 
$opts = [
  'expires'  => time() - 3600,
  'path'     => '/',
  'secure'   => true,    
  'httponly' => true,
  'samesite' => 'Strict',
];
setcookie('access_token',  '', $opts);
setcookie('refresh_token', '', $opts);

echo json_encode(['message' => 'Logged out']);
