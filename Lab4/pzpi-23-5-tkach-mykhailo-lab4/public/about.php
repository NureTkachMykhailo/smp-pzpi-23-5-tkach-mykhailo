<?php
session_start();
require_once '../includes/user_functions.php';
require_once '../includes/db.php';

// Set page title
$pageTitle = 'About Us - Web Store';

// Get about page icons from database
try {
    $aboutIcons = getIconsByPage('about');

    // Initialize icon variables
    $developerIcon = '';
    $lecturerIcon = '';
    $emailIcon = '';
    $institutionIcon = '';
    $heartIcon = '';

    // Process icons from database
    foreach ($aboutIcons as $icon) {
        switch ($icon['name']) {
            case 'Developer':
                $developerIcon = $icon['icon'];
                break;
            case 'Lecturer':
                $lecturerIcon = $icon['icon'];
                break;
            case 'Email':
                $emailIcon = $icon['icon'];
                break;
            case 'Institution':
                $institutionIcon = $icon['icon'];
                break;
            case 'Heart':
                $heartIcon = $icon['icon'];
                break;
        }
    }

    // Set default values if icons were not found
    if (empty($developerIcon)) {
        throw new Exception("Developer icon not found in database.");
    }
    if (empty($lecturerIcon)) {
        throw new Exception("Lecturer icon not found in database.");
    }
    if (empty($emailIcon)) {
        throw new Exception("Email icon not found in database.");
    }
    if (empty($institutionIcon)) {
        throw new Exception("Institution icon not found in database.");
    }

} catch (Exception $e) {
    // Store error message to display on page
    $iconError = "Error loading icons: " . $e->getMessage();
}

// Additional styles for this page
$additionalStyles = '
    .about-section {
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.6;
    }
    
    h2 {
        color: #333;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .team-section {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 30px;
        width: 100%;
    }
    
    .team-member {
        text-align: center;
        padding: 15px;
        border: 1px solid #eee;
        border-radius: 15px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .team-member:hover .easter-egg {
        opacity: 1;
        transform: translateY(0);
    }
    
    .member-avatar {
        width: 100px;
        height: 100px;
        background-color: #f5f5f5;
        border-radius: 50%;
        margin: 0 auto 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 40px;
    }
    
    .contact-info {
        margin-top: 30px;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 15px;
        width: 100%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .contact-info p {
        margin: 5px 0;
    }
    
    .easter-egg {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(255, 192, 203, 0.9);
        color: #333;
        padding: 10px;
        font-size: 14px;
        opacity: 0;
        transform: translateY(100%);
        transition: all 0.3s ease;
    }
    
    .hidden-message {
        color: transparent;
        user-select: none;
        font-size: 1px;
    }
    
    .hidden-message:hover {
        color: #f0f0f0;
        user-select: auto;
    }
    
    .rainbow-text {
        background-image: linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet);
        -webkit-background-clip: text;
        color: transparent;
        animation: rainbow 5s ease infinite;
        background-size: 400% 100%;
    }
    
    @keyframes rainbow {
        0%, 100% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
    }
    
    .error-message {
        color: #f44336;
        margin-bottom: 15px;
        padding: 10px;
        background-color: #ffebee;
        border-radius: 5px;
    }
';

// Initialize additionalScripts to prevent undefined variable error
$additionalScripts = '';

// Include header file
include '../includes/header.php';
?>

    <!-- Page content -->
    <div class="content">
        <div class="about-section">
            <h2>About Our Shop</h2>

            <?php if (isset($iconError)): ?>
                <div class="error-message"><?php echo htmlspecialchars($iconError); ?></div>
            <?php endif; ?>

            <p>Welcome to our online store! We are a student project created as part of the "Scripting Programming Languages" course at Kharkiv National University of Radio Electronics.</p>

            <p>Our mission is to provide a simple and intuitive shopping experience for our customers. We offer a variety of beverages and snacks at affordable prices.</p>

            <p>This web store was developed using PHP, HTML, and CSS, with a focus on implementing session management for the shopping cart functionality.</p>

            <h2>Our Team</h2>

            <div class="team-section">
                <div class="team-member">
                    <div class="member-avatar"><?php echo isset($developerIcon) ? $developerIcon : '👨‍💻'; ?></div>
                    <h3>Mykhailo Tkach</h3>
                    <p>Developer</p>
                    <p>Group: PZPI-23-5</p>
                    <div class="easter-egg">
                        <span class="rainbow-text">UwU Programming Power!</span>
                    </div>
                </div>

                <div class="team-member">
                    <div class="member-avatar"><?php echo isset($lecturerIcon) ? $lecturerIcon : '👨‍🏫'; ?></div>
                    <h3>Ihor Sokorchuk</h3>
                    <p>Senior Lecturer</p>
                    <p>Software Engineering Department</p>
                    <div class="easter-egg">
                        <span>The guy who knows his PHP. Probably.</span>
                    </div>
                </div>
            </div>

            <div class="contact-info">
                <h3>Contact Information</h3>
                <p><?php echo isset($emailIcon) ? $emailIcon : '📧'; ?> Email: mykhailo.tkach@nure.ua</p>
                <p><?php echo isset($institutionIcon) ? $institutionIcon : '🏫'; ?> Institution: Kharkiv National University of Radio Electronics</p>
                <p class="hidden-message"><?php echo isset($heartIcon) ? $heartIcon : '💖'; ?> Femboys rule the coding world!</p>
            </div>
        </div>
    </div>

<?php if (!empty($additionalScripts)) echo $additionalScripts; ?>

<?php
// Include footer file
include '../includes/footer.php';
?>