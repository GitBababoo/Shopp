<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'shopp_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Shopp');
define('SITE_DESCRIPTION', 'Modern E-commerce Website');
define('SITE_URL', 'http://localhost/Shopp');

// Security
define('SECRET_KEY', 'your-secret-key-here');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Bangkok');
?>