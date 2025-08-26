<?php
namespace Lib\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtService
{
    private string $secret;
    private string $issuer;
    private string $audience;
    private int $accessTtl;   
    private int $refreshTtl;  
    private string $algo = 'HS256';

    public function __construct(
        string $secret,
        string $issuer = 'https://localhost',
        string $audience = 'https://localhost',
        int $accessTtl = 900,          # 900 = 15 minutes (30sec for testing)
        int $refreshTtl = 1209600      // 14 days
    ) {
        $this->secret = $secret;
        $this->issuer = $issuer;
        $this->audience = $audience;
        $this->accessTtl = $accessTtl;
        $this->refreshTtl = $refreshTtl;
    }

    public function issueTokens(int|string $userId, array $extra = []): array
    {
        $now = time();
        $jti = bin2hex(random_bytes(16));

        $accessPayload = array_merge([
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->accessTtl,
            'sub' => (string)$userId,
            'jti' => $jti,
            'typ' => 'access'
        ], $extra);

        $refreshPayload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->refreshTtl,
            'sub' => (string)$userId,
            'jti' => $jti,
            'typ' => 'refresh'
        ];

        return [
            'access_token'  => JWT::encode($accessPayload, $this->secret, $this->algo),
            'refresh_token' => JWT::encode($refreshPayload, $this->secret, $this->algo),
            'expires_in'    => $this->accessTtl
        ];
    }

    public function verifyAccess(string $jwt): object
    {
        $decoded = JWT::decode($jwt, new Key($this->secret, $this->algo));
        if (!isset($decoded->typ) || $decoded->typ !== 'access') {
            throw new Exception('Invalid token type');
        }
        return $decoded;
    }

    public function verifyRefresh(string $jwt): object
    {
        $decoded = JWT::decode($jwt, new Key($this->secret, $this->algo));
        if (!isset($decoded->typ) || $decoded->typ !== 'refresh') {
            throw new Exception('Invalid token type');
        }
        return $decoded;
    }
}
