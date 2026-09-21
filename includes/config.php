<?php

declare(strict_types=1);

/**
 * Read application settings from server environment variables first, then from
 * an optional untracked includes/local_config.php file for shared hosting.
 */
function localConfig(): array
{
    static $config = null;
    if ($config !== null) return $config;

    $path = __DIR__ . '/local_config.php';
    if (!is_file($path)) return $config = [];

    $loaded = require $path;
    return $config = is_array($loaded) ? $loaded : [];
}

function configValue(string $key, ?string $default = null): ?string
{
    $environmentValue = getenv($key);
    if ($environmentValue !== false) return (string) $environmentValue;

    $localValue = localConfig()[$key] ?? null;
    return is_scalar($localValue) ? (string) $localValue : $default;
}

function configBool(string $key, bool $default = false): bool
{
    $value = configValue($key);
    if ($value === null) return $default;

    return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
}

function configInt(string $key, int $default, int $minimum, int $maximum): int
{
    $value = filter_var(configValue($key), FILTER_VALIDATE_INT);
    if ($value === false || $value < $minimum || $value > $maximum) return $default;
    return $value;
}

function appEnvironment(): string
{
    static $resolved = null;
    if ($resolved !== null) return $resolved;

    $configured = configValue('BIM_APP_ENV');
    if ($configured === null || trim($configured) === '') return $resolved = 'local';

    $environment = strtolower(trim($configured));
    if (!in_array($environment, ['local', 'production', 'testing'], true)) {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_log('Invalid BIM_APP_ENV value. Expected local, production, or testing.');
        http_response_code(500);
        exit('Service unavailable.');
    }
    return $resolved = $environment;
}

function appIsProduction(): bool
{
    return appEnvironment() === 'production';
}

function requestIsSecure(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') return true;
    if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) return true;

    if (configBool('BIM_TRUST_PROXY')) {
        $forwardedProto = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
        return $forwardedProto === 'https';
    }

    return false;
}

function requestHostIsLocal(): bool
{
    $rawHost = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($rawHost === '') return true;

    $host = strtolower((string) parse_url('http://' . $rawHost, PHP_URL_HOST));
    return $host === 'localhost'
        || $host === '127.0.0.1'
        || $host === '::1'
        || str_ends_with($host, '.local')
        || str_ends_with($host, '.test');
}

if (appEnvironment() === 'local' && PHP_SAPI !== 'cli' && !requestHostIsLocal()) {
    error_log('BIMBoleh refused a public request while BIM_APP_ENV is local.');
    http_response_code(503);
    exit('Service unavailable.');
}

if (appIsProduction()) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
}
