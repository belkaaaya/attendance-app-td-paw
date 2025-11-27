<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get input
        $id = $_POST['id'] ?? 0;
        $fullname = sanitizeInput($_POST['fullname'] ?? '');
        $matricule = sanitizeInput($_POST['matricule'] ?? '');
        $group_id = sanitizeInput($_POST['group_id'] ?? 'CS101');

        // Validation
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('Invalid student ID.');
        }

        if (empty($fullname) || empty($matricule)) {
            throw new Exception('Full name and matricule are required.');
        }

        // Check if matricule already exists (excluding current student)
        $check_sql = "SELECT id FROM students WHERE matricule = ? AND id != ?";
        $check_result = executeQuery($check_sql, [$matricule, $id]);
        
        if ($check_result['success'] && !empty($check_result['data'])) {
            throw new Exception('Matricule already exists for another student.');
        }

        // Update student
        $sql = "UPDATE students SET fullname = ?, matricule = ?, group_id = ? WHERE id = ?";
        $result = executeQuery($sql, [$fullname, $matricule, $group_id, $id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response['success'] = true;
            $response['message'] = 'Student updated successfully!';
        } else {
            throw new Exception('Failed to update student. Student may not exist.');
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>