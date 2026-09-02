<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: routes.php?status=error&message=Invalid+route+ID');
    exit();
}
$route_id = intval($_GET['id']);

try {
    // Get route name first
    $route_stmt = $conn->prepare('SELECT route FROM route WHERE route_id = ?');
    $route_stmt->bind_param('i', $route_id);
    $route_stmt->execute();
    $route_result = $route_stmt->get_result();
    $route_data = $route_result->fetch_assoc();
    $route_stmt->close();
    
    if (!$route_data) {
        header('Location: routes.php?status=error&message=Route+not+found');
        exit();
    }
    
    $route_name = $route_data['route'];
    
    // Check if route is used in trips
    $check_stmt = $conn->prepare('SELECT COUNT(*) as trip_count FROM van_trip WHERE origin = ? OR destination = ?');
    $check_stmt->bind_param('ss', $route_name, $route_name);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $trip_data = $result->fetch_assoc();
    $check_stmt->close();
    
    if ($trip_data['trip_count'] > 0) {
        header('Location: routes.php?status=error&message=Cannot+delete+route+because+it+is+used+in+trips');
        exit();
    }
    
    // If not used, proceed with deletion
    $stmt = $conn->prepare('DELETE FROM route WHERE route_id = ?');
    $stmt->bind_param('i', $route_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        header('Location: routes.php?status=success&message=Route+deleted+successfully');
    } else {
        header('Location: routes.php?status=error&message=Route+not+found');
    }
    $stmt->close();
    
} catch (mysqli_sql_exception $e) {
    // Handle any database errors
    if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
        header('Location: routes.php?status=error&message=Cannot+delete+route+because+it+is+referenced+by+other+records');
    } else {
        header('Location: routes.php?status=error&message=Database+error+while+deleting+route');
    }
}
exit();
