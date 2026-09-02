<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$booking = null;
if ($booking_id) {
    $stmt = $conn->prepare("SELECT b.*, 
                                   p.first_name AS passenger_first, 
                                   p.last_name AS passenger_last, 
                                   d.first_name AS driver_first, 
                                   d.last_name AS driver_last, 
                                   v.van_number,
                                   s.weekdays AS sched_weekday,
                                   DATE_FORMAT(s.departure_time, '%h:%i %p') AS sched_time
                             FROM booking b
                             LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
                             LEFT JOIN driver d ON b.driver_id = d.driver_id
                             LEFT JOIN van_trip vt ON b.trip_id = vt.trip_id
                             LEFT JOIN van v ON vt.van_id = v.van_id
                             LEFT JOIN schedule s ON s.sched_id = b.sched_id
                             WHERE b.booking_id = ?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
}
?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <a href="bookings.php" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
          <i class="fas fa-arrow-left mr-2"></i>
          Back
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Booking Details</h2>
      </div>
      <?php if ($booking): ?>
      <?php 
        $status = strtolower($booking['status'] ?? '');
        $statusClass = 'bg-gray-100 text-gray-700';
        if ($status === 'pending') $statusClass = 'bg-yellow-100 text-yellow-700';
        if ($status === 'confirmed') $statusClass = 'bg-green-100 text-green-700';
        if ($status === 'cancelled' || $status === 'canceled') $statusClass = 'bg-red-100 text-red-700';
        if ($status === 'completed') $statusClass = 'bg-blue-100 text-blue-700';
      ?>
      <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold <?php echo $statusClass; ?>">
        <i class="fas fa-circle mr-2 text-xs"></i>
        <?php echo htmlspecialchars($booking['status']); ?>
      </span>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
      <?php if ($booking): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Passenger</div>
          <div class="text-gray-900 font-semibold flex items-center"><i class="fas fa-user mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['passenger_first'] . ' ' . $booking['passenger_last']); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Driver</div>
          <div class="text-gray-900 font-semibold flex items-center"><i class="fas fa-user-tie mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['driver_first'] . ' ' . $booking['driver_last']); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Van Number</div>
          <div class="text-gray-900 font-semibold flex items-center"><i class="fas fa-bus mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['van_number'] ?? '—'); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Schedule</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-calendar-day mr-2 text-gray-400"></i><?php 
            $dw = $booking['sched_weekday'] ?? '';
            $tm = $booking['sched_time'] ?? '';
            echo htmlspecialchars(($dw ?: '—') . ($tm ? ' • ' . $tm : ''));
          ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Seats Requested</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-chair mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['seat']); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Pick Up</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-location-arrow mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['pick_up']); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Drop Off</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-map-marker-alt mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['drop_off']); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Payment Method</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-credit-card mr-2 text-gray-400"></i><?php echo htmlspecialchars(ucfirst($booking['payment_method'] ?? '—')); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Fare</div>
          <div class="text-gray-900 font-semibold flex items-center"><i class="fas fa-peso-sign mr-2 text-gray-400"></i>₱<?php echo number_format((float)($booking['total_fare'] ?? 0), 2); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Reference</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-hashtag mr-2 text-gray-400"></i><?php echo htmlspecialchars($booking['booking_id'] ?? $booking_id); ?></div>
        </div>
      </div>

      <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
        <div class="text-sm text-gray-500">Last updated: <?php echo htmlspecialchars($booking['updated_at'] ?? '—'); ?></div>
        <div class="flex items-center gap-3">
          <a href="bookings.php" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
            <i class="fas fa-list mr-2"></i> All Bookings
          </a>
          <a href="booking_delete.php?id=<?php echo $booking_id; ?>" 
             onclick="return confirm('Are you sure you want to delete this booking?');"
             class="inline-flex items-center px-5 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors shadow">
            <i class="fas fa-trash mr-2"></i> Delete Booking
          </a>
        </div>
      </div>
      <?php else: ?>
      <div class="p-8 text-center">
        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-50 flex items-center justify-center text-red-500">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="text-red-600 font-semibold">Booking not found.</p>
        <a href="bookings.php" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
          <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
