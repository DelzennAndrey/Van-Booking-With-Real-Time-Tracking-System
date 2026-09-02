<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Fetch bookings with optional search filter
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$baseSql = "SELECT b.*, 
        CONCAT(p.first_name, ' ', p.last_name) AS passenger_name,
        CONCAT(d.first_name, ' ', d.last_name) AS driver_name,
        vt.origin, vt.destination,
        v.van_number,
        s.weekdays AS sched_weekday,
        DATE_FORMAT(s.departure_time, '%h:%i %p') AS sched_time
        FROM booking b
        LEFT JOIN passenger p ON b.passenger_id = p.passenger_id
        LEFT JOIN driver d ON b.driver_id = d.driver_id
        LEFT JOIN van_trip vt ON b.trip_id = vt.trip_id
        LEFT JOIN van v ON vt.van_id = v.van_id
        LEFT JOIN schedule s ON s.sched_id = b.sched_id";

if ($search !== '') {
    $like = '%' . $search . '%';
    $where = " WHERE 
        p.first_name LIKE ? OR p.last_name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?
        OR d.first_name LIKE ? OR d.last_name LIKE ? OR CONCAT(d.first_name, ' ', d.last_name) LIKE ?
        OR v.van_number LIKE ?
        OR vt.origin LIKE ? OR vt.destination LIKE ?
        OR b.pick_up LIKE ? OR b.drop_off LIKE ?
        OR b.payment_status LIKE ? OR b.status LIKE ?
        OR s.weekdays LIKE ? OR DATE_FORMAT(s.departure_time, '%h:%i %p') LIKE ?";
    $order = " ORDER BY b.booking_id DESC";
    $stmt = $conn->prepare($baseSql . $where . $order);
    $stmt->bind_param(
        'sssssssssssssss',
        $like, $like, $like,
        $like, $like, $like,
        $like,
        $like, $like,
        $like, $like,
        $like, $like,
        $like, $like
    );
    $stmt->execute();
    $result = $stmt->get_result();
    // no need to close immediately; will close at end of request implicitly
} else {
    $sql = $baseSql . " ORDER BY b.booking_id DESC";
    $result = $conn->query($sql);
}
?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <div class="max-w-6xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Bookings</h1>
    <form method="get" class="flex items-center gap-2">
      <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search bookings..."
             class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Search</button>
      <?php if ($search !== ''): ?>
        <a href="bookings.php" class="text-sm text-gray-600 hover:text-gray-800 underline">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  <div class="bg-white shadow rounded-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trip Details</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pickup/Dropoff</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Status</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['passenger_name']); ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $row['driver_name'] ? htmlspecialchars($row['driver_name']) : '<span class="italic text-gray-400">Unassigned</span>'; ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="font-semibold"><?php echo htmlspecialchars($row['origin'] . ' → ' . $row['destination']); ?></div>
                <div class="text-xs text-gray-500">
                  Van: <?php echo htmlspecialchars($row['van_number']); ?>
                  <?php if (!empty($row['sched_weekday']) || !empty($row['sched_time'])): ?>
                    | Schedule: <?php echo htmlspecialchars($row['sched_weekday'] ?: '-'); ?><?php if (!empty($row['sched_time'])): ?> — <?php echo htmlspecialchars($row['sched_time']); ?><?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div><span class="font-semibold">Pickup:</span> <?php echo htmlspecialchars($row['pick_up']); ?></div>
                <div><span class="font-semibold">Dropoff:</span> <?php echo htmlspecialchars($row['drop_off']); ?></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php echo $row['payment_status'] == 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>"><?php echo htmlspecialchars($row['payment_status']); ?></span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php 
                  echo $row['status'] == 'confirmed' ? 'bg-green-100 text-green-700' : 
                    ($row['status'] == 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); 
                ?>"><?php echo htmlspecialchars($row['status']); ?></span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <a href="booking_view.php?id=<?php echo $row['booking_id']; ?>" class="text-blue-600 hover:underline mr-3">View</a>
                <a href="booking_status.php?id=<?php echo $row['booking_id']; ?>" class="text-yellow-600 hover:underline">Update</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center py-6 text-gray-500">No bookings found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
