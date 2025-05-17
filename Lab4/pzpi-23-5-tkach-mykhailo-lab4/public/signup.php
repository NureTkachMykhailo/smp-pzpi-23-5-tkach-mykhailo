<?php
session_start();
require_once '../includes/user_functions.php';
require_once '../includes/db.php';

// Redirect if user is already logged in
if (isset($_SESSION['username'])) {
    header('Location: products.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $surname = $_POST['surname'] ?? '';

    // Data validation
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Create user
        $userData = [
            'username' => $username,
            'password' => $password,
            'name' => $name,
            'surname' => $surname
        ];

        if (createUser($userData)) {
            $success = 'Account created successfully! You can now log in.';
        } else {
            $error = 'Username already exists';
        }
    }
}

// Set page title
$pageTitle = 'Sign Up - Web Store';

// Additional styles for this page
$additionalStyles = '
    .signup-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .signup-form {
        margin-top: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-sizing: border-box;
        height: 38px;
    }
    
    .error-message {
        color: #f44336;
        margin-bottom: 15px;
        padding: 10px;
        background-color: #ffebee;
        border-radius: 5px;
    }
    
    .success-message {
        color: #4CAF50;
        margin-bottom: 15px;
        padding: 10px;
        background-color: #e8f5e9;
        border-radius: 5px;
    }
    
    .signup-button {
        margin-top: 10px;
        width: 100%;
    }
    
    .login-link {
        text-align: center;
        margin-top: 15px;
    }
    
    .password-wrapper {
        position: relative;
        display: flex;
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        height: 38px;
    }
    
    .password-wrapper input {
        flex: 1;
        border: none;
        border-radius: 0;
        padding: 8px;
        height: 38px;
    }
    
    .password-wrapper input:focus {
        outline: none;
    }
    
    .password-toggle {
        background-color: #f1f1f1;
        border: none;
        width: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 0;
        padding: 0;
    }
    
    .password-toggle:hover {
        background-color: #e5e5e5;
    }
    
    .password-toggle:focus {
        outline: none;
    }
    
    .password-toggle img {
        width: 24px;
        height: 24px;
    }
';

// JavaScript for toggling password visibility
$additionalScripts = '
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById("password");
        const toggleButton = document.getElementById("password-toggle");
        const img = toggleButton.querySelector("img");
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            img.src = "../images/eye-open.svg";
            img.alt = "Hide password";
        } else {
            passwordInput.type = "password";
            img.src = "../images/eye-closed.svg";
            img.alt = "Show password";
        }
    }
</script>
';

// Include header file
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="signup-container">
            <h2>Create Account</h2>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="signup.php" class="signup-form">
                <div class="form-group">
                    <label for="username">Username*</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password*</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <button type="button" id="password-toggle" class="password-toggle" onclick="togglePasswordVisibility()">
                            <img src="../images/eye-closed.svg" alt="Show password" width="24" height="24">
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name">
                </div>

                <div class="form-group">
                    <label for="surname">Surname</label>
                    <input type="text" id="surname" name="surname">
                </div>

                <button type="submit" class="btn signup-button">Sign Up</button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>

<?php echo $additionalScripts; ?>

<?php
// Include footer file
include '../includes/footer.php';
?>