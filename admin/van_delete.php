<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: vans.php?status=error&message=Invalid+van+ID');
    exit();
}
$van_id = intval($_GET['id']);

$check = $conn->prepare('SELECT COUNT(*) FROM van_trip WHERE van_id = ?');
if ($check === false) {
    header('Location: vans.php?status=error&message=Failed+to+prepare+pre-delete+check');
    exit();
}
$check->bind_param('i', $van_id);
$check->execute();
$check->bind_result($tripCount);
$check->fetch();
$check->close();
if ($tripCount > 0) {
    header('Location: vans.php?status=error&message=Cannot+delete+van+with+existing+trips');
    exit();
}

$stmt = $conn->prepare('DELETE FROM van WHERE van_id = ?');
if ($stmt === false) {
    header('Location: vans.php?status=error&message=Failed+to+prepare+delete+statement');
    exit();
}

$stmt->bind_param('i', $van_id);

try {
    $executed = $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();

    if ($executed && $affectedRows > 0) {
        header('Location: vans.php?status=success&message=Van+deleted+successfully');
    } else {
        header('Location: vans.php?status=error&message=Unable+to+delete+van');
    }
} catch (mysqli_sql_exception $e) {
    $stmt->close();

    // Handle foreign key constraint specifically (van used in trips/bookings)
    if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
        header('Location: vans.php?status=error&message=Cannot+delete+van+because+it+is+used+in+trips+or+bookings');
    } else {
        header('Location: vans.php?status=error&message=Database+error+while+deleting+van');
    }
}
exit();
