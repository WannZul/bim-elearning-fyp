<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$host = trim((string) configValue('BIM_DB_HOST', 'localhost'));
$user = trim((string) configValue('BIM_DB_USER', appIsProduction() ? '' : 'root'));
$pass = (string) configValue('BIM_DB_PASS', '');
$dbname = trim((string) configValue('BIM_DB_NAME', 'bim_elearning'));
$port = configInt('BIM_DB_PORT', 3306, 1, 65535);

if ($host === '' || $user === '' || $dbname === '' || (appIsProduction() && (strtolower($user) === 'root' || $pass === ''))) {
    error_log('BIMBoleh database configuration is missing or unsafe for production.');
    http_response_code(503);
    die(function_exists('t') ? t('errors.db_unavailable') : 'Service unavailable.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();
if (!$conn) {
    error_log('BIMBoleh could not initialize the database client.');
    http_response_code(503);
    die(function_exists('t') ? t('errors.db_unavailable') : 'Service unavailable.');
}

mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$connected = mysqli_real_connect($conn, $host, $user, $pass, $dbname, $port);
if (!$connected) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    die(function_exists('t') ? t('errors.db_unavailable') : 'Service unavailable.');
}

if (!mysqli_set_charset($conn, 'utf8mb4')) {
    error_log('Database charset configuration failed: ' . mysqli_error($conn));
    mysqli_close($conn);
    http_response_code(503);
    die(function_exists('t') ? t('errors.db_unavailable') : 'Service unavailable.');
}
