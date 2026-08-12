<?php

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bim_elearning';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    die(t('errors.db_unavailable'));
}

mysqli_set_charset($conn, 'utf8mb4');
