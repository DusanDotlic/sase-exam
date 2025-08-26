<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env once
$root = dirname(__DIR__); // project root
$dotenv = Dotenv::createImmutable($root);
$dotenv->safeLoad();

function json_ok(array $data = [], int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_SLASHES);
  exit;
}
function json_err(int $code, string $msg, array $extra = []): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error'=>$msg] + $extra, JSON_UNESCAPED_SLASHES);
  exit;
}

$GLOBALS['cfg'] = [
  'jwt_secret'       => $_ENV['JWT_SECRET'] ?? 'dev-secret',
  'jwt_iss'          => $_ENV['JWT_ISS'] ?? 'https://localhost',
  'jwt_aud'          => $_ENV['JWT_AUD'] ?? 'https://localhost',
  'jwt_access_ttl'   => (int)($_ENV['JWT_ACCESS_TTL'] ?? 900),       # 900 = 15 minutes (30sec for testing)
  'jwt_refresh_ttl'  => (int)($_ENV['JWT_REFRESH_TTL'] ?? 1209600),  // 14 days
  'db_host'          => $_ENV['DB_HOST'] ?? '127.0.0.1',
  'db_name'          => $_ENV['DB_NAME'] ?? 'sase_exam',
  'db_user'          => $_ENV['DB_USER'] ?? 'root',
  'db_pass'          => $_ENV['DB_PASS'] ?? '',
];

function cfg(string $k) { return $GLOBALS['cfg'][$k] ?? null; }

/** Simple shared PDO */
function pdo(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', cfg('db_host'), cfg('db_name'));
  $pdo = new PDO($dsn, cfg('db_user'), cfg('db_pass'), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
