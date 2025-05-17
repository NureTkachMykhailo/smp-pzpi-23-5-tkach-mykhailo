<?php
session_start();
require_once '../includes/db.php';

// Set page title
$pageTitle = 'Home - Web Store';

// Additional styles for this page
$additionalStyles = '
    .welcome {
        text-align: center;
        max-width: 800px;
    }
    
    .welcome h1 {
        margin-bottom: 20px;
    }
    
    .welcome p {
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .shop-now-btn {
        display: inline-block;
        margin-top: 20px;
    }
    
    .welcome-user {
        background-color: #e8f5e9;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
';

// Include header file
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="welcome">
            <h1>Welcome to Our Web Store</h1>

            <?php if (isset($_SESSION['username'])): ?>
                <div class="welcome-user">
                    <h3>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                    <p>We're glad to see you again. Ready to continue shopping?</p>
                </div>
            <?php else: ?>
                <p>This is a simple web store created as a student project. You need to log in to browse our products and add them to your shopping cart.</p>
            <?php endif; ?>

            <?php if (isset($_SESSION['username'])): ?>
                <a href="products.php" class="btn shop-now-btn">Shop Now</a>
            <?php else: ?>
                <a href="login.php" class="btn shop-now-btn">Login</a>
                <a href="signup.php" class="btn">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>

<?php
// Include footer file
include '../includes/footer.php';
?>