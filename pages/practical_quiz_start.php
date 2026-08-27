<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/sign_catalog.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function practicalStartResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? null)) {
    practicalStartResponse(403, ['ok' => false]);
}

$token = (string) ($_POST['attempt_token'] ?? '');
$attempt = $_SESSION['practical_attempts'][$token] ?? null;
if (!is_array($attempt)) {
    practicalStartResponse(404, ['ok' => false]);
}

$category = (string) ($attempt['category'] ?? '');
$targets = array_values(array_filter($attempt['targets'] ?? [], 'is_string'));
$approvedTargets = array_column(cameraChallengeSigns($category), 'symbol');
$attemptValid = in_array($category, BIM_SIGN_CATEGORIES, true)
    && count($targets) === 5
    && count(array_unique($targets)) === 5
    && count(array_diff($targets, $approvedTargets)) === 0;
if (!$attemptValid) {
    unset($_SESSION['practical_attempts'][$token]);
    practicalStartResponse(422, ['ok' => false]);
}

$now = microtime(true);
$createdAt = (float) ($attempt['created_at'] ?? 0);
$startedAt = $attempt['started_at'] ?? null;
$deadline = $attempt['answer_deadline'] ?? null;

if ($createdAt <= 0 || $createdAt + 1800.0 < $now) {
    unset($_SESSION['practical_attempts'][$token]);
    practicalStartResponse(410, ['ok' => false]);
}

if (!is_numeric($startedAt) || !is_numeric($deadline)) {
    $attempt['started_at'] = $now;
    $attempt['answer_deadline'] = $now + 90.0;
    $_SESSION['practical_attempts'][$token] = $attempt;
    $deadline = $attempt['answer_deadline'];
}

$remainingMilliseconds = max(0, (int) floor(((float) $deadline - microtime(true)) * 1000));
if ($remainingMilliseconds <= 0) {
    practicalStartResponse(410, ['ok' => false]);
}

practicalStartResponse(200, ['ok' => true, 'remainingMilliseconds' => $remainingMilliseconds]);
