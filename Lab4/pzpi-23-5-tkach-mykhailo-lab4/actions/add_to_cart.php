<?php
session_start();
require_once '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../public/login.php');
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Get products from database
    $db = getDbConnection();
    $allProducts = [];

    $query = "SELECT product_id, name, price, icon FROM products";
    $result = $db->query($query);

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $allProducts[$row['product_id']] = [
            'name' => $row['name'],
            'price' => $row['price'],
            'icon' => $row['icon']
        ];
    }

    // Process each product
    $addedItems = 0;
    foreach ($_POST as $itemId => $count) {
        if ($count > 0 && isset($allProducts[$itemId])) {
            $count = (int)$count;
            $productInfo = $allProducts[$itemId];

            // Check if item already exists in cart
            $found = false;
            foreach ($_SESSION['cart'] as $key => $cartItem) {
                if ($cartItem['id'] === $itemId) {
                    // If item already exists, increase quantity
                    $_SESSION['cart'][$key]['count'] += $count;
                    $found = true;
                    break;
                }
            }

            // If item not found, add it
            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $itemId,
                    'name' => $productInfo['name'],
                    'price' => $productInfo['price'],
                    'icon' => $productInfo['icon'],
                    'count' => $count
                ];
            }

            $addedItems++;
        }
    }

    // Redirect to cart page
    header('Location: ../public/cart.php');
    exit;
} else {
    // If form wasn't submitted with POST method
    header('Location: ../public/products.php');
    exit;
}
?>