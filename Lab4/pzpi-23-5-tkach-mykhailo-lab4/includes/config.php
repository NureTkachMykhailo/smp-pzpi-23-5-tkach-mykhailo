<?php
// Site configuration
define('SITE_NAME', 'Web Store');
define('SITE_ROOT', dirname(__DIR__));
define('DB_PATH', SITE_ROOT . '/database/users.db');
define('UPLOADS_DIR', SITE_ROOT . '/uploads/');

// Error reporting (set to 0 in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session configuration
session_start();

// Time zone
date_default_timezone_set('Europe/Kiev');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0777, true);
}