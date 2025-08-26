<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

$in = json_decode(file_get_contents('php://input'), true) ?? [];

$name = trim($in['name'] ?? '');
$email = trim($in['email'] ?? '');
$pass  = (string)($in['password'] ?? '');

// Basic validation
if ($name === '' || mb_strlen($name) > 100) {
  http_response_code(422);
  echo json_encode(['error' => 'Please provide a valid name (max 100 chars).']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  echo json_encode(['error' => 'Please provide a valid email address.']);
  exit;
}
if ($pass === '' || mb_strlen($pass) < 8) {
  http_response_code(422);
  echo json_encode(['error' => 'Password must be at least 8 characters.']);
  exit;
}

$pdo = pdo();

// Duplicate check
$st = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$st->execute([$email]);
if ($st->fetch()) {
  http_response_code(409);
  echo json_encode(['error' => 'Email already exists']);
  exit;
}

// Create user
$hash = password_hash($pass, PASSWORD_BCRYPT);
$ins = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
$ins->execute([$name, $email, $hash]);

echo json_encode(['message' => 'Account created', 'data' => ['name' => $name, 'email' => $email]]);
