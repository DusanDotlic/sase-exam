<?php
declare(strict_types=1);
require __DIR__ . '/../auth_required.php';

$pdo = new PDO(
  sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", cfg('db_host'), cfg('db_name')),
  cfg('db_user'),
  cfg('db_pass'),
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$st = $pdo->prepare("SELECT id, email FROM users WHERE id=?");
$st->execute([$GLOBALS['auth_user_id']]);
$user = $st->fetch(PDO::FETCH_ASSOC);

echo json_encode(['user' => $user ?: ['id' => (int)$GLOBALS['auth_user_id']]]);
