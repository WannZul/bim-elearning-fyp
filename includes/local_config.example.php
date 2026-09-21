<?php

/**
 * Copy this file to includes/local_config.php on the hosting server.
 * Never commit the copied file because it contains production secrets.
 */
return [
    'BIM_APP_ENV' => 'production',
    'BIM_DB_HOST' => 'localhost',
    'BIM_DB_PORT' => '3306',
    'BIM_DB_NAME' => 'your_database_name',
    'BIM_DB_USER' => 'your_limited_database_user',
    'BIM_DB_PASS' => 'replace_with_a_strong_password',

    // Set to 1 only when HTTPS is terminated by a trusted reverse proxy.
    'BIM_TRUST_PROXY' => '0',
    'BIM_COOKIE_PATH' => '/',
    'BIM_COOKIE_DOMAIN' => '',
    'BIM_SESSION_NAME' => 'BIMBOLEHSESSID',
    'BIM_SESSION_IDLE_SECONDS' => '1800',
    'BIM_SESSION_ABSOLUTE_SECONDS' => '28800',
    'BIM_SESSION_ROTATE_SECONDS' => '900',

    'BIM_LOGIN_MAX_ATTEMPTS' => '5',
    'BIM_LOGIN_WINDOW_SECONDS' => '900',
    'BIM_LOGIN_BLOCK_SECONDS' => '900',
    'BIM_ENABLE_DIAGNOSTICS' => '0',
];
