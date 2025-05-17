<?php
// This file should be placed in includes/header.php

// Check if getNavigationItems() function exists before calling it
if (!function_exists('getNavigationItems')) {
    // Include the file that contains the function
    require_once dirname(__FILE__) . '/db.php';
}

// Get navigation data from database
try {
    $navigation = getNavigationItems();
} catch (Exception $e) {
    // Display error and exit
    echo '<div style="background-color: #ffebee; color: #f44336; padding: 10px; margin: 10px; border-radius: 5px;">';
    echo '<strong>Navigation Error:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
    exit;
}

// Determine current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['username']);

// Check if page should be restricted
$restrictedPages = ['products.php', 'cart.php', 'profile.php', 'checkout.php', 'orders.php'];
if (in_array($currentPage, $restrictedPages) && !$isLoggedIn) {
    header('Location: page404.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Web Store'; ?></title>
    <link rel="stylesheet" href="styles.css">
    <?php if (isset($additionalStyles)): ?>
        <style>
            <?php echo $additionalStyles; ?>
        </style>
    <?php endif; ?>
</head>
<body>
<!-- Site header with icons from database -->
<div class="header">
    <?php
    $first = true;
    foreach ($navigation['navigation'] as $item):
        // Check if this item should be shown
        $showItem = true;
        if ((!$isLoggedIn && $item['name'] === 'Profile') ||
            (in_array($item['link'], $restrictedPages) && !$isLoggedIn && $item['name'] !== 'Profile')) {
            $showItem = false;
        }

        if ($showItem):
            if (!$first):
                ?>
                <span class="separator">|</span>
            <?php
            endif;
            $first = false;
            ?>
            <a href="<?php echo $item['link']; ?>" class="nav-item<?php echo ($currentPage == $item['link']) ? ' active' : ''; ?>">
                <span class="nav-icon"><?php echo $item['icon']; ?></span> <?php echo $item['name']; ?>
            </a>
        <?php
        endif;
    endforeach;
    ?>

    <!-- Add Login/Signup menu items if user is not logged in -->
    <?php if (!$isLoggedIn): ?>
        <?php
        try {
            // Get login and signup icons from database
            $loginIcons = [];
            $db = getDbConnection();
            $stmt = $db->prepare('SELECT name, link, icon FROM icons WHERE name IN (?, ?) AND page = ?');
            $stmt->bindValue(1, 'Login', SQLITE3_TEXT);
            $stmt->bindValue(2, 'Signup', SQLITE3_TEXT);
            $stmt->bindValue(3, 'header', SQLITE3_TEXT);
            $result = $stmt->execute();

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $loginIcons[$row['name']] = $row;
            }

            // Check if login icons were found
            if (empty($loginIcons)) {
                throw new Exception("Login/Signup icons not found in database.");
            }

            // Display login link
            if (isset($loginIcons['Login'])):
                echo '<span class="separator">|</span>';
                echo '<a href="' . $loginIcons['Login']['link'] . '" class="nav-item' . ($currentPage == 'login.php' ? ' active' : '') . '">';
                echo '<span class="nav-icon">' . $loginIcons['Login']['icon'] . '</span> Login</a>';
            endif;

            // Display signup link
            if (isset($loginIcons['Signup'])):
                echo '<span class="separator">|</span>';
                echo '<a href="' . $loginIcons['Signup']['link'] . '" class="nav-item' . ($currentPage == 'signup.php' ? ' active' : '') . '">';
                echo '<span class="nav-icon">' . $loginIcons['Signup']['icon'] . '</span> Sign Up</a>';
            endif;

        } catch (Exception $e) {
            // Display error message
            echo '<span class="separator">|</span>';
            echo '<span style="color: #f44336;">Login/Signup icons error: ' . htmlspecialchars($e->getMessage()) . '</span>';
        }
        ?>
    <?php endif; ?>
</div>