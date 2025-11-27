<?php
require_once 'db_connect.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = [
    'success' => false,
    'message' => '',
    'session_id' => null,
    'session_data' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and sanitize input data
        $course_id = sanitizeInput($_POST['course_id'] ?? '');
        $group_id = sanitizeInput($_POST['group_id'] ?? '');
        $professor_id = sanitizeInput($_POST['professor_id'] ?? '');
        
        // For API requests, also check JSON input
        if (empty($course_id) && empty($group_id) && empty($professor_id)) {
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input) {
                $course_id = sanitizeInput($input['course_id'] ?? '');
                $group_id = sanitizeInput($input['group_id'] ?? '');
                $professor_id = sanitizeInput($input['professor_id'] ?? '');
            }
        }

        // Validate required fields
        if (empty($course_id)) {
            throw new Exception('Course ID is required.');
        }
        
        if (empty($group_id)) {
            throw new Exception('Group ID is required.');
        }
        
        if (empty($professor_id)) {
            throw new Exception('Professor ID is required.');
        }

        // Set current date
        $current_date = date('Y-m-d');
        
        // Check if session already exists for the same course, group, and date
        $check_sql = "SELECT id, status FROM attendance_sessions 
                     WHERE course_id = ? AND group_id = ? AND date = ?";
        $check_result = executeQuery($check_sql, [$course_id, $group_id, $current_date]);
        
        if ($check_result['success'] && !empty($check_result['data'])) {
            $existing_session = $check_result['data'][0];
            throw new Exception("A session for course '$course_id', group '$group_id' already exists today (Status: {$existing_session['status']}).");
        }

        // Create new session
        $sql = "INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) 
                VALUES (?, ?, ?, ?, 'active')";
        
        $result = executeQuery($sql, [$course_id, $group_id, $current_date, $professor_id]);

        if ($result['success']) {
            $session_id = $result['insert_id'];
            
            $response['success'] = true;
            $response['session_id'] = $session_id;
            $response['message'] = 'Attendance session created successfully!';
            
            // Get the complete session data
            $get_session_sql = "SELECT * FROM attendance_sessions WHERE id = ?";
            $session_result = executeQuery($get_session_sql, [$session_id]);
            if ($session_result['success'] && !empty($session_result['data'])) {
                $response['session_data'] = $session_result['data'][0];
            }
        } else {
            throw new Exception('Failed to create attendance session: ' . $result['error']);
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>