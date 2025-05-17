<?php
// This file should be placed in includes/db.php

/**
 * Get database connection
 * @return SQLite3 Database connection
 */
function getDbConnection() {
    static $db = null;

    if ($db === null) {
        $dbPath = dirname(__DIR__) . '/database/users.db';

        // Check if database directory exists
        $dbDir = dirname($dbPath);
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        try {
            $db = new SQLite3($dbPath);
            $db->enableExceptions(true);

            // Create tables if they don't exist
            checkTablesExist($db);
        } catch (Exception $e) {
            // Log error for debugging
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Unable to connect to database: " . $e->getMessage());
        }
    }

    return $db;
}

/**
 * Check if required tables exist
 * @param SQLite3 $db Database connection
 * @throws Exception if a required table is missing
 */
function checkTablesExist($db) {
    $requiredTables = [
        'users', 'products', 'icons', 'orders', 'order_items'
    ];

    $existingTables = [];
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $existingTables[] = $row['name'];
    }

    $missingTables = array_diff($requiredTables, $existingTables);

    if (!empty($missingTables)) {
        $missingTablesStr = implode(', ', $missingTables);
        throw new Exception("Required tables are missing: $missingTablesStr. Please run init_database.php first.");
    }
}

/**
 * Get navigation items for header
 * @return array Navigation items
 * @throws Exception If navigation can't be retrieved
 */
function getNavigationItems() {
    try {
        $db = getDbConnection();

        // Get navigation items from icons table
        $result = $db->query('SELECT name, link, icon FROM icons WHERE display_in_header = 1 ORDER BY id');

        if (!$result) {
            throw new Exception("Failed to retrieve navigation items from database: " . $db->lastErrorMsg());
        }

        $navigation = ['navigation' => []];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $navigation['navigation'][] = [
                'name' => $row['name'],
                'link' => $row['link'],
                'icon' => $row['icon']
            ];
        }

        // Check if we got any navigation items
        if (empty($navigation['navigation'])) {
            throw new Exception("No navigation items found in the database. Please check the icons table.");
        }

        return $navigation;
    } catch (Exception $e) {
        // Throw exception to be handled by caller
        throw new Exception("Error getting navigation: " . $e->getMessage());
    }
}

/**
 * Get icons by page
 * @param string $page Page name
 * @return array Icons for the page
 * @throws Exception If icons can't be retrieved
 */
if (!function_exists('getIconsByPage')) {
    function getIconsByPage($page) {
        try {
            $db = getDbConnection();

            $stmt = $db->prepare('SELECT * FROM icons WHERE page = ? ORDER BY id ASC');

            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . $db->lastErrorMsg());
            }

            $stmt->bindValue(1, $page, SQLITE3_TEXT);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Failed to execute query: " . $db->lastErrorMsg());
            }

            $icons = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $icons[] = $row;
            }

            if (empty($icons)) {
                throw new Exception("No icons found for page '$page'. Please check the icons table.");
            }

            return $icons;
        } catch (Exception $e) {
            // Throw exception to be handled by caller
            throw new Exception("Error retrieving icons for page '$page': " . $e->getMessage());
        }
    }
}

/**
 * Get all products grouped by category
 * @return array Products grouped by category
 * @throws Exception If products can't be retrieved
 */
function getAllProducts() {
    try {
        $db = getDbConnection();

        // Get distinct categories
        $result = $db->query('SELECT DISTINCT category FROM products ORDER BY category');

        if (!$result) {
            throw new Exception("Failed to retrieve product categories: " . $db->lastErrorMsg());
        }

        $categories = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $categories[] = $row['category'];
        }

        if (empty($categories)) {
            throw new Exception("No product categories found. Please check the products table.");
        }

        // Build products array
        $products = ['products' => []];

        foreach ($categories as $category) {
            $categoryData = ['category' => $category, 'items' => []];

            $stmt = $db->prepare('SELECT * FROM products WHERE category = ? ORDER BY name');

            if (!$stmt) {
                throw new Exception("Failed to prepare statement for category '$category': " . $db->lastErrorMsg());
            }

            $stmt->bindValue(1, $category, SQLITE3_TEXT);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Failed to execute query for category '$category': " . $db->lastErrorMsg());
            }

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $categoryData['items'][] = [
                    'id' => $row['product_id'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'icon' => $row['icon']
                ];
            }

            if (!empty($categoryData['items'])) {
                $products['products'][] = $categoryData;
            }
        }

        if (empty($products['products'])) {
            throw new Exception("No products found in any category. Please check the products table.");
        }

        return $products;
    } catch (Exception $e) {
        // Throw exception to be handled by caller
        throw new Exception("Error retrieving products: " . $e->getMessage());
    }
}