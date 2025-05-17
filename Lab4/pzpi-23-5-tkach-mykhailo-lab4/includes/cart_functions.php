<?php
// Cart functions

/**
 * Get product details by ID
 * @param string $productId Product ID
 * @return array|false Product data or false if not found
 */
function getProductById($productId) {
    $db = getDbConnection();

    $stmt = $db->prepare('SELECT * FROM products WHERE product_id = :product_id');
    $stmt->bindValue(':product_id', $productId, SQLITE3_TEXT);

    $result = $stmt->execute();
    $product = $result->fetchArray(SQLITE3_ASSOC);

    if ($product) {
        return [
            'id' => $product['product_id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'icon' => $product['icon']
        ];
    }

    return false;
}

/**
 * Initialize cart if not already initialized
 */
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add item to cart
 * @param string $productId Product ID
 * @param int $quantity Quantity
 * @return bool Success status
 */
function addToCart($productId, $quantity) {
    if ($quantity <= 0) {
        return false;
    }

    $product = getProductById($productId);
    if (!$product) {
        return false;
    }

    initCart();

    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] === $productId) {
            $_SESSION['cart'][$key]['count'] += $quantity;
            $found = true;
            break;
        }
    }

    // If not found, add new item
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $productId,
            'name' => $product['name'],
            'price' => $product['price'],
            'icon' => $product['icon'],
            'count' => $quantity
        ];
    }

    return true;
}

/**
 * Remove item from cart
 * @param int $index Cart item index
 * @return bool Success status
 */
function removeCartItem($index) {
    if (!isset($_SESSION['cart'][$index])) {
        return false;
    }

    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    return true;
}

/**
 * Clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Get cart total
 * @return float Cart total
 */
function getCartTotal() {
    $total = 0;

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['count'];
        }
    }

    return $total;
}