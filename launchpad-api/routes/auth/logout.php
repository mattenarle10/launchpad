<?php

/**
 * POST /auth/logout
 * User logout
 */

if ($method !== 'POST') {
    Response::error('Method not allowed', 405);
}

Auth::requireAuth();
Response::success(null, 'Logout successful');

