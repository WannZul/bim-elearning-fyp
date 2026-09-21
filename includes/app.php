<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

const BIM_LOCALES = ['ms', 'en', 'zh-Hans', 'ta'];
const BIM_DEFAULT_LOCALE = 'ms';
const BIM_LOCALE_COOKIE = 'bim_locale';

function cspNonce(): string
{
    static $nonce = null;
    if ($nonce === null) $nonce = base64_encode(random_bytes(18));
    return $nonce;
}

function applySecurityHeaders(bool $cameraAllowed = false): void
{
    if (headers_sent()) return;

    $scriptPolicy = "script-src 'self' 'nonce-" . cspNonce() . "' https://cdn.jsdelivr.net";
    $connectPolicy = "connect-src 'self'";
    $workerPolicy = "worker-src 'self'";
    if ($cameraAllowed) {
        $scriptPolicy .= " 'wasm-unsafe-eval'";
        $connectPolicy .= ' https://cdn.jsdelivr.net';
        $workerPolicy .= ' blob:';
    }

    $policy = implode('; ', [
        "default-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'none'",
        "object-src 'none'",
        $scriptPolicy,
        $connectPolicy,
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
        "font-src 'self' data: https://cdn.jsdelivr.net https://fonts.gstatic.com",
        "img-src 'self' data: blob: https://images.bimsignbank.org",
        "media-src 'self' blob:",
        $workerPolicy,
    ]);
    if (appIsProduction() && requestIsSecure()) $policy .= '; upgrade-insecure-requests';

    header('Content-Security-Policy: ' . $policy, true);
    header('Permissions-Policy: camera=' . ($cameraAllowed ? '(self)' : '()') . ', microphone=(), geolocation=(), payment=(), usb=()', true);
    header('Referrer-Policy: strict-origin-when-cross-origin', true);
    header('X-Content-Type-Options: nosniff', true);
    header('X-Frame-Options: DENY', true);
    header('X-Permitted-Cross-Domain-Policies: none', true);
    header('Cross-Origin-Opener-Policy: same-origin-allow-popups', true);
    header('Cache-Control: no-store, private', true);

    if (appIsProduction() && requestIsSecure()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains', true);
    }
}

function applyCameraSecurityHeaders(): void
{
    applySecurityHeaders(true);
}

function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    $sessionName = preg_replace('/[^A-Za-z0-9]/', '', (string) configValue('BIM_SESSION_NAME', 'BIMBOLEHSESSID')) ?: 'BIMBOLEHSESSID';
    session_name($sessionName);

    $cookieParams = [
        'lifetime' => 0,
        'httponly' => true,
        'secure' => requestIsSecure(),
        'samesite' => 'Lax',
        'path' => (string) configValue('BIM_COOKIE_PATH', '/'),
    ];
    $cookieDomain = trim((string) configValue('BIM_COOKIE_DOMAIN', ''));
    if ($cookieDomain !== '') $cookieParams['domain'] = $cookieDomain;

    session_set_cookie_params($cookieParams);
    session_start();
}

function enforceAuthenticatedSessionLifetime(): void
{
    if (!isset($_SESSION['user_id'])) return;

    $now = time();
    $idleLimit = configInt('BIM_SESSION_IDLE_SECONDS', 1800, 300, 86400);
    $absoluteLimit = configInt('BIM_SESSION_ABSOLUTE_SECONDS', 28800, 1800, 604800);
    $rotationLimit = configInt('BIM_SESSION_ROTATE_SECONDS', 900, 300, 3600);
    $startedAt = (int) ($_SESSION['auth_started_at'] ?? $now);
    $lastActivity = (int) ($_SESSION['last_activity_at'] ?? $now);

    if ($now - $lastActivity > $idleLimit || $now - $startedAt > $absoluteLimit) {
        $locale = supportedLocale($_SESSION['locale'] ?? null);
        $_SESSION = [];
        session_regenerate_id(true);
        if ($locale !== null) $_SESSION['locale'] = $locale;
        $_SESSION['flash'] = ['type' => 'info', 'message_key' => 'flash.session_expired', 'params' => []];
        return;
    }

    $_SESSION['auth_started_at'] = $startedAt;
    $_SESSION['last_activity_at'] = $now;
    $lastRotation = (int) ($_SESSION['session_rotated_at'] ?? $startedAt);
    if ($now - $lastRotation >= $rotationLimit) {
        session_regenerate_id(true);
        $_SESSION['session_rotated_at'] = $now;
    }
}

function beginAuthenticatedSession(int $userId, string $username): void
{
    $locale = supportedLocale($_SESSION['locale'] ?? null) ?? supportedLocale($_COOKIE[BIM_LOCALE_COOKIE] ?? null);
    $_SESSION = [];
    session_regenerate_id(true);
    if ($locale !== null) $_SESSION['locale'] = $locale;

    $now = time();
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['auth_started_at'] = $now;
    $_SESSION['last_activity_at'] = $now;
    $_SESSION['session_rotated_at'] = $now;
}

startSecureSession();
applySecurityHeaders();
enforceAuthenticatedSessionLifetime();

function supportedLocale(?string $locale): ?string
{
    return is_string($locale) && in_array($locale, BIM_LOCALES, true) ? $locale : null;
}

function currentLocale(): string
{
    static $resolved = null;

    if ($resolved !== null) return $resolved;

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
        if (!is_array($value) || !array_key_exists($segment, $value)) return null;
        $value = $value[$segment];
    }
    return $value;
}

function t(string $key, array $params = []): string
{
    $value = catalogValue(localeCatalog(currentLocale()), $key);
    if (!is_string($value)) $value = catalogValue(localeCatalog(BIM_DEFAULT_LOCALE), $key);
    if (!is_string($value)) return $key;

    $replacements = [];
    foreach ($params as $name => $replacement) $replacements[':' . $name] = (string) $replacement;
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
        if (empty($_SESSION['flash'])) setFlash('info', 'flash.login_required');
        header('Location: ' . $loginPath);
        exit;
    }
}

function csrfToken(): string
{
    if (!is_string($_SESSION['csrf_token'] ?? null) || strlen($_SESSION['csrf_token']) !== 64) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(?string $token): bool
{
    return is_string($token)
        && is_string($_SESSION['csrf_token'] ?? null)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function destroyCurrentSession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
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
    if (!is_string($returnTo) || $returnTo === '' || preg_match('/[\\x00-\\x1F\\x7F\\\\]/', $returnTo)) return $fallback;

    $parts = parse_url($returnTo);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['port'])) return $fallback;

    $path = (string) ($parts['path'] ?? '');
    if ($path === '' || str_starts_with($path, '//')) return $fallback;
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
    if ($timestamp === false) return $value;

    $monthNames = explode('|', t('dates.months'));
    $month = $monthNames[(int) date('n', $timestamp) - 1] ?? date('M', $timestamp);
    $date = currentLocale() === 'zh-Hans'
        ? date('Y年n月d日', $timestamp)
        : date('d', $timestamp) . ' ' . $month . ' ' . date('Y', $timestamp);
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
    foreach (array_unique($keys) as $key) $messages[$key] = t($key);
    return $messages;
}
