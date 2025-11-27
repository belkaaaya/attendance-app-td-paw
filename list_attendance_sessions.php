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
    // Get optional filters
    $course_filter = $_GET['course_id'] ?? '';
    $group_filter = $_GET['group_id'] ?? '';
    $date_filter = $_GET['date'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    
    // Build query
    $sql = "SELECT 
                id, course_id, group_id, date, opened_by, status, 
                created_at, updated_at 
            FROM attendance_sessions 
            WHERE 1=1";
    $params = [];
    
    if (!empty($course_filter)) {
        $sql .= " AND course_id = ?";
        $params[] = $course_filter;
    }
    
    if (!empty($group_filter)) {
        $sql .= " AND group_id = ?";
        $params[] = $group_filter;
    }
    
    if (!empty($date_filter)) {
        $sql .= " AND date = ?";
        $params[] = $date_filter;
    }
    
    if (!empty($status_filter)) {
        $sql .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY date DESC, created_at DESC";
    
    // Execute query
    $result = executeQuery($sql, $params);
    
    if ($result['success']) {
        $response['success'] = true;
        $response['sessions'] = $result['data'];
        $response['total'] = count($result['data']);
        $response['message'] = 'Attendance sessions retrieved successfully.';
        
        // Add statistics
        $response['stats'] = [
            'active' => count(array_filter($result['data'], fn($s) => $s['status'] === 'active')),
            'closed' => count(array_filter($result['data'], fn($s) => $s['status'] === 'closed')),
            'cancelled' => count(array_filter($result['data'], fn($s) => $s['status'] === 'cancelled'))
        ];
    } else {
        throw new Exception('Failed to retrieve attendance sessions: ' . $result['error']);
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>