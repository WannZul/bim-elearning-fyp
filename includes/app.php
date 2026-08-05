<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isSecure,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireAuth(string $loginPath = 'login.php'): void
{
    if (!isLoggedIn()) {
        setFlash('info', 'Sila log masuk untuk meneruskan pembelajaran.');
        header('Location: ' . $loginPath);
        exit;
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function initials(string $name): string
{
    $words = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';

    foreach (array_slice($words, 0, 2) as $word) {
        $letters .= function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
    }

    return strtoupper($letters ?: 'U');
}

function formatDuration(int $seconds): string
{
    $seconds = max(0, $seconds);
    return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
}

function scoreLabel(int $score, int $maximum = 50): string
{
    $percentage = $maximum > 0 ? ($score / $maximum) * 100 : 0;

    if ($percentage >= 80) {
        return 'Cemerlang';
    }
    if ($percentage >= 60) {
        return 'Bagus';
    }
    if ($percentage >= 40) {
        return 'Teruskan usaha';
    }

    return 'Cuba lagi';
}
