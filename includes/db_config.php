<?php
// db_config.php

// MySQL database configuration
//$host = 'localhost';          // Replace with your MySQL host
//$dbname = 'majistic2k25';     // Database name
//$username = 'root';           // Replace with your MySQL username
//$password = '';               // Replace with your MySQL password

// MySQL database configuration
$host = 'srv1834.hstgr.io';          // Replace with your MySQL host
$dbname = 'u901957751_majistic2k25';     // Database name
$username = 'u901957751_majistic2k25';           // Replace with your MySQL username
$password = '0!MyCmOOd4CA';               // Replace with your MySQL password

try {
    // Establish PDO connection
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set timezone
    date_default_timezone_set('Asia/Kolkata');
} catch (PDOException $e) {
    die("MySQL connection failed: " . $e->getMessage());
}

/**
 * Helper function to generate a unique ID (optional, since MySQL uses AUTO_INCREMENT)
 * @return string A unique identifier
 */
function generateUniqueId() {
    return uniqid(); // Simple unique ID, or use MySQL AUTO_INCREMENT instead
}

/**
 * Helper function to format MySQL row for output (simplified from MongoDB version)
 * @param array $row The MySQL row
 * @return array Formatted row
 */
function formatDocument($row) {
    return $row; // No MongoDB-specific conversion needed, but kept for compatibility
}

/**
 * Helper function to count rows in a table with a filter
 * @param string $table The MySQL table name
 * @param array $filter Filter criteria (e.g., ['email' => 'test@example.com'])
 * @return int Count of matching rows
 */
