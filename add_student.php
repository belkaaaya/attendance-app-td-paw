<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'student_id' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate input
        $fullname = sanitizeInput($_POST['fullname'] ?? '');
        $matricule = sanitizeInput($_POST['matricule'] ?? '');
        $group_id = sanitizeInput($_POST['group_id'] ?? 'CS101');

        // Validation
        if (empty($fullname) || empty($matricule)) {
            throw new Exception('Full name and matricule are required.');
        }

        if (strlen($fullname) < 2 || strlen($fullname) > 100) {
            throw new Exception('Full name must be between 2 and 100 characters.');
        }

        if (!preg_match('/^[A-Za-z0-9]+$/', $matricule)) {
            throw new Exception('Matricule must contain only letters and numbers.');
        }

        // Check if matricule already exists
        $check_sql = "SELECT id FROM students WHERE matricule = ?";
        $check_result = executeQuery($check_sql, [$matricule]);
        
        if ($check_result['success'] && !empty($check_result['data'])) {
            throw new Exception('Matricule already exists.');
        }

        // Insert new student
        $sql = "INSERT INTO students (fullname, matricule, group_id) VALUES (?, ?, ?)";
        $result = executeQuery($sql, [$fullname, $matricule, $group_id]);

        if ($result['success']) {
            $response['success'] = true;
            $response['message'] = 'Student added successfully!';
            $response['student_id'] = $result['insert_id'];
        } else {
            throw new Exception('Failed to add student: ' . $result['error']);
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>