<?php
declare(strict_types=1);

require __DIR__ . '/auth_required.php';

$pdo = new PDO(
    sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", cfg('db_host'), cfg('db_name')),
    cfg('db_user'),
    cfg('db_pass'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$userId = $GLOBALS['auth_user_id'];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $stmt = $pdo->prepare("SELECT id, title, content, created_at, updated_at FROM notes WHERE user_id = ?");
        $stmt->execute([$userId]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_ok(['notes' => $notes]);
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $title = trim($input['title'] ?? '');
        $content = trim($input['content'] ?? '');

        if ($title === '' || $content === '') {
            json_err(400, 'Title and content required');
        }

        $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $title, $content]);

        json_ok([
            'message' => 'Note created',
            'note_id' => $pdo->lastInsertId()
        ], 201);
        break;

    default:
        json_err(405, 'Method not allowed');
}
