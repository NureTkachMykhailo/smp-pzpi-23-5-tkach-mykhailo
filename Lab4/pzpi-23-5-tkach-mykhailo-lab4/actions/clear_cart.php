<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../public/login.php');
    exit;
}

// Clear the cart
$_SESSION['cart'] = [];

// Redirect back to cart
header('Location: ../public/cart.php');
exit;
?>