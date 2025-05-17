<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../public/login.php');
    exit;
}

// Check if cart exists
if (!isset($_SESSION['cart'])) {
    header('Location: ../public/cart.php');
    exit;
}

// Get the index of the item to remove
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

// Check if index is valid
if ($index >= 0 && $index < count($_SESSION['cart'])) {
    // Remove the item
    unset($_SESSION['cart'][$index]);
    // Reindex the array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Redirect back to cart
header('Location: ../public/cart.php');
exit;
?>