<?php
session_start();
require_once '../includes/db.php';

// Set page title
$pageTitle = 'Cart - Web Store';

// Additional styles for this page
$additionalStyles = '
    .cart-container {
        width: 100%;
        max-width: 800px;
    }
    
    .cart-empty {
        text-align: center;
        padding: 30px;
    }
    
    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .cart-table th, .cart-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    .cart-table th {
        background-color: #f2f2f2;
    }
    
    .cart-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    
    .product-icon {
        margin-right: 5px;
    }
    
    .cart-table .actions {
        width: 100px;
        text-align: center;
    }
';

// Include header file
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="cart-container">
            <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
                <div class="cart-empty">
                    <h2>Your cart is empty</h2>
                    <p>Go to <a href="products.php">Products</a> to add items to your cart.</p>
                </div>
            <?php else: ?>
                <h2>Shopping Cart</h2>
                <table class="cart-table">
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th class="actions">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $totalSum = 0;
                    foreach ($_SESSION['cart'] as $index => $item):
                        $itemTotal = $item['price'] * $item['count'];
                        $totalSum += $itemTotal;
                        ?>
                        <tr>
                            <td><span class="product-icon"><?php echo $item['icon']; ?></span> <?php echo $item['name']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['count']; ?></td>
                            <td>$<?php echo number_format($itemTotal, 2); ?></td>
                            <td class="actions">
                                <a href="../actions/remove_item.php?index=<?php echo $index; ?>" class="btn btn-delete">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($totalSum, 2); ?></strong></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>

                <div class="cart-actions">
                    <a href="products.php" class="btn">Continue Shopping</a>
                    <a href="../actions/clear_cart.php" class="btn btn-delete">Clear Cart</a>
                    <a href="checkout.php" class="btn btn-checkout">Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php
// Include footer file
include '../includes/footer.php';
?>