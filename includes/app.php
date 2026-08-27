<?php

declare(strict_types=1);

const BIM_LOCALES = ['ms', 'en', 'zh-Hans', 'ta'];
const BIM_DEFAULT_LOCALE = 'ms';
const BIM_LOCALE_COOKIE = 'bim_locale';

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

function supportedLocale(?string $locale): ?string
{
    return is_string($locale) && in_array($locale, BIM_LOCALES, true) ? $locale : null;
}

function currentLocale(): string
{
    static $resolved = null;

    if ($resolved !== null) {
        return $resolved;
    }

    $sessionLocale = supportedLocale($_SESSION['locale'] ?? null);
    $cookieLocale = supportedLocale($_COOKIE[BIM_LOCALE_COOKIE] ?? null);
    $resolved = $sessionLocale ?? $cookieLocale ?? BIM_DEFAULT_LOCALE;
    $_SESSION['locale'] = $resolved;

    return $resolved;
}

function localeCatalog(string $locale): array
{
    static $catalogs = [];
    $locale = supportedLocale($locale) ?? BIM_DEFAULT_LOCALE;

    if (!isset($catalogs[$locale])) {
        $catalog = require __DIR__ . '/../locales/' . $locale . '.php';
        $catalogs[$locale] = is_array($catalog) ? $catalog : [];
    }

    return $catalogs[$locale];
}

function catalogValue(array $catalog, string $key): mixed
{
    $value = $catalog;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }
        $value = $value[$segment];
    }

    return $value;
}

function t(string $key, array $params = []): string
{
    $value = catalogValue(localeCatalog(currentLocale()), $key);
    if (!is_string($value)) {
        $value = catalogValue(localeCatalog(BIM_DEFAULT_LOCALE), $key);
    }
    if (!is_string($value)) {
        return $key;
    }

    $replacements = [];
    foreach ($params as $name => $replacement) {
        $replacements[':' . $name] = (string) $replacement;
    }

    return strtr($value, $replacements);
}

function localeOptions(): array
{
    return [
        'ms' => t('locale.ms'),
        'en' => t('locale.en'),
        'zh-Hans' => t('locale.zh-Hans'),
        'ta' => t('locale.ta'),
    ];
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
        setFlash('info', 'flash.login_required');
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

function applyCameraSecurityHeaders(): void
{
    if (headers_sent()) return;

    header('Permissions-Policy: camera=(self)');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'; script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' https://cdn.jsdelivr.net; connect-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' data: blob:; media-src 'self' blob:; worker-src 'self' blob:");
}

function setFlash(string $type, string $messageKey, array $params = []): void
{
    $_SESSION['flash'] = ['type' => $type, 'message_key' => $messageKey, 'params' => $params];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function flashMessage(array $flash): string
{
    if (isset($flash['message_key']) && is_string($flash['message_key'])) {
        return t($flash['message_key'], is_array($flash['params'] ?? null) ? $flash['params'] : []);
    }

    return is_string($flash['message'] ?? null) ? $flash['message'] : '';
}

function safeReturnTo(?string $returnTo, string $fallback): string
{
    if (!is_string($returnTo) || $returnTo === '' || preg_match('/[\\x00-\\x1F\\x7F\\\\]/', $returnTo)) {
        return $fallback;
    }

    $parts = parse_url($returnTo);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['port'])) {
        return $fallback;
    }

    $path = (string) ($parts['path'] ?? '');
    if ($path === '' || str_starts_with($path, '//')) {
        return $fallback;
    }

    return $returnTo;
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

function localizedDate(string $value, bool $withTime = false): string
{
    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    $months = t('dates.months');
    $monthNames = explode('|', $months);
    $month = $monthNames[(int) date('n', $timestamp) - 1] ?? date('M', $timestamp);
    if (currentLocale() === 'zh-Hans') {
        $date = date('Y年n月d日', $timestamp);
    } else {
        $date = date('d', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp);
    }

    return $withTime ? $date . ' ' . date('H:i', $timestamp) : $date;
}

function scoreLabel(int $score, int $maximum = 50): string
{
    $percentage = $maximum > 0 ? ($score / $maximum) * 100 : 0;

    if ($percentage >= 80) return t('score.excellent');
    if ($percentage >= 60) return t('score.good');
    if ($percentage >= 40) return t('score.keep_going');
    return t('score.try_again');
}

function clientTranslations(array $keys): array
{
    $messages = [];
    foreach (array_unique($keys) as $key) {
        $messages[$key] = t($key);
    }
    return $messages;
}
