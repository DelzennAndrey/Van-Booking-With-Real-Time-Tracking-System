<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: passengers.php');
    exit();
}
$passenger_id = intval($_GET['id']);

$stmt = $conn->prepare('UPDATE passenger SET no_show = 0 WHERE passenger_id = ?');
$stmt->bind_param('i', $passenger_id);
$stmt->execute();
$stmt->close();

header('Location: passengers.php');
exit();
