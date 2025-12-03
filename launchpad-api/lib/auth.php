<?php

/**
 * Authentication Helper
 */

class Auth
{
    public static function verify(): ?array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        return self::decodeToken($token);
    }

    public static function requireAuth(): array
    {
        $payload = self::verify();

        if ($payload === null) {
            Response::error('Unauthorized', 401);
        }

        return $payload;
    }

    public static function requireRole(string|array $roles): array
    {
        $payload = self::requireAuth();
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        if (!in_array($payload['role'], $allowedRoles)) {
            Response::error('Forbidden: Insufficient permissions', 403);
        }

        return $payload;
    }

    public static function generateToken(array $payload): string
    {
        $header = base64_encode(json_encode(['alg' => JWT_ALGORITHM, 'typ' => 'JWT']));
        $payload['exp'] = time() + JWT_EXPIRATION;
        $payload['iat'] = time();
        $payloadEncoded = base64_encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true);
        $signatureEncoded = base64_encode($signature);

        return "$header.$payloadEncoded.$signatureEncoded";
    }

    private static function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payloadEncoded, $signature] = $parts;

        $expectedSignature = base64_encode(
            hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true)
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $payload = json_decode(base64_decode($payloadEncoded), true);

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Validate password complexity
     * Requirements: min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public static function validatePasswordComplexity(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&*...)';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function getUserId(): ?int
    {
        $payload = self::verify();
        return $payload ? ($payload['id'] ?? null) : null;
    }

    public static function getRole(): ?string
    {
        $payload = self::verify();
        return $payload ? ($payload['role'] ?? null) : null;
    }
}
