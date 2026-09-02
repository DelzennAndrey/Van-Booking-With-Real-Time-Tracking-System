<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Fetch trips with optional search filter
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$baseSql = "SELECT trip.*, van.van_number,
                   COUNT(DISTINCT b.booking_id) AS booking_count,
                   GROUP_CONCAT(DISTINCT s.weekdays ORDER BY s.weekdays SEPARATOR ', ') AS weekdays_list,
                   GROUP_CONCAT(DISTINCT DATE_FORMAT(s.departure_time, '%h:%i %p') ORDER BY s.departure_time SEPARATOR ', ') AS times_list
            FROM van_trip AS trip
            LEFT JOIN van ON trip.van_id = van.van_id
            LEFT JOIN booking b ON b.trip_id = trip.trip_id
            LEFT JOIN schedule s ON s.trip_id = trip.trip_id";
$group = " GROUP BY trip.trip_id";
if ($search !== '') {
  $like = '%' . $search . '%';
  $where = " WHERE 
    van.van_number LIKE ? OR trip.origin LIKE ? OR trip.destination LIKE ?
    OR trip.status LIKE ? OR s.weekdays LIKE ? OR s.departure_time LIKE ?
    OR CAST(trip.available_seat AS CHAR) LIKE ?";
  $order = " ORDER BY trip.trip_id DESC";
  $stmt = $conn->prepare($baseSql . $where . $group . $order);
  $stmt->bind_param('sssssss', $like, $like, $like, $like, $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $sql = $baseSql . $group . " ORDER BY trip.trip_id DESC";
  $result = $conn->query($sql);
}
// Build in-memory trips array
$trips = [];
if ($result) {
  while ($r = $result->fetch_assoc()) {
    $trips[] = $r;
  }
}

// Fetch all schedules for listed trips and group them by trip and day
$schedulesByTrip = [];
if (!empty($trips)) {
  $tripIds = array_map(function($t){ return (int)$t['trip_id']; }, $trips);
  $tripIds = array_values(array_unique($tripIds));
  if (!empty($tripIds)) {
    $in = implode(',', array_map('intval', $tripIds));
    $schedSql = "SELECT trip_id, weekdays, DATE_FORMAT(departure_time, '%h:%i %p') AS time12
                 FROM schedule
                 WHERE trip_id IN ($in)
                 ORDER BY FIELD(weekdays,'Mon','Tue','Wed','Thu','Fri','Sat','Sun'), departure_time";
    $schedRes = $conn->query($schedSql);
    if ($schedRes) {
      while ($s = $schedRes->fetch_assoc()) {
        $tid = (int)$s['trip_id'];
        $day = $s['weekdays'];
        $time = $s['time12'];
        if (!isset($schedulesByTrip[$tid])) { $schedulesByTrip[$tid] = []; }
        if (!isset($schedulesByTrip[$tid][$day])) { $schedulesByTrip[$tid][$day] = []; }
        if (!in_array($time, $schedulesByTrip[$tid][$day], true)) {
          $schedulesByTrip[$tid][$day][] = $time;
        }
      }
    }
  }
}
?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <div class="max-w-5xl mx-auto">
  
  <!-- Success/Error Messages -->
  <?php if (isset($_GET['success'])): ?>
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
      <?php
      switch($_GET['success']) {
        case 'trip_deleted':
          echo 'Trip deleted successfully.';
          break;
        default:
          echo 'Operation completed successfully.';
      }
      ?>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_GET['error'])): ?>
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
      <?php
      switch($_GET['error']) {
        case 'cannot_delete_trip_with_bookings':
          echo 'Cannot delete trip: There are existing bookings for this trip. Please cancel all bookings first.';
          break;
        case 'invalid_id':
          echo 'Invalid trip ID provided.';
          break;
        case 'trip_not_found':
          echo 'Trip not found.';
          break;
        case 'deletion_failed':
          echo 'Failed to delete trip. Please try again.';
          break;
        default:
          echo 'An error occurred. Please try again.';
      }
      ?>
    </div>
  <?php endif; ?>
  
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Trips</h1>
    <div class="flex items-center gap-3">
      <form method="get" class="flex items-center gap-2">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search trips..."
               class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Search</button>
        <?php if ($search !== ''): ?>
          <a href="trips.php" class="text-sm text-gray-600 hover:text-gray-800 underline">Clear</a>
        <?php endif; ?>
      </form>
      <a href="trip_add.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Add New Trip</a>
    </div>
  </div>
  <div class="bg-blue-600 shadow rounded-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Van</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Origin</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Seats</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booked</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php if (!empty($trips)): ?>
          <?php
          $dayLabels = [
            'Mon' => 'Monday',
            'Tue' => 'Tuesday',
            'Wed' => 'Wednesday',
            'Thu' => 'Thursday',
            'Fri' => 'Friday',
            'Sat' => 'Saturday',
            'Sun' => 'Sunday',
          ];
          ?>
          <?php foreach ($trips as $row): ?>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['van_number']); ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['origin']); ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['destination']); ?></td>
              <td class="px-6 py-4 align-top text-sm text-gray-900">
                <?php if (isset($schedulesByTrip[$row['trip_id']]) && !empty($schedulesByTrip[$row['trip_id']])): ?>
                  <?php foreach ($dayLabels as $code => $label): ?>
                    <?php if (!empty($schedulesByTrip[$row['trip_id']][$code])): ?>
                      <div class="leading-6"><span class="font-medium text-gray-700"><?php echo htmlspecialchars($label); ?></span>: <?php echo htmlspecialchars(implode(', ', $schedulesByTrip[$row['trip_id']][$code])); ?></div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div>-</div>
                <?php endif; ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['available_seat']); ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo isset($row['booking_count']) ? (int)$row['booking_count'] : 0; ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <?php echo getStatusBadge($row['status'], 'trip'); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <a href="trip_edit.php?id=<?php echo $row['trip_id']; ?>" class="text-yellow-600 hover:underline mr-3">Edit</a>
                <?php if ((int)$row['booking_count'] > 0): ?>
                  <span class="text-gray-400 cursor-not-allowed" title="Cannot delete trip with existing bookings">Delete</span>
                <?php else: ?>
                  <a href="trip_delete.php?id=<?php echo $row['trip_id']; ?>" 
                     class="text-red-600 hover:underline" 
                     onclick="return confirm('Are you sure you want to delete this trip? This action cannot be undone.')">Delete</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center py-6 text-gray-500">No trips found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  </div>
</div>
 
<?php include 'includes/footer.php'; ?>
