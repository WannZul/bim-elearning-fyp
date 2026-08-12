<?php
require_once __DIR__ . '/includes/app.php';

$fallback = isLoggedIn() ? 'index.php' : 'login.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

$returnTo = safeReturnTo($_POST['return_to'] ?? null, $fallback);
if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'errors.csrf');
    header('Location: ' . $returnTo, true, 303);
    exit;
}

$locale = supportedLocale($_POST['locale'] ?? null);
if ($locale !== null) {
    $_SESSION['locale'] = $locale;
    $_COOKIE[BIM_LOCALE_COOKIE] = $locale;
    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    setcookie(BIM_LOCALE_COOKIE, $locale, [
        'expires' => time() + 31536000,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$attemptToken = (string) ($_POST['attempt_token'] ?? '');
$attempt = $_SESSION['quiz_attempts'][$attemptToken] ?? null;
if (is_array($attempt) && (float) ($attempt['answer_deadline'] ?? 0) >= microtime(true)) {
    $allowedIds = array_fill_keys(array_filter($attempt['question_ids'] ?? [], 'is_string'), true);
    $postedAnswers = is_array($_POST['quiz_answers'] ?? null) ? $_POST['quiz_answers'] : [];
    $selectedAnswers = is_array($attempt['selected_answers'] ?? null) ? $attempt['selected_answers'] : [];
    foreach ($postedAnswers as $questionId => $letter) {
        $letter = strtoupper((string) $letter);
        if (is_string($questionId) && isset($allowedIds[$questionId]) && in_array($letter, ['A', 'B', 'C', 'D'], true)) {
            $selectedAnswers[$questionId] = $letter;
        }
    }
    $_SESSION['quiz_attempts'][$attemptToken]['selected_answers'] = $selectedAnswers;
    $_SESSION['resume_quiz_attempt'] = $attemptToken;
}

header('Location: ' . $returnTo, true, 303);
exit;
