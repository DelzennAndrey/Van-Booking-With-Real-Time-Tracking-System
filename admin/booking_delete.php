<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id > 0) {
    // Verify booking exists
    $stmt = $conn->prepare("SELECT booking_id FROM booking WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->fetch_assoc();
    $stmt->close();

    if ($exists) {
        // Delete booking
        $stmt = $conn->prepare("DELETE FROM booking WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = "Booking deleted successfully.";
    } else {
        $_SESSION['error'] = "Booking not found.";
    }
} else {
    $_SESSION['error'] = "Invalid booking ID.";
}

// Redirect back to bookings list
header("Location: bookings.php");
exit;
