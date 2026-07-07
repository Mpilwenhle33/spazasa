<?php
define('BASE_URL', 'https://dash.infinityfree.com/accounts/if0_42320131/domains/spazasa.freedev.app');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    ini_set('session.gc_maxlifetime', 86400);
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 0); 
