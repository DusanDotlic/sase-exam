<?php
declare(strict_types=1);

namespace Lib\Auth;

use PDO;
use DateTimeImmutable;

class RefreshTokenRepo
{
    /** Issue a new refresh token for user */
    public static function issue(string $userId, int $ttlSeconds): array
    {
        $raw  = bin2hex(random_bytes(32)); // 64 hex chars
        $hash = hash('sha256', $raw);
        $exp  = (new DateTimeImmutable("+{$ttlSeconds} seconds"))->format('Y-m-d H:i:s');

        $sql = "INSERT INTO refresh_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)";
        pdo()->prepare($sql)->execute([$userId, $hash, $exp]);

        return ['raw' => $raw, 'hash' => $hash, 'expires_at' => $exp];
    }

    /** Rotate an existing token: revoke old and issue a new one atomically */
    public static function rotate(string $userId, string $oldRaw, int $ttlSeconds): array
    {
        $oldHash = hash('sha256', $oldRaw);

        $sel = "SELECT id, revoked_at, expires_at FROM refresh_tokens
                WHERE user_id=? AND token_hash=? LIMIT 1";
        $stmt = pdo()->prepare($sel);
        $stmt->execute([$userId, $oldHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['revoked_at'] !== null || strtotime($row['expires_at']) <= time()) {
            throw new \RuntimeException('Invalid or expired refresh token');
        }

        pdo()->prepare("UPDATE refresh_tokens SET revoked_at=NOW() WHERE id=?")->execute([$row['id']]);

        return self::issue($userId, $ttlSeconds);
    }

    /** Revoke all active tokens for a user */
    public static function revokeAllForUser(string $userId): void
    {
        pdo()->prepare("UPDATE refresh_tokens SET revoked_at=NOW()
                        WHERE user_id=? AND revoked_at IS NULL")->execute([$userId]);
    }
}