function countDocuments($table, $filter = []) {
    global $db;
    $query = "SELECT COUNT(*) FROM $table";
    $params = [];
    
    if (!empty($filter)) {
        $conditions = [];
        foreach ($filter as $key => $value) {
            $conditions[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

/**
 * Helper function to paginate MySQL results
 * @param string $table The MySQL table name
 * @param array $filter Filter criteria
 * @param array $options Options for sorting, limit, etc. (e.g., ['limit' => 10, 'offset' => 0, 'sort' => 'id DESC'])
 * @return array Array of rows
 */
function paginateResults($table, $filter = [], $options = []) {
    global $db;
    $query = "SELECT * FROM $table";
    $params = [];
    
    if (!empty($filter)) {
        $conditions = [];
        foreach ($filter as $key => $value) {
            $conditions[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Sorting
    if (isset($options['sort'])) {
        $query .= " ORDER BY " . $options['sort'];
    }
    
    // Pagination
    $limit = isset($options['limit']) ? (int)$limit : 10;
    $offset = isset($options['offset']) ? (int)$offset : 0;
    $query .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Helper function to find a single row
 * @param string $table The MySQL table name
 * @param array $filter Filter criteria
 * @return array|null The row or null if not found
 */
function findDocument($table, $filter = []) {
    global $db;
    $query = "SELECT * FROM $table";
    $params = [];
    
    if (!empty($filter)) {
        $conditions = [];
        foreach ($filter as $key => $value) {
            $conditions[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $query .= " LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? formatDocument($result) : null;
}

/**
 * Helper function to insert a document in a table (MongoDB compatible function for MySQL)
 * @param string $table The MySQL table name
 * @param array $data Data to insert
 * @return bool|int The inserted ID or false if it fails
 */
function insertOne($table, $data = []) {
    global $db;
    
    if (empty($data)) {
        return false;
    }
    
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($query);
    
    try {
        $stmt->execute($data);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("MySQL insertion error: " . $e->getMessage());
        return false;
    }
}

/**
 * Helper function to update documents in a table
 * @param string $table The MySQL table name
 * @param array $filter Filter criteria
 * @param array $data Data to update
 * @return int Number of rows affected
 */
function updateOne($table, $filter = [], $data = []) {
    global $db;
    
    if (empty($filter) || empty($data)) {
        return 0;
    }
    
    $setStatements = [];
    $params = [];
    
    foreach ($data as $key => $value) {
        $setStatements[] = "$key = :set_$key";
        $params[":set_$key"] = $value;
    }
    
    $conditions = [];
    foreach ($filter as $key => $value) {
        $conditions[] = "$key = :where_$key";
        $params[":where_$key"] = $value;
    }
    
    $query = "UPDATE $table SET " . implode(', ', $setStatements) . " WHERE " . implode(' AND ', $conditions);
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    return $stmt->rowCount();
}

/**
 * Helper function to delete documents from a table
 * @param string $table The MySQL table name
 * @param array $filter Filter criteria
 * @return int Number of rows affected
 */
function deleteOne($table, $filter = []) {
    global $db;
    
    if (empty($filter)) {
        return 0;
    }
    
    $conditions = [];
    $params = [];
    
    foreach ($filter as $key => $value) {
        $conditions[] = "$key = :$key";
        $params[":$key"] = $value;
    }
    
    $query = "DELETE FROM $table WHERE " . implode(' AND ', $conditions);
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    return $stmt->rowCount();
}

/**
 * Create table function to ensure tables exist 
 * This should be run on initialization
 */
function createRequiredTables() {
    global $db;
    
    // Create registrations table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(100) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        jis_id VARCHAR(20) NOT NULL UNIQUE,
        mobile VARCHAR(15) NOT NULL,
        email VARCHAR(100) NOT NULL,
        department VARCHAR(50) NOT NULL,
        inhouse_competition VARCHAR(10) NULL,
        competition_name VARCHAR(100) NULL,
        registration_date DATETIME NOT NULL,
        payment_status VARCHAR(20) NOT NULL DEFAULT 'Not Paid',
        receipt_number VARCHAR(50) DEFAULT NULL,
        payment_updated_by VARCHAR(255) DEFAULT NULL,
        payment_update_timestamp DATETIME DEFAULT NULL,
        paid_amount DECIMAL(10, 2) DEFAULT NULL,
        ticket_generated ENUM('Yes', 'No') DEFAULT 'No',
        checkin_1 ENUM('Yes', 'No') DEFAULT 'No',
        checkin_1_timestamp DATETIME DEFAULT NULL,
        checkin_2 ENUM('Yes', 'No') DEFAULT 'No',
        checkin_2_timestamp DATETIME DEFAULT NULL,
        edited_by VARCHAR(255) DEFAULT NULL,
        edited_timestamp DATETIME DEFAULT NULL,
        UNIQUE INDEX (email),
        UNIQUE INDEX (mobile)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create alumni_registrations table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS alumni_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        alumni_name VARCHAR(100) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        jis_id VARCHAR(20) NOT NULL UNIQUE,
        mobile VARCHAR(15) NOT NULL,
        email VARCHAR(100) NOT NULL,
        department VARCHAR(50) NOT NULL,
        passout_year VARCHAR(4) NOT NULL,
        current_organization VARCHAR(100) NULL,
        registration_date DATETIME NOT NULL,
        payment_status VARCHAR(20) NOT NULL DEFAULT 'Not Paid',
        receipt_number VARCHAR(50) DEFAULT NULL,
        payment_updated_by VARCHAR(255) DEFAULT NULL,
        payment_update_timestamp DATETIME DEFAULT NULL,
        paid_amount DECIMAL(10, 2) DEFAULT NULL,
        ticket_generated ENUM('Yes', 'No') DEFAULT 'No',
        checkin_1 ENUM('Yes', 'No') DEFAULT 'No',
        checkin_1_timestamp DATETIME DEFAULT NULL,
        checkin_2 ENUM('Yes', 'No') DEFAULT 'No',
        checkin_2_timestamp DATETIME DEFAULT NULL,
        edited_by VARCHAR(255) DEFAULT NULL,
        edited_timestamp DATETIME DEFAULT NULL,
        UNIQUE INDEX (email),
        UNIQUE INDEX (mobile)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create department_coordinators table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS department_coordinators (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        department VARCHAR(50) NOT NULL,
        contact VARCHAR(15) NOT NULL,
        available_time VARCHAR(100) NULL,
        email VARCHAR(100) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (department)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create admin_users table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        mobile VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) NOT NULL,
        department VARCHAR(50) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME NULL,
        UNIQUE INDEX (username),
        INDEX (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Create app_configs table for configuration settings
    $db->exec("CREATE TABLE IF NOT EXISTS app_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        config_key VARCHAR(50) NOT NULL UNIQUE,
        config_value TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE INDEX (config_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

// Create tables on initialization
createRequiredTables();
?>