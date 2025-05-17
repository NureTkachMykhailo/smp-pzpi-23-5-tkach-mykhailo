<?php
session_start();
require_once '../includes/user_functions.php';
require_once '../includes/db.php';

// Check authorization
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Get user data and check if it exists
$user = getUserByUsername($_SESSION['username']);
if (!$user) {
    $_SESSION['error_message'] = 'User data not found. Please try logging out and back in.';
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$errorMessages = [];

// Process profile form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which form was submitted
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'profile_update') {
        // Update profile information
        $name = $_POST['name'] ?? '';
        $surname = $_POST['surname'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';
        $bio = $_POST['bio'] ?? '';

        $userData = [
            'name' => $name,
            'surname' => $surname,
            'birthdate' => $birthdate,
            'bio' => $bio
        ];

        // Process photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            try {
                $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB

                if (!in_array($fileExtension, $allowedExtensions)) {
                    $error = 'Invalid file type. Only JPEG, PNG and GIF are allowed.';
                } elseif ($_FILES['photo']['size'] > $maxFileSize) {
                    $error = 'File is too large. Maximum size is 5MB.';
                } else {
                    // Get the binary data of the uploaded image
                    $photoData = file_get_contents($_FILES['photo']['tmp_name']);
                    if ($photoData === false) {
                        throw new Exception("Could not read uploaded file data");
                    }

                    $photoType = $_FILES['photo']['type'];

                    // Store the binary data in the database
                    $userData['photo'] = $photoData;
                    $userData['photo_type'] = $photoType;
                }
            } catch (Exception $e) {
                $error = 'Error processing uploaded file: ' . $e->getMessage();
                error_log($e->getMessage());
            }
        }

        // Update user profile
        if (empty($error)) {
            try {
                if (updateUserProfile($_SESSION['username'], $userData)) {
                    $success = 'Profile updated successfully!';
                } else {
                    $error = 'Error updating profile. Please try again.';
                }
            } catch (Exception $e) {
                $error = 'Error updating profile: ' . $e->getMessage();
                error_log($e->getMessage());
            }
        }
    } elseif ($formType === 'username_update') {
        // Update username
        $newUsername = $_POST['new_username'] ?? '';

        if (empty($newUsername)) {
            $error = 'New username cannot be empty.';
        } elseif (strlen($newUsername) < 3) {
            $error = 'Username must be at least 3 characters long.';
        } else {
            if (changeUsername($user['id'], $newUsername)) {
                // Update session with new username
                $_SESSION['username'] = $newUsername;
                $success = 'Username updated successfully!';

                // Refresh user data
                $user = getUserByUsername($newUsername);
            } else {
                $error = 'Username already exists or could not be updated.';
            }
        }
    } elseif ($formType === 'password_update') {
        // Update password
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $error = 'All password fields are required.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            if (changePassword($user['id'], $currentPassword, $newPassword)) {
                $success = 'Password updated successfully!';
            } else {
                $error = 'Current password is incorrect or could not be updated.';
            }
        }
    }
}

// Try to get profile icons from database
try {
    $profileIcons = getIconsByPage('profile');

    // Organize icons by name for easier access
    $iconsByName = [];
    foreach ($profileIcons as $icon) {
        $iconsByName[$icon['name']] = [
            'icon' => $icon['icon'],
            'link' => $icon['link']
        ];
    }

    // Check for required icons
    $requiredIcons = ['User', 'Orders', 'Shop'];
    foreach ($requiredIcons as $iconName) {
        if (!isset($iconsByName[$iconName])) {
            throw new Exception("Required icon '$iconName' not found in database.");
        }
    }
} catch (Exception $e) {
    $errorMessages[] = "Icons Error: " . $e->getMessage();
}

// Set page title
$pageTitle = 'Profile - Web Store';

