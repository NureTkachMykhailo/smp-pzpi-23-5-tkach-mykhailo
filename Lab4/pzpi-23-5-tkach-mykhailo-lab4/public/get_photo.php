<?php
// This file should be placed in the public folder
session_start();
require_once '../includes/user_functions.php';
require_once '../includes/db.php';

// Check if username is provided
if (!isset($_GET['username'])) {
    header('HTTP/1.0 400 Bad Request');
    exit('No username specified');
}

$username = $_GET['username'];

// Get user photo
$photo = getUserPhoto($username);

if ($photo) {
    // Output the image with proper content type
    header('Content-Type: ' . $photo['type']);
    echo $photo['data'];
} else {
    // Return a default image or 404
    header('HTTP/1.0 404 Not Found');
    exit('Photo not found');
}