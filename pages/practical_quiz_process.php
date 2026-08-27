<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/sign_catalog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quiz.php');
    exit;
}

$token = (string) ($_POST['attempt_token'] ?? '');
$attempt = $_SESSION['practical_attempts'][$token] ?? null;
$now = microtime(true);
$graceSeconds = 5.0;
$startedAt = is_array($attempt) ? ($attempt['started_at'] ?? null) : null;
$answerDeadline = is_array($attempt) ? ($attempt['answer_deadline'] ?? null) : null;
if (!verifyCsrf($_POST['csrf_token'] ?? null)
    || !is_array($attempt)
    || !is_numeric($startedAt)
    || !is_numeric($answerDeadline)
    || $now > (float) $answerDeadline + $graceSeconds) {
    unset($_SESSION['practical_attempts'][$token]);
    setFlash('error', 'flash.practical_invalid');
    header('Location: quiz.php');
    exit;
}

$category = (string) ($attempt['category'] ?? '');
$issuedTargets = array_values(array_filter($attempt['targets'] ?? [], 'is_string'));
$approvedTargets = array_column(cameraChallengeSigns($category), 'symbol');
$issuedUnique = array_values(array_unique($issuedTargets));
$issuedValid = in_array($category, BIM_SIGN_CATEGORIES, true)
    && count($issuedTargets) === 5
    && count($issuedUnique) === 5
    && count(array_diff($issuedTargets, $approvedTargets)) === 0;

$decodedConfirmations = json_decode((string) ($_POST['confirmations'] ?? '[]'), true);
$confirmations = is_array($decodedConfirmations) ? array_values($decodedConfirmations) : [];
$confirmationStrings = count(array_filter($confirmations, 'is_string')) === count($confirmations);
$confirmationUnique = $confirmationStrings ? array_values(array_unique($confirmations)) : [];
$confirmationPositions = [];
if ($confirmationStrings) {
    foreach ($confirmations as $confirmation) {
        $position = array_search($confirmation, $issuedTargets, true);
        if ($position !== false) $confirmationPositions[] = $position;
    }
}
$sortedConfirmationPositions = $confirmationPositions;
sort($sortedConfirmationPositions);
$confirmationsInIssuedOrder = count($confirmationPositions) === count($confirmations)
    && $confirmationPositions === $sortedConfirmationPositions;
$confirmationsValid = $confirmationStrings
    && count($confirmations) <= 5
    && count($confirmationUnique) === count($confirmations)
    && count(array_diff($confirmations, $issuedTargets)) === 0
    && $confirmationsInIssuedOrder;

if (!$issuedValid || !$confirmationsValid) {
    unset($_SESSION['practical_attempts'][$token]);
    setFlash('error', 'flash.practical_unverified');
    header('Location: quiz.php');
    exit;
}

$elapsed = min(90, max(0, (int) floor($now - (float) $startedAt)));
unset($_SESSION['practical_attempts'][$token]);

$resultToken = bin2hex(random_bytes(16));
$results = array_filter($_SESSION['practical_results'] ?? [], static fn(array $result): bool => (int) ($result['expires_at'] ?? 0) >= time());
$results[$resultToken] = [
    'category' => $category,
    'issued_targets' => $issuedTargets,
    'confirmed_targets' => $confirmations,
    'time_taken' => $elapsed,
    'expires_at' => time() + 1800,
];
$_SESSION['practical_results'] = array_slice($results, -5, null, true);
header('Location: practical_quiz_review.php?result=' . urlencode($resultToken));
exit;
