<?php
// This file should be placed in includes/user_functions.php

/**
 * User management functions
 */

/**
 * Check user credentials
 * @param string $username Username
 * @param string $password Password
 * @return array|false User data or false if invalid
 */
function checkCredentials($username, $password) {
    $db = getDbConnection();

    $stmt = $db->prepare('SELECT * FROM users WHERE username = :username AND password = :password');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':password', $password, SQLITE3_TEXT);

    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    return $user ? $user : false;
}

/**
 * Get user by username
 * @param string $username Username
 * @return array|false User data or false if not found
 */
function getUserByUsername($username) {
    try {
        $db = getDbConnection();

        $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);

        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        return $user ? $user : false;
    } catch (Exception $e) {
        error_log("Error in getUserByUsername: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user profile
 * @param string $username Username
 * @param array $data Profile data to update
 * @return bool Success status
 */
function updateUserProfile($username, $data) {
    try {
        $db = getDbConnection();

        // Build the query - filter out columns that don't exist in the database
        $query = 'UPDATE users SET ';
        $validData = [];

        // Get the actual columns in the users table
        $result = $db->query("PRAGMA table_info(users)");
        $columns = [];
        while ($column = $result->fetchArray(SQLITE3_ASSOC)) {
            $columns[] = $column['name'];
        }

        // Only include columns that exist in the database
        foreach ($data as $key => $value) {
            if (in_array($key, $columns)) {
                $validData[$key] = $value;
                $query .= "$key = :$key, ";
            } else {
                error_log("Skipping non-existent column in update: $key");
            }
        }

        if (empty($validData)) {
            error_log("No valid columns to update for user: $username");
            return false;
        }

        $query = rtrim($query, ', ');
        $query .= ' WHERE username = :username';

        // Prepare statement
        $stmt = $db->prepare($query);
        if (!$stmt) {
            error_log("Error preparing statement: " . $db->lastErrorMsg());
            return false;
        }

        // Bind values
        foreach ($validData as $key => $value) {
            if ($key === 'photo') {
                $stmt->bindValue(":$key", $value, SQLITE3_BLOB);
            } else {
                $stmt->bindValue(":$key", $value, SQLITE3_TEXT);
            }
        }
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);

        // Execute statement
        $result = $stmt->execute();
        if (!$result) {
            error_log("Error executing update: " . $db->lastErrorMsg());
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log("Error in updateUserProfile: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new user
 * @param array $data User data
 * @return bool Success status
 */
function createUser($data) {
    try {
        $db = getDbConnection();

        // Check if username already exists
        $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindValue(':username', $data['username'], SQLITE3_TEXT);
        $result = $stmt->execute();

        if ($result->fetchArray()) {
            return false; // User already exists
        }

        // Add registration date
        $data['registration_date'] = date('Y-m-d H:i:s');

        $keys = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $query = "INSERT INTO users ($keys) VALUES ($placeholders)";
        $stmt = $db->prepare($query);

        if (!$stmt) {
            error_log("Error preparing statement: " . $db->lastErrorMsg());
            return false;
        }

        foreach ($data as $key => $value) {
            if ($key === 'photo') {
                $stmt->bindValue(":$key", $value, SQLITE3_BLOB);
            } else {
                $stmt->bindValue(":$key", $value, SQLITE3_TEXT);
            }
        }

        return $stmt->execute() ? true : false;
    } catch (Exception $e) {
        error_log("Error in createUser: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user profile photo by username
 * @param string $username Username
 * @return array|false Photo data or false if not found
 */
function getUserPhoto($username) {
    try {
        $db = getDbConnection();

        // Check if photo_type column exists
        $result = $db->query("PRAGMA table_info(users)");
        $hasPhotoType = false;

        while ($column = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($column['name'] === 'photo_type') {
                $hasPhotoType = true;
                break;
            }
        }

        if ($hasPhotoType) {
            $stmt = $db->prepare('SELECT photo, photo_type FROM users WHERE username = :username');
        } else {
            $stmt = $db->prepare('SELECT photo FROM users WHERE username = :username');
        }

        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row && $row['photo']) {
            return [
                'data' => $row['photo'],
                'type' => $row['photo_type'] ?? 'image/jpeg' // Default to JPEG if column doesn't exist or is null
            ];
        }

        return false;
    } catch (Exception $e) {
        error_log("Error getting user photo: " . $e->getMessage());
        return false;
    }
}

/**
 * Get icons from database by page
 * @param string $page Page name
 * @return array Icons for the specified page
 */
function getIconsByPage($page) {
    try {
        $db = getDbConnection();

        // Check if icons table exists first
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='icons'");
        $tableExists = $result->fetchArray();

        if (!$tableExists) {
            // Return empty array if table doesn't exist yet
            return [];
        }

        $stmt = $db->prepare('SELECT * FROM icons WHERE page = ? ORDER BY id ASC');
        $stmt->bindValue(1, $page, SQLITE3_TEXT);
        $result = $stmt->execute();

        $icons = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $icons[] = $row;
        }

        return $icons;
    } catch (Exception $e) {
        error_log("Error in getIconsByPage: " . $e->getMessage());
        return [];
    }
}

/**
 * Change user username
 * @param int $userId User ID
 * @param string $newUsername New username
 * @return bool Success status
 */
function changeUsername($userId, $newUsername) {
    try {
        $db = getDbConnection();

        // Check if username already exists
        $stmt = $db->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
        $stmt->bindValue(1, $newUsername, SQLITE3_TEXT);
        $stmt->bindValue(2, $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        if ($result->fetchArray()) {
            return false; // Username already exists
        }

        // Update username
        $stmt = $db->prepare('UPDATE users SET username = ? WHERE id = ?');
        $stmt->bindValue(1, $newUsername, SQLITE3_TEXT);
        $stmt->bindValue(2, $userId, SQLITE3_INTEGER);
        $stmt->execute();

        return true;
    } catch (Exception $e) {
        error_log("Error changing username: " . $e->getMessage());
        return false;
    }
}

/**
 * Change user password
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return bool Success status
 */
function changePassword($userId, $currentPassword, $newPassword) {
    try {
        $db = getDbConnection();

        // Verify current password
        $stmt = $db->prepare('SELECT id FROM users WHERE id = ? AND password = ?');
        $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
        $stmt->bindValue(2, $currentPassword, SQLITE3_TEXT);
        $result = $stmt->execute();

        if (!$result->fetchArray()) {
            return false; // Current password is incorrect
        }

        // Update password
        $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bindValue(1, $newPassword, SQLITE3_TEXT);
        $stmt->bindValue(2, $userId, SQLITE3_INTEGER);
        $stmt->execute();

        return true;
    } catch (Exception $e) {
        error_log("Error changing password: " . $e->getMessage());
        return false;
    }
}