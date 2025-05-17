<?php
/**
 * Database Initialization Script
 *
 * This script initializes the complete database structure for the Web Store application,
 * including creating tables and importing data from JSON files.
 */

echo "<h1>Database Initialization Tool</h1>";

// Create database directory if it doesn't exist
$databaseDir = __DIR__ . '/database';
if (!file_exists($databaseDir)) {
    if (!mkdir($databaseDir, 0777, true)) {
        echo "<p>Error: Unable to create database directory at: $databaseDir</p>";
        echo "<p>Please create this directory manually and make sure it's writable.</p>";
        exit;
    }
}

// Check if database directory is writable
if (!is_writable($databaseDir)) {
    echo "<p>Error: Database directory is not writable: $databaseDir</p>";
    echo "<p>Please check permissions on this directory.</p>";
    exit;
}

// Define the database file path
$dbFile = $databaseDir . '/users.db';

try {
    // Connect to database directly in this script
    $db = new SQLite3($dbFile);
    $db->enableExceptions(true);

    echo "<h2>Successfully connected to database</h2>";

    // Drop existing tables if the reset parameter is set
    if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
        echo "<p>Reset parameter detected. Dropping existing tables...</p>";
        $db->exec('DROP TABLE IF EXISTS order_items');
        $db->exec('DROP TABLE IF EXISTS orders');
        $db->exec('DROP TABLE IF EXISTS users');
        $db->exec('DROP TABLE IF EXISTS products');
        $db->exec('DROP TABLE IF EXISTS icons');
        $db->exec('DROP TABLE IF EXISTS navigation');
        echo "<p>Existing tables dropped successfully.</p>";
    }

    // Create tables with the complete correct structure
    echo "<h2>Creating database tables...</h2>";

    // Users table - with BLOB for photo and photo_type column
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        name TEXT,
        surname TEXT,
        birthdate TEXT,
        bio TEXT,
        photo BLOB,
        photo_type TEXT,
        registration_date TEXT
    )');

    // Products table
    $db->exec('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        price REAL NOT NULL,
        icon TEXT NOT NULL,
        category TEXT NOT NULL
    )');

    // Icons table - consolidated from both navigation and content icons
    $db->exec('CREATE TABLE IF NOT EXISTS icons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        link TEXT,
        icon TEXT NOT NULL,
        page TEXT NOT NULL,
        display_in_header INTEGER DEFAULT 0
    )');

    // Orders table
    $db->exec('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        order_date TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "pending",
        total_amount REAL NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )');

    // Order items table
    $db->exec('CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        price REAL NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    )');

    echo "<p>Database tables created successfully!</p>";

    // Import initial data section
    echo "<h2>Checking and importing data...</h2>";

    // Function to check if specific required data exists
    function dataExists($db, $table, $condition = null) {
        $query = "SELECT COUNT(*) as count FROM $table";
        if ($condition) {
            $query .= " WHERE $condition";
        }
        $result = $db->query($query);
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row['count'] > 0;
    }

    // Check products
    $productsExist = dataExists($db, 'products');
    if (!$productsExist || isset($_GET['reset'])) {
        echo "<p>Importing products data...</p>";
        // Load products from JSON
        $productsFile = __DIR__ . '/products.json';
        if (file_exists($productsFile)) {
            $productsData = file_get_contents($productsFile);
            $products = json_decode($productsData, true);

            if ($products && isset($products['products'])) {
                $stmt = $db->prepare('INSERT INTO products (product_id, name, price, icon, category) VALUES (?, ?, ?, ?, ?)');
                $importedCount = 0;

                foreach ($products['products'] as $category) {
                    $categoryName = $category['category'];

                    foreach ($category['items'] as $item) {
                        $stmt->reset();
                        $stmt->bindValue(1, $item['id'], SQLITE3_TEXT);
                        $stmt->bindValue(2, $item['name'], SQLITE3_TEXT);
                        $stmt->bindValue(3, $item['price'], SQLITE3_FLOAT);
                        $stmt->bindValue(4, $item['icon'], SQLITE3_TEXT);
                        $stmt->bindValue(5, $categoryName, SQLITE3_TEXT);
                        $stmt->execute();
                        $importedCount++;
                    }
                }

                echo "<p>Successfully imported $importedCount products into the database.</p>";
            } else {
                echo "<p>Error: Invalid JSON format in products.json</p>";
            }
        } else {
            echo "<p>Warning: products.json file not found at: $productsFile</p>";
        }
    } else {
        echo "<p>Products data already exists in the database. Skipping import.</p>";
    }

    // Check icons - check both general existence and specific required icons
    $iconsExist = dataExists($db, 'icons');
    $requiredIcons = ['Developer', 'Lecturer', 'Email', 'Institution', 'Heart', 'Home', 'Products', 'Cart', 'Profile', 'Login', 'Signup', 'User', 'Orders', 'Shop'];
    $missingIcons = [];

    if ($iconsExist && !isset($_GET['reset'])) {
        // Check for missing required icons
        foreach ($requiredIcons as $icon) {
            if (!dataExists($db, 'icons', "name = '" . SQLite3::escapeString($icon) . "'")) {
                $missingIcons[] = $icon;
            }
        }

        if (empty($missingIcons)) {
            echo "<p>All required icons exist in the database. Skipping import.</p>";
        } else {
            echo "<p>Missing required icons: " . implode(', ', $missingIcons) . ". Will import all icons.</p>";
            // Clear existing icons to avoid duplicates with different properties
            $db->exec('DELETE FROM icons');
            $iconsExist = false;
        }
    }

    if (!$iconsExist || isset($_GET['reset'])) {
        echo "<p>Importing icons data...</p>";
        // Load icons from JSON
        $iconsFile = __DIR__ . '/icons.json';
        if (file_exists($iconsFile)) {
            $iconsData = file_get_contents($iconsFile);
            $iconsJson = json_decode($iconsData, true);

            if ($iconsJson && isset($iconsJson['icons'])) {
                $stmt = $db->prepare('INSERT INTO icons (name, link, icon, page, display_in_header) VALUES (?, ?, ?, ?, ?)');
                $importedCount = 0;

                foreach ($iconsJson['icons'] as $icon) {
                    $stmt->reset();
                    $stmt->bindValue(1, $icon['name'], SQLITE3_TEXT);
                    $stmt->bindValue(2, $icon['link'], SQLITE3_TEXT);
                    $stmt->bindValue(3, $icon['icon'], SQLITE3_TEXT);
                    $stmt->bindValue(4, $icon['page'], SQLITE3_TEXT);
                    $stmt->bindValue(5, $icon['display_in_header'], SQLITE3_INTEGER);
                    $stmt->execute();
                    $importedCount++;
                }

                echo "<p>Successfully imported $importedCount icons into the database.</p>";

                // Verify all required icons are now imported
                $stillMissing = [];
                foreach ($requiredIcons as $icon) {
                    if (!dataExists($db, 'icons', "name = '" . SQLite3::escapeString($icon) . "'")) {
                        $stillMissing[] = $icon;
                    }
                }

                if (!empty($stillMissing)) {
                    echo "<p>Warning: Some required icons are still missing after import: " . implode(', ', $stillMissing) . "</p>";
                    echo "<p>Please check your icons.json file to ensure all required icons are defined.</p>";
                }
            } else {
                echo "<p>Error: Invalid JSON format in icons.json</p>";
            }
        } else {
            echo "<p>Warning: icons.json file not found at: $iconsFile</p>";

            // Add complete set of required icons manually if JSON file not found
            echo "<p>Adding complete set of required icons manually...</p>";

            $requiredIconsData = [
                // Header menu icons
                ['name' => 'Home', 'link' => 'index.php', 'icon' => '🏠', 'page' => 'header', 'display_in_header' => 1],
                ['name' => 'Products', 'link' => 'products.php', 'icon' => '📦', 'page' => 'header', 'display_in_header' => 1],
                ['name' => 'Cart', 'link' => 'cart.php', 'icon' => '🛒', 'page' => 'header', 'display_in_header' => 1],
                ['name' => 'Profile', 'link' => 'profile.php', 'icon' => '👤', 'page' => 'header', 'display_in_header' => 1],
                ['name' => 'Login', 'link' => 'login.php', 'icon' => '🔑', 'page' => 'header', 'display_in_header' => 0],
                ['name' => 'Signup', 'link' => 'signup.php', 'icon' => '📝', 'page' => 'header', 'display_in_header' => 0],

                // Profile page icons
                ['name' => 'User', 'link' => '', 'icon' => '👤', 'page' => 'profile', 'display_in_header' => 0],
                ['name' => 'Orders', 'link' => 'orders.php', 'icon' => '📋', 'page' => 'profile', 'display_in_header' => 0],
                ['name' => 'Shop', 'link' => 'products.php', 'icon' => '🛒', 'page' => 'profile', 'display_in_header' => 0],

                // About page icons
                ['name' => 'Developer', 'link' => '', 'icon' => '👨‍💻', 'page' => 'about', 'display_in_header' => 0],
                ['name' => 'Lecturer', 'link' => '', 'icon' => '👨‍🏫', 'page' => 'about', 'display_in_header' => 0],
                ['name' => 'Email', 'link' => '', 'icon' => '📧', 'page' => 'about', 'display_in_header' => 0],
                ['name' => 'Institution', 'link' => '', 'icon' => '🏫', 'page' => 'about', 'display_in_header' => 0],
                ['name' => 'Heart', 'link' => '', 'icon' => '💖', 'page' => 'about', 'display_in_header' => 0]
            ];

            $stmt = $db->prepare('INSERT INTO icons (name, link, icon, page, display_in_header) VALUES (?, ?, ?, ?, ?)');
            $importedCount = 0;

            foreach ($requiredIconsData as $icon) {
                $stmt->reset();
                $stmt->bindValue(1, $icon['name'], SQLITE3_TEXT);
                $stmt->bindValue(2, $icon['link'], SQLITE3_TEXT);
                $stmt->bindValue(3, $icon['icon'], SQLITE3_TEXT);
                $stmt->bindValue(4, $icon['page'], SQLITE3_TEXT);
                $stmt->bindValue(5, $icon['display_in_header'], SQLITE3_INTEGER);
                $stmt->execute();
                $importedCount++;
            }

            echo "<p>Added $importedCount required icons manually.</p>";
        }
    } else {
        echo "<p>Icons data already exists in the database. Skipping import.</p>";
    }

    // Check if users table is empty and add test user if needed
    $usersExist = dataExists($db, 'users');
    if (!$usersExist || isset($_GET['reset'])) {
        // Add a test user
        $db->exec("INSERT INTO users (username, password, name, surname, registration_date) 
                 VALUES ('Test', '123123', 'Test', 'User', '".date('Y-m-d H:i:s')."')");
        echo "<p>Added test user (Username: Test, Password: 123123)</p>";
    } else {
        echo "<p>Users data already exists in the database. Skipping test user creation.</p>";
    }

    // Verification section - verify that all required data is present
    echo "<h2>Verifying database initialization...</h2>";

    // Verify products
    $productsCount = $db->querySingle("SELECT COUNT(*) FROM products");
    echo "<p>Products count: $productsCount</p>";
    if ($productsCount < 1) {
        echo "<p class='warning'>Warning: No products found in the database. The store may not function correctly.</p>";
    }

    // Verify icons
    $iconsCount = $db->querySingle("SELECT COUNT(*) FROM icons");
    echo "<p>Icons count: $iconsCount</p>";

    // Verify specific required icons
    foreach ($requiredIcons as $icon) {
        $iconExists = $db->querySingle("SELECT COUNT(*) FROM icons WHERE name = '" . SQLite3::escapeString($icon) . "'");
        echo "<p>Icon '$icon': " . ($iconExists ? "Found" : "MISSING") . "</p>";
    }

    // Verify users
    $usersCount = $db->querySingle("SELECT COUNT(*) FROM users");
    echo "<p>Users count: $usersCount</p>";

    echo "<h2>Database initialization completed successfully!</h2>";
    echo "<p>The database has been set up with all required tables and data.</p>";
    echo "<p><a href='public/index.php'>Go to homepage</a></p>";
    echo "<p><small>To reset the database, append ?reset=true to the URL of this script.</small></p>";

} catch (Exception $e) {
    echo "<h2>Error during database initialization:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";

    echo "<h3>Debugging information:</h3>";
    echo "<p>PHP SQLite3 extension loaded: " . (extension_loaded('sqlite3') ? 'Yes' : 'No') . "</p>";
    echo "<p>Database directory: $databaseDir</p>";
    echo "<p>Database file: $dbFile</p>";
    echo "<p>Database directory exists: " . (file_exists($databaseDir) ? 'Yes' : 'No') . "</p>";
    echo "<p>Database directory permissions: " . substr(sprintf('%o', fileperms($databaseDir)), -4) . "</p>";

    if (file_exists($dbFile)) {
        echo "<p>Database file exists: Yes</p>";
        echo "<p>Database file size: " . filesize($dbFile) . " bytes</p>";
        echo "<p>Database file permissions: " . substr(sprintf('%o', fileperms($dbFile)), -4) . "</p>";
    } else {
        echo "<p>Database file exists: No</p>";
    }
}
?>