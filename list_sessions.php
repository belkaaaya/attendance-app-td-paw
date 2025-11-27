<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'sessions' => [],
    'total' => 0,
    'message' => ''
];

try {
    // Build query with professor names
    $sql = "SELECT s.*, 
                   CASE 
                     WHEN s.opened_by = 'professor_john' THEN 'Professor John'
                     WHEN s.opened_by = 'professor_smith' THEN 'Professor Smith'
                     WHEN s.opened_by = 'professor_williams' THEN 'Professor Williams'
                     WHEN s.opened_by = 'assistant_mary' THEN 'Assistant Mary'
                     ELSE s.opened_by
                   END as professor_name
            FROM attendance_sessions s 
            ORDER BY s.date DESC, s.created_at DESC";
    
    $result = executeQuery($sql);
    
    if ($result['success']) {
        $response['success'] = true;
        $response['sessions'] = $result['data'];
        $response['total'] = count($result['data']);
        $response['message'] = 'Sessions retrieved successfully.';
    } else {
        throw new Exception('Failed to retrieve sessions: ' . $result['error']);
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>