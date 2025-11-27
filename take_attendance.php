<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'attendance_data' => null
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get attendance data from POST
        $attendance_json = $_POST['attendance'] ?? '';
        
        if (empty($attendance_json)) {
            throw new Exception('No attendance data received.');
        }
        
        $attendance_data = json_decode($attendance_json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        // Save attendance data to JSON file (for demonstration)
        $filename = 'attendance_' . date('Y-m-d_H-i-s') . '.json';
        
        if (file_put_contents($filename, json_encode($attendance_data, JSON_PRETTY_PRINT))) {
            $response['success'] = true;
            $response['message'] = 'Attendance data saved successfully!';
            $response['attendance_data'] = $attendance_data;
            $response['file'] = $filename;
        } else {
            throw new Exception('Failed to save attendance data to file.');
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. Only POST requests are allowed.';
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>