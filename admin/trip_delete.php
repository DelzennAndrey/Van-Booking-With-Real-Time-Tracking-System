<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: trips.php?error=invalid_id');
    exit();
}
$trip_id = intval($_GET['id']);

try {
    // Check if there are any bookings for this trip
    $check_stmt = $conn->prepare('SELECT COUNT(*) as booking_count FROM booking WHERE trip_id = ?');
    $check_stmt->bind_param('i', $trip_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $booking_data = $result->fetch_assoc();
    $check_stmt->close();
    
    if ($booking_data['booking_count'] > 0) {
        // If there are bookings, prevent deletion and redirect with error message
        header('Location: trips.php?error=cannot_delete_trip_with_bookings');
        exit();
    }
    
    // If no bookings, proceed with deletion
    $sched_stmt = $conn->prepare('DELETE FROM schedule WHERE trip_id = ?');
    $sched_stmt->bind_param('i', $trip_id);
    $sched_stmt->execute();
    $sched_stmt->close();

    $stmt = $conn->prepare('DELETE FROM van_trip WHERE trip_id = ?');
    $stmt->bind_param('i', $trip_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        header('Location: trips.php?success=trip_deleted');
        exit();
    } else {
        $stmt->close();
        header('Location: trips.php?error=trip_not_found');
        exit();
    }
    
} catch (mysqli_sql_exception $e) {
    // Handle any database errors
    error_log("Trip deletion error: " . $e->getMessage());
    header('Location: trips.php?error=deletion_failed');
    exit();
}
