<?php
/**
 * Database Connection Handler
 * 
 * This file handles database connections with proper error handling and logging.
 * Uses try/catch blocks and returns connection objects.
 */

// Include the configuration
require_once 'config.php';

/**
 * Database Connection Class
 */
class DatabaseConnection {
    private $connection = null;
    private $pdo_connection = null;
    private $last_error = '';
    
    /**
     * Get MySQLi connection with error handling
     * @return mysqli|null Returns MySQLi connection or null on failure
     */
    public function getMySQLiConnection() {
        try {
            // Check if connection already exists and is valid
            if ($this->connection && $this->connection->ping()) {
                return $this->connection;
            }
            
            // Create new connection
            $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            // Check connection
            if ($this->connection->connect_error) {
                throw new Exception("MySQLi Connection failed: " . $this->connection->connect_error);
            }
            
            // Set character set
            if (!$this->connection->set_charset(DB_CHARSET)) {
                throw new Exception("Error setting character set: " . $this->connection->error);
            }
            
            return $this->connection;
            
        } catch (Exception $e) {
            $this->last_error = $e->getMessage();
            error_log("MySQLi Connection Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get the last error message
     * @return string Last error message
     */
    public function getLastError() {
        return $this->last_error;
    }
}

/**
 * Global function to get database connection
 * @return mysqli|null Returns connection object or null on failure
 */
function getDatabaseConnection() {
    static $db = null;
    
    // Create DatabaseConnection instance if it doesn't exist
    if ($db === null) {
        $db = new DatabaseConnection();
    }
    
    return $db->getMySQLiConnection();
}

/**
 * Function to execute a safe database query with error handling
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Query results
 */
function executeQuery($sql, $params = []) {
    $result = [
        'success' => false,
        'data' => null,
        'error' => '',
        'affected_rows' => 0,
        'insert_id' => 0
    ];
    
    try {
        $conn = getDatabaseConnection();
        
        if (!$conn) {
            throw new Exception("No database connection available");
        }
        
        if (empty($params)) {
            // Simple query without parameters
            $query_result = $conn->query($sql);
            if ($query_result) {
                $result['success'] = true;
                
                if ($query_result instanceof mysqli_result) {
                    $result['data'] = [];
                    while ($row = $query_result->fetch_assoc()) {
                        $result['data'][] = $row;
                    }
                    $query_result->free();
                } else {
                    $result['affected_rows'] = $conn->affected_rows;
                    $result['insert_id'] = $conn->insert_id;
                }
            } else {
                throw new Exception("MySQLi query failed: " . $conn->error);
            }
        } else {
            // Prepared statement
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("MySQLi prepare failed: " . $conn->error);
            }
            
            // Bind parameters if provided
            if (!empty($params)) {
                $types = str_repeat('s', count($params)); // Assume all strings for simplicity
                $stmt->bind_param($types, ...$params);
            }
            
            if ($stmt->execute()) {
                $result['success'] = true;
                $query_result = $stmt->get_result();
                
                if ($query_result instanceof mysqli_result) {
                    $result['data'] = [];
                    while ($row = $query_result->fetch_assoc()) {
                        $result['data'][] = $row;
                    }
                    $query_result->free();
                } else {
                    $result['affected_rows'] = $stmt->affected_rows;
                    $result['insert_id'] = $stmt->insert_id;
                }
            } else {
                throw new Exception("MySQLi execute failed: " . $stmt->error);
            }
            
            $stmt->close();
        }
        
    } catch (Exception $e) {
        $result['error'] = $e->getMessage();
        error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
    }
    
    return $result;
}
?>