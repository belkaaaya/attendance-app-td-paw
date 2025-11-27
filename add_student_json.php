<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get input data
        $studentId = sanitizeInput($_POST['studentId'] ?? '');
        $lastName = sanitizeInput($_POST['lastName'] ?? '');
        $firstName = sanitizeInput($_POST['firstName'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');

        // Validation
        if (empty($studentId) || empty($lastName) || empty($firstName) || empty($email)) {
            throw new Exception('All fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        // Create student data array
        $studentData = [
            'student_id' => $studentId,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // For Exercise 1: Save to JSON file
        $jsonFile = 'students_data.json';
        $existingData = [];
        
        // Read existing data if file exists
        if (file_exists($jsonFile)) {
            $existingContent = file_get_contents($jsonFile);
            $existingData = json_decode($existingContent, true) ?? [];
        }
        
        // Add new student
        $existingData[] = $studentData;
        
        // Save back to JSON file
        if (file_put_contents($jsonFile, json_encode($existingData, JSON_PRETTY_PRINT))) {
            $response['success'] = true;
            $response['message'] = 'Student added to JSON file successfully!';
        } else {
            throw new Exception('Failed to write to JSON file.');
        }

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>