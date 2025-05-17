<?php
session_start();
require_once '../includes/db.php';

// Check authorization
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Get user ID
$username = $_SESSION['username'];
$db = getDbConnection();
$stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bindValue(1, $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = 'User information not found.';
    header('Location: profile.php');
    exit;
}

$userId = $user['id'];

// Get order history
$stmt = $db->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC');
$stmt->bindValue(1, $userId, SQLITE3_INTEGER);
$result = $stmt->execute();

$orders = [];
while ($order = $result->fetchArray(SQLITE3_ASSOC)) {
    // Get order items
    $stmt2 = $db->prepare('
        SELECT oi.*, p.name, p.icon 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = ?
    ');
    $stmt2->bindValue(1, $order['id'], SQLITE3_INTEGER);
    $itemsResult = $stmt2->execute();

    $items = [];
    while ($item = $itemsResult->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $item;
    }

    $order['items'] = $items;
    $orders[] = $order;
}

// Set page title
$pageTitle = 'Order History - Web Store';

// Additional styles for this page
$additionalStyles = '
    .orders-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .orders-empty {
        text-align: center;
        padding: 30px;
    }
    
    .order {
        margin-bottom: 30px;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .order-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    
    .order-id {
        font-weight: bold;
        color: #4CAF50;
    }
    
    .order-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    
    .order-table th, .order-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    .order-table th {
        background-color: #f2f2f2;
    }
    
    .product-icon {
        margin-right: 5px;
    }
    
    .message {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
    }
    
    .error-message {
        background-color: #ffebee;
        color: #f44336;
    }
    
    .back-link {
        margin-top: 20px;
        text-align: center;
    }
';

// Include header file
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="orders-container">
            <h2>Order History</h2>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="message error-message">
                    <?php
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="orders-empty">
                    <p>You haven't placed any orders yet.</p>
                    <p>Go to <a href="products.php">Products</a> to start shopping.</p>
                </div>
            <?php else: ?>
                <?php
                // Counter for user-specific order number
                $orderCount = count($orders);
                ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order">
                        <div class="order-header">
                            <div>
                                <span class="order-id">Order #<?php echo $orderCount--; ?></span>
                            </div>
                            <div>
                                <span><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
                            </div>
                        </div>

                        <div>
                            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                            <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                        </div>

                        <table class="order-table">
                            <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><span class="product-icon"><?php echo $item['icon']; ?></span> <?php echo $item['name']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="back-link">
                <a href="profile.php" class="btn">Back to Profile</a>
            </div>
        </div>
    </div>

<?php
// Include footer file
include '../includes/footer.php';
?>