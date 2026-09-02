<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: discounts.php');
    exit();
}
$disc_id = intval($_GET['id']);

$stmt = $conn->prepare('DELETE FROM discount WHERE disc_id = ?');
$stmt->bind_param('i', $disc_id);
$stmt->execute();
$stmt->close();

header('Location: discounts.php');
exit();
