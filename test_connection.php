<?php
require_once 'config.php';

header('Content-Type: application/json');

$test_result = testDatabaseConnection();
echo json_encode($test_result, JSON_PRETTY_PRINT);
?>