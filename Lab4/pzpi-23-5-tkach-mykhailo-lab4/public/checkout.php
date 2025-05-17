<?php
session_start();
require_once '../includes/db.php';

// Check if there are items in the cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Get user ID (needed for order creation)
$username = $_SESSION['username'];
$db = getDbConnection();
$stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bindValue(1, $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = 'User information not found.';
    header('Location: cart.php');
    exit;
}

$userId = $user['id'];

// Calculate order total
$totalAmount = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalAmount += $item['price'] * $item['count'];
}

// Create order record
$db->exec('BEGIN TRANSACTION');

try {
    // Insert order
    $stmt = $db->prepare('INSERT INTO orders (user_id, order_date, status, total_amount) VALUES (?, ?, ?, ?)');
    $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
    $stmt->bindValue(2, date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->bindValue(3, 'completed', SQLITE3_TEXT);
    $stmt->bindValue(4, $totalAmount, SQLITE3_FLOAT);
    $stmt->execute();

    // Get the order ID
    $orderId = $db->lastInsertRowID();

    // Insert order items
    $stmt = $db->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');

    foreach ($_SESSION['cart'] as $item) {
        $stmt->reset();
        $stmt->bindValue(1, $orderId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $item['id'], SQLITE3_TEXT);
        $stmt->bindValue(3, $item['count'], SQLITE3_INTEGER);
        $stmt->bindValue(4, $item['price'], SQLITE3_FLOAT);
        $stmt->execute();
    }

    $db->exec('COMMIT');

    // Store order ID in session for reference
    $_SESSION['last_order_id'] = $orderId;

    // Get user-specific order number (count of user's orders)
    $userOrderNumber = 0;
    try {
        $stmt = $db->prepare('SELECT COUNT(*) as count FROM orders WHERE user_id = ?');
        $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $userOrderNumber = $row['count'];
    } catch (Exception $e) {
        // Fallback to using the database ID if there's an error
        error_log("Error getting user order count: " . $e->getMessage());
        $userOrderNumber = $orderId;
    }

    // Store user order number in session
    $_SESSION['user_order_number'] = $userOrderNumber;

    // Clear the cart after successful order
    $_SESSION['cart'] = [];

    // Set success message
    $_SESSION['success_message'] = 'Your order has been successfully placed!';

} catch (Exception $e) {
    $db->exec('ROLLBACK');
    $_SESSION['error_message'] = 'Error processing your order: ' . $e->getMessage();
    header('Location: cart.php');
    exit;
}

// Set page title
$pageTitle = 'Checkout - Web Store';

// Additional styles for this page
$additionalStyles = '
    .checkout-container {
        width: 100%;
        max-width: 800px;
    }
    
    .success-message {
        text-align: center;
        padding: 30px;
        background-color: #f8f8f8;
        border-radius: 15px;
        margin-top: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .checkout-actions {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    
    .order-details {
        margin-top: 20px;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 10px;
    }
    
    .order-id {
        font-weight: bold;
        color: #4CAF50;
    }
';

// Include header file
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="checkout-container">
            <h2>Order Confirmation</h2>

            <div class="success-message">
                <h3>Thank you for your order!</h3>
                <p>Your order has been successfully placed.</p>

                <div class="order-details">
                    <p><span class="order-id">Order #<?php echo $_SESSION['user_order_number']; ?></span></p>
                    <p>Date: <?php echo date('F j, Y, g:i a'); ?></p>
                    <p>Total: $<?php echo number_format($totalAmount, 2); ?></p>
                </div>

                <p>This is a demonstration website, so no actual payment has been processed.</p>

                <div class="checkout-actions">
                    <a href="products.php" class="btn btn-checkout">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>

<?php
// Include footer file
include '../includes/footer.php';
?>