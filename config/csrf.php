<?php

declare(strict_types=1);

/**
 * Ensures an active PHP session exists before reading or writing session tokens.
 */
function ensureSessionStarted(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Generates (if absent) and returns the current session CSRF token.
 */
function getCsrfToken(): string
{
    ensureSessionStarted();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Returns a hidden HTML input containing the CSRF token for POST forms.
 */
function csrfField(): string
{
    $token = htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Verifies a submitted CSRF token against the session token using timing-safe comparison.
 */
function verifyCsrfToken(?string $token = null): bool
{
    ensureSessionStarted();

    $submitted = $token ?? ($_POST['csrf_token'] ?? '');
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submitted) || !is_string($stored) || $submitted === '' || $stored === '') {
        return false;
    }

    return hash_equals($stored, $submitted);
}

/**
 * Blocks invalid POST requests with HTTP 403 Forbidden.
 */
function requireCsrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !verifyCsrfToken()) {
        http_response_code(403);
        exit('403 Forbidden - Invalid or missing CSRF token.');
    }
}

/**
 * Escapes dynamic output for HTML contexts to prevent XSS.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}