<?php
require_once 'db_connect.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Insert Test Sessions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .card { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid; }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        .btn { padding: 10px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #667eea; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Insert Test Sessions</h1>";

// Test session data
$test_sessions = [
    [
        'course_id' => 'CS101',
        'group_id' => 'GROUP_A', 
        'date' => date('Y-m-d'),
        'opened_by' => 'professor_john',
        'status' => 'active'
    ],
    [
        'course_id' => 'MATH201',
        'group_id' => 'GROUP_B',
        'date' => date('Y-m-d'),
        'opened_by' => 'professor_smith',
        'status' => 'active'
    ],
    [
        'course_id' => 'PHY301',
        'group_id' => 'GROUP_A',
        'date' => date('Y-m-d', strtotime('+1 day')),
        'opened_by' => 'professor_williams',
        'status' => 'active'
    ]
];

$inserted_sessions = [];

foreach ($test_sessions as $session_data) {
    try {
        // Check if session already exists
        $check_sql = "SELECT id FROM attendance_sessions 
                     WHERE course_id = ? AND group_id = ? AND date = ?";
        $check_result = executeQuery($check_sql, [
            $session_data['course_id'], 
            $session_data['group_id'], 
            $session_data['date']
        ]);
        
        if ($check_result['success'] && !empty($check_result['data'])) {
            echo "<div class='card error'>
                    ⚠️ Session already exists: {$session_data['course_id']} - {$session_data['group_id']} - {$session_data['date']}
                  </div>";
            continue;
        }
        
        // Insert session
        $sql = "INSERT INTO attendance_sessions (course_id, group_id, date, opened_by, status) 
                VALUES (?, ?, ?, ?, ?)";
        
        $result = executeQuery($sql, [
            $session_data['course_id'],
            $session_data['group_id'], 
            $session_data['date'],
            $session_data['opened_by'],
            $session_data['status']
        ]);
        
        if ($result['success']) {
            $session_id = $result['insert_id'];
            $inserted_sessions[] = [
                'id' => $session_id,
                'course_id' => $session_data['course_id'],
                'group_id' => $session_data['group_id'],
                'date' => $session_data['date'],
                'opened_by' => $session_data['opened_by'],
                'status' => $session_data['status']
            ];
            
            echo "<div class='card success'>
                    ✅ Session created: ID {$session_id} - {$session_data['course_id']} - {$session_data['group_id']} - {$session_data['date']}
                  </div>";
        } else {
            echo "<div class='card error'>
                    ❌ Failed to create session: {$session_data['course_id']} - {$session_data['group_id']} - {$session_data['date']}
                  </div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='card error'>
                ❌ Error creating session: {$session_data['course_id']} - {$session_data['group_id']} - {$session_data['date']}
              </div>";
    }
}

// Display inserted sessions in a table
if (!empty($inserted_sessions)) {
    echo "<h3>Inserted Sessions</h3>";
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Course</th>
                <th>Group</th>
                <th>Date</th>
                <th>Opened By</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>";
    
    foreach ($inserted_sessions as $session) {
        echo "<tr>
                <td>{$session['id']}</td>
                <td>{$session['course_id']}</td>
                <td>{$session['group_id']}</td>
                <td>{$session['date']}</td>
                <td>{$session['opened_by']}</td>
                <td>{$session['status']}</td>
                <td>
                    <a href='test_close_session.html?session_id={$session['id']}' class='btn btn-primary'>Test Close</a>
                    <button onclick='closeSession({$session['id']})' class='btn btn-danger'>Close Now</button>
                </td>
              </tr>";
    }
    echo "</table>";
}

echo "
    <div style='margin-top: 20px;'>
        <a href='test_close_session.html' class='btn btn-success'>🧪 Go to Close Session Test</a>
    </div>

    <script>
    function closeSession(sessionId) {
        if (confirm('Are you sure you want to close session ' + sessionId + '?')) {
            const formData = new FormData();
            formData.append('session_id', sessionId);
            
            fetch('close_session.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error: ' + error);
            });
        }
    }
    </script>
</body>
</html>";
?>