<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: drivers.php');
    exit();
}
$driver_id = intval($_GET['id']);

// Use a transaction to keep all deletions consistent
$conn->begin_transaction();

try {
    // Check if driver exists
    $stmt = $conn->prepare('SELECT user_id FROM driver WHERE driver_id = ? LIMIT 1');
    $stmt->bind_param('i', $driver_id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $found = $stmt->fetch();
    $stmt->close();

    if (!$found) {
        // Nothing to delete
        $conn->rollback();
        header('Location: drivers.php');
        exit();
    }

    // Check for active bookings or trips that would prevent deletion
    $checkStmt = $conn->prepare('
        SELECT COUNT(*) as active_bookings 
        FROM booking b 
        JOIN van_trip vt ON b.trip_id = vt.trip_id 
        WHERE b.driver_id = ? AND vt.status IN ("active", "ongoing")
    ');
    $checkStmt->bind_param('i', $driver_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $active_bookings = $result->fetch_assoc()['active_bookings'];
    $checkStmt->close();

    if ($active_bookings > 0) {
        // Cannot delete driver with active trips
        $conn->rollback();
        header('Location: drivers.php?error=active_trips');
        exit();
    }

    // Handle related records first (in proper order due to foreign key constraints)
    
    // 1. Delete location records (no foreign key constraint, but should be cleaned up)
    $stmtLocation = $conn->prepare('DELETE FROM location WHERE driver_id = ?');
    $stmtLocation->bind_param('i', $driver_id);
    $stmtLocation->execute();
    $stmtLocation->close();

    // 2. Update van records to set driver_id to NULL (due to ON DELETE SET NULL constraint)
    // This happens automatically due to foreign key constraint, but we can be explicit
    $stmtVan = $conn->prepare('UPDATE van SET driver_id = NULL WHERE driver_id = ?');
    $stmtVan->bind_param('i', $driver_id);
    $stmtVan->execute();
    $stmtVan->close();

    // 3. Update booking records to set driver_id to NULL (due to ON DELETE SET NULL constraint)
    // This happens automatically due to foreign key constraint, but we can be explicit
    $stmtBooking = $conn->prepare('UPDATE booking SET driver_id = NULL WHERE driver_id = ?');
    $stmtBooking->bind_param('i', $driver_id);
    $stmtBooking->execute();
    $stmtBooking->close();

    // 4. Delete the driver record
    $stmtDelDriver = $conn->prepare('DELETE FROM driver WHERE driver_id = ?');
    $stmtDelDriver->bind_param('i', $driver_id);
    $stmtDelDriver->execute();
    $stmtDelDriver->close();

    // 5. If there is an associated user account, delete it too
    if (!is_null($user_id)) {
        $stmtDelUser = $conn->prepare('DELETE FROM user WHERE user_id = ?');
        $stmtDelUser->bind_param('i', $user_id);
        $stmtDelUser->execute();
        $stmtDelUser->close();
    }

    $conn->commit();
    
    // Redirect with success message
    header('Location: drivers.php?success=deleted');
    exit();

} catch (Throwable $e) {
    // Roll back on any error and log the error
    $conn->rollback();
    error_log("Driver deletion error: " . $e->getMessage());
    header('Location: drivers.php?error=delete_failed');
    exit();
}