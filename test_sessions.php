<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'sessions_created' => 0,
    'sessions' => []
];

try {
    // Test session data
    $test_sessions = [
        [
            'course_id' => 'AWP',
            'group_id' => 'WEB3',
            'professor_id' => 'professor_john'
        ],
        [
            'course_id' => 'PHP',
            'group_id' => 'ISIL', 
            'professor_id' => 'professor_smith'
        ],
        [
            'course_id' => 'JavaScript',
            'group_id' => 'Master',
            'professor_id' => 'assistant_mary'
        ]
    ];

    $created_sessions = [];
    $current_date = date('Y-m-d');

    foreach ($test_sessions as $session_data) {
        // Check if session already exists
        $check_sql = "SELECT id FROM attendance_sessions 
                     WHERE course_id = ? AND group_id = ? AND date = ?";
        $check_result = executeQuery($check_sql, [
            $session_data['course_id'], 
            $session_data['group_id'], 
            $current_date
        ]);
        
        if ($check_result['success'] && !empty($check_result['data'])) {
            // Session already exists, get it
            $existing_id = $check_result['data'][0]['id'];
            $get_sql = "SELECT * FROM attendance_sessions WHERE id = ?";
            $get_result = executeQuery($get_sql, [$existing_id]);
            if ($get_result['success'] && !empty($get_result['data'])) {
                $created_sessions[] = $get_result['data'][0];
            }
            continue;
        }
        
        // Create new session
        $sql = "INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) 
                VALUES (?, ?, ?, ?, 'active')";
        
        $result = executeQuery($sql, [
            $session_data['course_id'],
            $session_data['group_id'], 
            $current_date,
            $session_data['professor_id'],
        ]);
        
        if ($result['success']) {
            $session_id = $result['insert_id'];
            
            // Get the created session
            $get_sql = "SELECT * FROM attendance_sessions WHERE id = ?";
            $get_result = executeQuery($get_sql, [$session_id]);
            if ($get_result['success'] && !empty($get_result['data'])) {
                $created_sessions[] = $get_result['data'][0];
                $response['sessions_created']++;
            }
        }
    }

    $response['success'] = true;
    $response['sessions'] = $created_sessions;
    $response['message'] = 'Test sessions processed successfully.';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>