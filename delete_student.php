<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get student ID
        $id = $_POST['id'] ?? 0;

        // Validation
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('Invalid student ID.');
        }

        // Check if student exists
        $check_sql = "SELECT id, fullname FROM students WHERE id = ?";
        $check_result = executeQuery($check_sql, [$id]);
        
        if (!$check_result['success'] || empty($check_result['data'])) {
            throw new Exception('Student not found.');
        }

        $student_name = $check_result['data'][0]['fullname'];

        // Delete student
        $sql = "DELETE FROM students WHERE id = ?";
        $result = executeQuery($sql, [$id]);

        if ($result['success'] && $result['affected_rows'] > 0) {
            $response['success'] = true;
            $response['message'] = "Student '$student_name' deleted successfully!";
        } else {
            throw new Exception('Failed to delete student.');
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>