<?php
session_start();

// Set page title
$pageTitle = 'Logout';

// Destroy the session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to index page
header('Location: index.php');
exit;
?>