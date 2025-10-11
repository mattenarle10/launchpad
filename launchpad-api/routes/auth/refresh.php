<?php

/**
 * POST /auth/refresh
 * Refresh JWT token
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

$payload = Auth::requireAuth();
$newToken = Auth::generateToken($payload);

Response::success([
    'token' => $newToken,
    'expiresIn' => JWT_EXPIRATION
], 'Token refreshed');

