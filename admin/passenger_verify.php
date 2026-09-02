<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: passengers.php');
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: passengers.php');
    exit();
}

$passenger_id = intval($_POST['id']);

$stmt = $conn->prepare('UPDATE passenger SET is_verified = 1 WHERE passenger_id = ?');
$stmt->bind_param('i', $passenger_id);
$stmt->execute();
$stmt->close();

// If request is AJAX (fetch with X-Requested-With), return JSON; otherwise redirect back
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'id' => $passenger_id]);
    exit();
}

header('Location: passenger_view.php?id=' . $passenger_id);
exit();