// Additional styles for this page
$additionalStyles = '
    .profile-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .profile-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 20px;
        background-color: #f1f1f1;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 50px;
    }
    
    .profile-info {
        flex-grow: 1;
    }
    
    .profile-name {
        font-size: 24px;
        margin-bottom: 5px;
    }
    
    .profile-username {
        color: #666;
        margin-bottom: 10px;
    }
    
    .profile-details {
        margin-top: 10px;
    }
    
    .form-section {
        margin-top: 30px;
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-sizing: border-box;
        height: 38px;
    }
    
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 10px;
        box-sizing: border-box;
        overflow-y: hidden; /* Hide vertical scrollbar */
        resize: none; /* Disable manual resizing */
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
    
    .success-message {
        background-color: #e8f5e9;
        color: #4CAF50;
    }
    
    .button-container {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    
    .logout-link {
        margin-top: 20px;
        text-align: center;
    }
    
    .user-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin: 20px 0;
        padding: 15px;
        background-color: #f0f8ff;
        border-radius: 10px;
    }
    
    .action-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
        color: #333;
        padding: 10px;
        border-radius: 10px;
        transition: background-color 0.2s;
    }
    
    .action-button:hover {
        background-color: #e3f2fd;
    }
    
    .action-icon {
        font-size: 24px;
        margin-bottom: 5px;
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
    
    .password-toggle svg {
        width: 24px;
        height: 24px;
        fill: #666;
    }
    
    .password-toggle img {
        width: 24px;
        height: 24px;
        fill: #666;
    }
    
    .tabs {
        display: flex;
        border-bottom: 1px solid #ddd;
        margin-bottom: 20px;
    }
    
    .tab {
        padding: 10px 20px;
        cursor: pointer;
        margin-right: 5px;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: 5px 5px 0 0;
    }
    
    .tab.active {
        background-color: #f9f9f9;
        border-color: #ddd;
        margin-bottom: -1px;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    input[type="file"] {
        display: block;
    }
';

// JavaScript for tab switching and password visibility
$additionalScripts = '
<script>
    // Tab switching
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        
        // Hide all tab content
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        
        // Remove active class from all tabs
        tablinks = document.getElementsByClassName("tab");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        
        // Show the current tab and add an active class to the button
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
    
    // Password visibility toggle
    function togglePasswordVisibility(passwordId, toggleId) {
        const passwordInput = document.getElementById(passwordId);
        const toggleButton = document.getElementById(toggleId);
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            // Show the open eye
            toggleButton.innerHTML = `<img src="../images/eye-open.svg" alt="Hide password">`;
        } else {
            passwordInput.type = "password";
            // Show the closed eye
            toggleButton.innerHTML = `<img src="../images/eye-closed.svg" alt="Show password">`;
        }
    }
    
    // Set first tab as active by default
    window.onload = function() {
        document.getElementsByClassName("tab")[0].click();
    };
</script>

<script>
    // Tab switching and password visibility functions...
    
    // Auto-resize textarea to fit content
    function autoResizeTextarea() {
        const textareas = document.querySelectorAll("textarea");
        
        textareas.forEach(textarea => {
            // Initial resize on page load
            adjustHeight(textarea);
            
            // Resize on input
            textarea.addEventListener("input", function() {
                adjustHeight(this);
            });
        });
        
        function adjustHeight(element) {
            // Reset height to allow shrinking if text is removed
            element.style.height = "auto";
            
            // Set height to scrollHeight to fit all content
            element.style.height = (element.scrollHeight) + "px";
        }
    }
    
    // Set first tab as active by default and initialize auto-resize
    window.onload = function() {
        document.getElementsByClassName("tab")[0].click();
        autoResizeTextarea();
    };
</script>
';

// Include header file
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="profile-container">
            <?php if (!empty($errorMessages)): ?>
                <?php foreach ($errorMessages as $errorMessage): ?>
                    <div class="message error-message">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error-message">
                    <?php
                    echo $error;
                    $error = ''; // Clear the error after displaying
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success-message">
                    <?php
                    echo $success;
                    $success = ''; // Clear the success message after displaying
                    ?>
                </div>
            <?php endif; ?>

            <div class="profile-header">
                <div class="profile-photo">
                    <?php if (!empty($user['photo'])): ?>
                        <img src="get_photo.php?username=<?php echo urlencode($user['username']); ?>" alt="Profile Photo" class="profile-photo">
                    <?php elseif (isset($iconsByName['User'])): ?>
                        <span><?php echo $iconsByName['User']['icon']; ?></span>
                    <?php else: ?>
                        <span>👤</span>
                        <div class="message error-message">User icon not found in database</div>
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <h2 class="profile-name"><?php echo htmlspecialchars(($user['name'] ?? '') . ' ' . ($user['surname'] ?? '')); ?></h2>
                    <div class="profile-username">@<?php echo htmlspecialchars($user['username'] ?? ''); ?></div>

                    <div class="profile-details">
                        <?php if (!empty($user['birthdate'])): ?>
                            <p><strong>Birth Date:</strong> <?php echo htmlspecialchars($user['birthdate']); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($user['registration_date'])): ?>
                            <p><strong>Member since:</strong> <?php echo htmlspecialchars($user['registration_date']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User Actions Section - using DB icons -->
            <?php if (isset($iconsByName)): ?>
                <div class="user-actions">
                    <?php if (isset($iconsByName['Orders'])): ?>
                        <a href="<?php echo $iconsByName['Orders']['link']; ?>" class="action-button">
                            <span class="action-icon"><?php echo $iconsByName['Orders']['icon']; ?></span>
                            <span>My Orders</span>
                        </a>
                    <?php endif; ?>

                    <?php if (isset($iconsByName['Shop'])): ?>
                        <a href="<?php echo $iconsByName['Shop']['link']; ?>" class="action-button">
                            <span class="action-icon"><?php echo $iconsByName['Shop']['icon']; ?></span>
                            <span>Shop Now</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="message error-message">
                    Cannot display action buttons: required icons not found in database.
                </div>
            <?php endif; ?>

            <?php if (!empty($user['bio'])): ?>
                <div class="profile-bio">
                    <h3>About Me</h3>
                    <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                </div>
            <?php endif; ?>

            <!-- Profile Edit Tabs -->
            <div class="tabs">
                <div class="tab" onclick="openTab(event, 'profile-tab')">Edit Profile</div>
                <div class="tab" onclick="openTab(event, 'username-tab')">Change Username</div>
                <div class="tab" onclick="openTab(event, 'password-tab')">Change Password</div>
            </div>

            <!-- Profile Edit Tab -->
            <div id="profile-tab" class="tab-content">
                <div class="form-section">
                    <h3>Edit Profile</h3>

                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="profile_update">

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="surname">Surname</label>
                            <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($user['surname'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="birthdate">Birth Date</label>
                            <input type="date" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="bio">About Me</label>
                            <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="photo">Profile Photo</label>
                            <input type="file" id="photo" name="photo">
                            <small>Max file size: 5MB. Allowed file types: JPEG, PNG, GIF.</small>
                        </div>

                        <div class="button-container">
                            <button type="submit" class="btn">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Username Change Tab -->
            <div id="username-tab" class="tab-content">
                <div class="form-section">
                    <h3>Change Username</h3>

                    <form method="POST" action="profile.php">
                        <input type="hidden" name="form_type" value="username_update">

                        <div class="form-group">
                            <label for="current_username">Current Username</label>
                            <input type="text" id="current_username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                        </div>

                        <div class="form-group">
                            <label for="new_username">New Username</label>
                            <input type="text" id="new_username" name="new_username" required>
                        </div>

                        <div class="button-container">
                            <button type="submit" class="btn">Update Username</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Change Tab -->
            <div id="password-tab" class="tab-content">
                <div class="form-section">
                    <h3>Change Password</h3>

                    <form method="POST" action="profile.php">
                        <input type="hidden" name="form_type" value="password_update">

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="current_password" name="current_password" required>
                                <button type="button" id="toggle-current" class="password-toggle" onclick="togglePasswordVisibility('current_password', 'toggle-current')">
                                    <img src="../images/eye-closed.svg" alt="Show password">
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="new_password" name="new_password" required>
                                <button type="button" id="toggle-new" class="password-toggle" onclick="togglePasswordVisibility('new_password', 'toggle-new')">
                                    <img src="../images/eye-closed.svg" alt="Show password">
                                </button>
                            </div>
                        </div>

                        <div class="button-container">
                            <button type="submit" class="btn">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="logout-link">
                <a href="logout.php" class="btn btn-delete">Logout</a>
            </div>
        </div>
    </div>

<?php echo $additionalScripts; ?>

<?php
// Include footer file
include '../includes/footer.php';
?>