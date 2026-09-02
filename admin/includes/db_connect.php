<?php
// Database connection settings
$host = "localhost";
$username = "root";
$password = "";
$dbname = "u208597444_van_booking";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}
?>
