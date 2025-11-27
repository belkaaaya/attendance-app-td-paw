<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'students' => [],
    'total' => 0,
    'message' => ''
];

try {
    // Get optional filters
    $group_filter = $_GET['group_id'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query
    $sql = "SELECT id, fullname, matricule, group_id, created_at, updated_at FROM students WHERE 1=1";
    $params = [];
    
    if (!empty($group_filter)) {
        $sql .= " AND group_id = ?";
        $params[] = $group_filter;
    }
    
    if (!empty($search)) {
        $sql .= " AND (fullname LIKE ? OR matricule LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $sql .= " ORDER BY fullname ASC";
    
    // Execute query
    $result = executeQuery($sql, $params);
    
    if ($result['success']) {
        $response['success'] = true;
        $response['students'] = $result['data'];
        $response['total'] = count($result['data']);
        $response['message'] = 'Students retrieved successfully.';
    } else {
        throw new Exception('Failed to retrieve students: ' . $result['error']);
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>