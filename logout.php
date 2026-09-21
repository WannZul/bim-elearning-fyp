<?php
require_once __DIR__ . '/includes/app.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

if (!isLoggedIn() || !verifyCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit;
}

destroyCurrentSession();
header('Location: login.php', true, 303);
exit;
