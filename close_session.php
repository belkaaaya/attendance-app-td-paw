<?php
require_once 'db_connect.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

$response = [
    'success' => false,
    'message' => '',
    'session_id' => null,
    'session_data' => null
];

try {
    // Get session ID from various sources (POST, GET, or JSON input)
    $session_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $session_id = $_POST['session_id'] ?? null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $session_id = $_GET['session_id'] ?? null;
    }
    
    // Also check for JSON input
    if (empty($session_id)) {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['session_id'])) {
            $session_id = $input['session_id'];
        }
    }

    // Validate session ID
    if (empty($session_id)) {
        throw new Exception('Session ID is required.');
    }

    if (!is_numeric($session_id) || $session_id <= 0) {
        throw new Exception('Invalid session ID. Must be a positive integer.');
    }

    // Check if session exists and get current status
    $check_sql = "SELECT id, course_id, group_id, date, status, opened_by 
                 FROM attendance_sessions 
                 WHERE id = ?";
    $check_result = executeQuery($check_sql, [$session_id]);
    
    if (!$check_result['success']) {
        throw new Exception('Database error while checking session: ' . $check_result['error']);
    }
    
    if (empty($check_result['data'])) {
        throw new Exception("Session with ID {$session_id} not found.");
    }
    
    $session = $check_result['data'][0];
    $current_status = $session['status'];
    
    // Check if session is already closed
    if ($current_status === 'closed') {
        throw new Exception("Session is already closed.");
    }
    
    // Update session status to closed
    $update_sql = "UPDATE attendance_sessions 
                  SET status = 'closed', updated_at = CURRENT_TIMESTAMP 
                  WHERE id = ?";
    
    $update_result = executeQuery($update_sql, [$session_id]);
    
    if (!$update_result['success']) {
        throw new Exception('Database error while updating session: ' . $update_result['error']);
    }
    
    if ($update_result['affected_rows'] === 0) {
        throw new Exception('Failed to close session.');
    }
    
    // Get the updated session data
    $get_updated_sql = "SELECT * FROM attendance_sessions WHERE id = ?";
    $updated_result = executeQuery($get_updated_sql, [$session_id]);
    
    if ($updated_result['success'] && !empty($updated_result['data'])) {
        $updated_session = $updated_result['data'][0];
        
        $response['success'] = true;
        $response['session_id'] = (int)$session_id;
        $response['session_data'] = $updated_session;
        $response['message'] = "Session successfully closed for {$session['course_id']} - {$session['group_id']} on {$session['date']}";
        
    } else {
        throw new Exception('Session closed but failed to retrieve updated data.');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>