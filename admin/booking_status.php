<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

include 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: bookings.php');
    exit();
}
$booking_id = intval($_GET['id']);

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = trim($_POST['status']);
    $payment_status = trim($_POST['payment_status']);

    $stmt = $conn->prepare("UPDATE booking SET status = ?, payment_status = ? WHERE booking_id = ?");
    $stmt->bind_param('ssi', $new_status, $payment_status, $booking_id);
    if ($stmt->execute()) {
        $success = 'Booking status updated successfully!';
    } else {
        $error = 'Error updating booking status.';
    }
    $stmt->close();
}

// Fetch booking data
$sql = "SELECT b.*, CONCAT(p.first_name, ' ', p.last_name) AS passenger_name,
        vt.origin, vt.destination,
        s.weekdays AS sched_weekday,
        DATE_FORMAT(s.departure_time, '%h:%i %p') AS sched_time
        FROM booking b
        LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
        LEFT JOIN van_trip vt ON b.trip_id = vt.trip_id
        LEFT JOIN schedule s ON s.sched_id = b.sched_id
        WHERE b.booking_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    echo '<div class="flex-1 p-6 page-content"><div class="max-w-3xl mx-auto"><div class="p-6 bg-white rounded-xl shadow text-red-600 font-semibold">Booking not found.</div></div></div>';
    include 'includes/footer.php';
    exit();
}
?>

<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="bookings.php" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
                <h2 class="text-2xl font-bold text-gray-900">Update Booking Status</h2>
            </div>
            <?php
                $status = strtolower($booking['status'] ?? '');
                $statusClass = 'bg-gray-100 text-gray-700';
                if ($status === 'pending') $statusClass = 'bg-yellow-100 text-yellow-700';
                if ($status === 'confirmed') $statusClass = 'bg-green-100 text-green-700';
                if ($status === 'cancelled' || $status === 'canceled') $statusClass = 'bg-red-100 text-red-700';
                if ($status === 'picked up') $statusClass = 'bg-blue-100 text-blue-700';
                if ($status === 'no-show') $statusClass = 'bg-orange-100 text-orange-700';
            ?>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold <?php echo $statusClass; ?>">
                <i class="fas fa-circle mr-2 text-xs"></i>
                <?php echo htmlspecialchars($booking['status']); ?>
            </span>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-700 border border-green-200 flex items-start">
                <i class="fas fa-check-circle mt-0.5 mr-2"></i>
                <div><?php echo $success; ?></div>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-700 border border-red-200 flex items-start">
                <i class="fas fa-exclamation-triangle mt-0.5 mr-2"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                <div class="space-y-1">
                    <div class="text-gray-500 text-xs uppercase">Booking ID</div>
                    <div class="text-gray-900 font-semibold flex items-center"><i class="fas fa-hashtag mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['booking_id']); ?></div>
                </div>
                <div class="space-y-1">
                    <div class="text-gray-500 text-xs uppercase">Passenger</div>
                    <div class="text-gray-900 font-semibold flex items-center"><i class="fas fa-user mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['passenger_name']); ?></div>
                </div>
                <div class="space-y-1">
                    <div class="text-gray-500 text-xs uppercase">Trip</div>
                    <div class="text-gray-900 flex items-center"><i class="fas fa-route mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['origin'] . ' → ' . $booking['destination']); ?></div>
                </div>
                <div class="space-y-1">
                    <div class="text-gray-500 text-xs uppercase">Schedule</div>
                    <div class="text-gray-900 flex items-center"><i class="fas fa-calendar-day mr-2 text-gray-400"></i><?php 
                        $dw = $booking['sched_weekday'] ?? '';
                        $tm = $booking['sched_time'] ?? '';
                        echo htmlspecialchars(($dw ?: '—') . ($tm ? ' • ' . $tm : ''));
                    ?></div>
                </div>
            </div>

            <div class="px-6 pb-6">
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Booking Status</label>
                        <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" required>
                            <option value="pending" <?php if ($booking['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="confirmed" <?php if ($booking['status'] == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                            <option value="ongoing" <?php if ($booking['status'] == 'ongoing') echo 'selected'; ?>>Picked Up</option>
                            <option value="no_show" <?php if ($booking['status'] == 'no_show') echo 'selected'; ?>>No-Show</option>
                            <option value="cancelled" <?php if ($booking['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                        <select name="payment_status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" required>
                            <option value="pending" <?php if ($booking['payment_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="paid" <?php if ($booking['payment_status'] == 'paid') echo 'selected'; ?>>Paid</option>
                            <option value="refunded" <?php if ($booking['payment_status'] == 'refunded') echo 'selected'; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-center justify-between pt-2">
                        <div class="text-sm text-gray-500">Make sure the statuses reflect the passenger's current state.</div>
                        <div class="flex items-center gap-3">
                            <a href="booking_view.php?id=<?php echo $booking_id; ?>" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-eye mr-2"></i> View Booking
                            </a>
                            <button type="submit" class="inline-flex items-center px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow">
                                <i class="fas fa-save mr-2"></i> Update Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
