<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

const BIM_DUMMY_PASSWORD_HASH = 'bim-sha384:$2y$12$T1wmRmwmXtKgIG6JyIzj.eGaopzfX7NWsQcnmFH8.BgW3ZgIrWOES';
const BIM_PASSWORD_PREHASH_PREFIX = 'bim-sha384:';

function normalizedEmail(mixed $value): string
{
    return strtolower(trim((string) $value));
}

function validAccountEmail(string $email): bool
{
    return $email !== '' && strlen($email) <= 190 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function normalizedDisplayName(mixed $value): string
{
    $name = trim((string) $value);
    return preg_replace('/\s+/u', ' ', $name) ?? '';
}

function textLength(string $value): int
{
    if (function_exists('grapheme_strlen')) {
        $length = grapheme_strlen($value);
        if ($length !== false) return $length;
    }
    if (function_exists('mb_strlen')) return mb_strlen($value, 'UTF-8');

    $length = preg_match_all('/\X/u', $value, $matches);
    return $length === false ? 0 : $length;
}

function validDisplayName(string $name): bool
{
    if ($name === '' || preg_match('//u', $name) !== 1 || preg_match('/[\p{C}]/u', $name) === 1) return false;
    $length = textLength($name);
    return $length >= 2 && $length <= 50;
}

function accountPasswordAlgorithm(): string|int
{
    return PASSWORD_DEFAULT;
}

function accountPasswordOptions(): array
{
    return PASSWORD_DEFAULT === PASSWORD_BCRYPT ? ['cost' => 12] : [];
}

function accountPasswordByteLimit(): int
{
    return 512;
}

function validAccountPassword(string $password): bool
{
    if ($password === '' || preg_match('//u', $password) !== 1) return false;
    $length = textLength($password);
    return $length >= 8 && $length <= 128 && strlen($password) <= accountPasswordByteLimit();
}

function validLoginPassword(string $password): bool
{
    return $password !== ''
        && preg_match('//u', $password) === 1
        && textLength($password) <= 128
        && strlen($password) <= 512;
}

function accountPasswordMaterial(string $password): string
{
    return base64_encode(hash('sha384', "BIMBoleh password\0" . $password, true));
}

function hashAccountPassword(string $password): string|false
{
    $hash = password_hash(accountPasswordMaterial($password), accountPasswordAlgorithm(), accountPasswordOptions());
    return is_string($hash) ? BIM_PASSWORD_PREHASH_PREFIX . $hash : false;
}

function verifyAccountPassword(string $password, string $storedHash): bool
{
    if (str_starts_with($storedHash, BIM_PASSWORD_PREHASH_PREFIX)) {
        return password_verify(accountPasswordMaterial($password), substr($storedHash, strlen(BIM_PASSWORD_PREHASH_PREFIX)));
    }

    $verified = password_verify($password, $storedHash);
    $info = password_get_info($storedHash);
    $storedCost = (int) ($info['options']['cost'] ?? 0);
    $targetCost = (int) (accountPasswordOptions()['cost'] ?? $storedCost);
    if (($info['algoName'] ?? '') === 'bcrypt' && $storedCost > 0 && $targetCost > $storedCost) {
        $totalRuns = 1 << min(6, $targetCost - $storedCost);
        for ($run = 1; $run < $totalRuns; $run++) password_verify($password, $storedHash);
    }
    return $verified;
}

function accountPasswordNeedsRehash(string $hash): bool
{
    if (!str_starts_with($hash, BIM_PASSWORD_PREHASH_PREFIX)) return true;
    return password_needs_rehash(substr($hash, strlen(BIM_PASSWORD_PREHASH_PREFIX)), accountPasswordAlgorithm(), accountPasswordOptions());
}

/** @return array<string, mixed>|false|null False means storage error; null means no account. */
function userCredentialRecord(mysqli $conn, string $email): array|false|null
{
    $stmt = mysqli_prepare($conn, 'SELECT id, username, password FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        error_log('Credential lookup preparation failed: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Credential lookup failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log('Credential result retrieval failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return is_array($user) ? $user : null;
}

function persistPasswordRehash(mysqli $conn, int $userId, string $password): bool
{
    $replacementHash = hashAccountPassword($password);
    if (!is_string($replacementHash)) {
        error_log('Password rehash generation failed.');
        return false;
    }

    $stmt = mysqli_prepare($conn, 'UPDATE users SET password = ? WHERE id = ?');
    if (!$stmt) {
        error_log('Password rehash preparation failed: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'si', $replacementHash, $userId);
    $saved = mysqli_stmt_execute($stmt);
    if (!$saved) error_log('Password rehash persistence failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    return $saved;
}

function loginAttemptKey(string $email): string
{
    $remoteAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    if ($remoteAddress === '') $remoteAddress = 'unknown';
    return hash('sha256', $email . "\0" . $remoteAddress);
}

function loginThrottleStorageReady(mysqli $conn): bool
{
    static $ready = null;
    if ($ready !== null) return $ready;

    $result = mysqli_query($conn, "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'login_attempts' LIMIT 1");
    $ready = $result instanceof mysqli_result && mysqli_num_rows($result) === 1;
    if ($result instanceof mysqli_result) mysqli_free_result($result);
    return $ready;
}

/** @return array<string, int>|false|null False means storage error; null means no row. */
function loginThrottleRecord(mysqli $conn, string $attemptKey): array|false|null
{
    $stmt = mysqli_prepare($conn, 'SELECT attempt_count, window_started_at, blocked_until FROM login_attempts WHERE attempt_key = ? LIMIT 1');
    if (!$stmt) {
        error_log('Login throttle read preparation failed: ' . mysqli_error($conn));
        return false;
    }

    mysqli_stmt_bind_param($stmt, 's', $attemptKey);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Login throttle read failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    $record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!is_array($record)) return null;
    return [
        'attempt_count' => (int) ($record['attempt_count'] ?? 0),
        'window_started_at' => (int) ($record['window_started_at'] ?? 0),
        'blocked_until' => (int) ($record['blocked_until'] ?? 0),
    ];
}

function loginThrottleStatus(mysqli $conn, string $email): array
{
    if (!loginThrottleStorageReady($conn)) {
        return ['available' => false, 'blocked' => false, 'retry_after' => 0];
    }

    $record = loginThrottleRecord($conn, loginAttemptKey($email));
    if ($record === false) return ['available' => false, 'blocked' => false, 'retry_after' => 0];

    $now = time();
    $blockedUntil = (int) ($record['blocked_until'] ?? 0);
    return [
        'available' => true,
        'blocked' => $blockedUntil > $now,
        'retry_after' => max(0, $blockedUntil - $now),
    ];
}

function reserveLoginAttempt(mysqli $conn, string $email): array
{
    if (!loginThrottleStorageReady($conn)) {
        return ['available' => false, 'allowed' => !appIsProduction(), 'retry_after' => 0];
    }

    $attemptKey = loginAttemptKey($email);
    $now = time();
    $windowSeconds = configInt('BIM_LOGIN_WINDOW_SECONDS', 900, 60, 86400);
    $blockSeconds = configInt('BIM_LOGIN_BLOCK_SECONDS', 900, 60, 86400);
    $maximumAttempts = configInt('BIM_LOGIN_MAX_ATTEMPTS', 5, 3, 20);
    $blockedCandidate = $now + $blockSeconds;

    if (!mysqli_begin_transaction($conn)) {
        error_log('Login throttle transaction could not start: ' . mysqli_error($conn));
        return ['available' => false, 'allowed' => false, 'retry_after' => 0];
    }

    // Reserve the attempt before password verification. The primary-key upsert
    // serializes concurrent requests, so no more than the configured number of
    // guesses can enter the expensive credential check in one window.
    $upsertSql = 'INSERT INTO login_attempts (attempt_key, attempt_count, window_started_at, blocked_until, updated_at) VALUES (?, 1, ?, 0, ?) '
        . 'ON DUPLICATE KEY UPDATE '
        . 'attempt_count = IF(? - window_started_at >= ?, 1, LEAST(255, attempt_count + 1)), '
        . 'window_started_at = IF(? - window_started_at >= ?, ?, window_started_at), '
        . 'updated_at = ?';
    $upsert = mysqli_prepare($conn, $upsertSql);
    if (!$upsert) {
        mysqli_rollback($conn);
        error_log('Login throttle reservation preparation failed: ' . mysqli_error($conn));
        return ['available' => false, 'allowed' => false, 'retry_after' => 0];
    }
    mysqli_stmt_bind_param(
        $upsert,
        'siiiiiiii',
        $attemptKey,
        $now,
        $now,
        $now,
        $windowSeconds,
        $now,
        $windowSeconds,
        $now,
        $now
    );
    $saved = mysqli_stmt_execute($upsert);
    if (!$saved) error_log('Login throttle reservation failed: ' . mysqli_stmt_error($upsert));
    mysqli_stmt_close($upsert);

    $block = $saved
        ? mysqli_prepare($conn, 'UPDATE login_attempts SET blocked_until = GREATEST(blocked_until, ?) WHERE attempt_key = ? AND attempt_count >= ?')
        : false;
    if ($block) {
        mysqli_stmt_bind_param($block, 'isi', $blockedCandidate, $attemptKey, $maximumAttempts);
        $saved = mysqli_stmt_execute($block);
        if (!$saved) error_log('Login throttle block write failed: ' . mysqli_stmt_error($block));
        mysqli_stmt_close($block);
    } elseif ($saved) {
        $saved = false;
        error_log('Login throttle block preparation failed: ' . mysqli_error($conn));
    }

    $record = $saved ? loginThrottleRecord($conn, $attemptKey) : false;
    if (!$saved || !is_array($record) || !mysqli_commit($conn)) {
        mysqli_rollback($conn);
        return ['available' => false, 'allowed' => false, 'retry_after' => 0];
    }

    $attemptCount = (int) $record['attempt_count'];
    $blockedUntil = (int) $record['blocked_until'];
    $allowed = $attemptCount <= $maximumAttempts
        && ($attemptCount === $maximumAttempts || $blockedUntil <= $now);

    $expiry = $now - max($windowSeconds, $blockSeconds) * 4;
    $cleanup = mysqli_prepare($conn, 'DELETE FROM login_attempts WHERE updated_at < ?');
    if ($cleanup) {
        mysqli_stmt_bind_param($cleanup, 'i', $expiry);
        mysqli_stmt_execute($cleanup);
        mysqli_stmt_close($cleanup);
    }

    return [
        'available' => true,
        'allowed' => $allowed,
        'retry_after' => $allowed ? 0 : max(1, $blockedUntil - $now),
    ];
}

function clearLoginFailures(mysqli $conn, string $email): bool
{
    if (!loginThrottleStorageReady($conn)) return false;

    $attemptKey = loginAttemptKey($email);
    $stmt = mysqli_prepare($conn, 'DELETE FROM login_attempts WHERE attempt_key = ?');
    if (!$stmt) {
        error_log('Login throttle cleanup preparation failed: ' . mysqli_error($conn));
        return false;
    }
    mysqli_stmt_bind_param($stmt, 's', $attemptKey);
    $cleared = mysqli_stmt_execute($stmt);
    if (!$cleared) error_log('Login throttle cleanup failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    return $cleared;
}
