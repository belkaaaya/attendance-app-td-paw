<?php
/**
 * Database Configuration File
 * 
 * This file contains all the database connection settings for the Attendance System.
 * Update these values according to your WAMP server configuration.
 */

// Error reporting for development
if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database Configuration Constants
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'attendance_sessions');
define('DB_CHARSET', 'utf8mb4');

// Data Source Name (DSN) for PDO
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

// Database Connection Options
$db_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

/**
 * Function to create database connection using MySQLi
 * @return mysqli|false Returns MySQLi connection object or false on failure
 */
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        return false;
    }
    
    // Set character set
    $conn->set_charset(DB_CHARSET);
    
    return $conn;
}

/**
 * Function to test database connection
 * @return array Returns connection status and message
 */
function testDatabaseConnection() {
    $conn = getDBConnection();
    
    if ($conn) {
        $result = [
            'success' => true,
            'message' => 'Database connection successful!',
            'host' => DB_HOST,
            'database' => DB_NAME,
            'server_info' => $conn->server_info,
            'host_info' => $conn->host_info
        ];
        $conn->close();
        return $result;
    } else {
        return [
            'success' => false,
            'message' => 'Database connection failed!',
            'error' => 'Check your database configuration in config.php'
        ];
    }
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>