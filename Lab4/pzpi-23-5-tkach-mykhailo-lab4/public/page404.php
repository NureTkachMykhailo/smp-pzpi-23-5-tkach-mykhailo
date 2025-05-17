<?php
session_start();

// Set page title
$pageTitle = '404 - Access Denied';

// Additional styles for this page
$additionalStyles = '
    .error-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 40px 20px;
        text-align: center;
    }
    
    .error-code {
        font-size: 72px;
        color: #f44336;
        margin-bottom: 20px;
    }
    
    .error-message {
        font-size: 24px;
        margin-bottom: 30px;
    }
    
    .error-description {
        margin-bottom: 30px;
        color: #666;
    }
';

// Include header file - Fix the path to use the correct location
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="error-container">
            <div class="error-code">404</div>
            <div class="error-message">Please Login First</div>
            <div class="error-description">
                <p>You need to be logged in to view this page.</p>
                <p>Please login to access the content you're looking for.</p>
            </div>

            <a href="login.php" class="btn">Login</a>
            <a href="index.php" class="btn">Go to Homepage</a>
        </div>
    </div>

<?php
// Include footer file - Fix the path to use the correct location
include '../includes/footer.php';
?>