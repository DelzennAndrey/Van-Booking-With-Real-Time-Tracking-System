<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

// Fetch only the statistics that are actually used
$stats = [
  'earnings_range' => 0.00,
  'bookings' => 0,
  'total_earnings' => 0.00
];

// Range filter (daily, weekly, monthly, yearly)
$range = isset($_GET['range']) ? strtolower($_GET['range']) : 'daily';
$validRanges = ['daily','weekly','monthly','yearly'];
if (!in_array($range, $validRanges, true)) { $range = 'daily'; }

// Compute date range boundaries [startDate, endDate]
$today = new DateTime('today');
switch ($range) {
  case 'weekly':
    // Monday to Sunday of current week
    $start = clone $today; $start->modify('monday this week');
    $end = clone $start; $end->modify('sunday this week');
    $rangeLabel = 'Weekly';
    break;
  case 'monthly':
    $start = new DateTime(date('Y-m-01'));
    $end = new DateTime(date('Y-m-t'));
    $rangeLabel = 'Monthly';
    break;
  case 'yearly':
    $start = new DateTime(date('Y-01-01'));
    $end = new DateTime(date('Y-12-31'));
    $rangeLabel = 'Yearly';
    break;
  case 'daily':
  default:
    $start = clone $today; $end = clone $today; $rangeLabel = 'Daily';
}
$startDate = $start->format('Y-m-d');
$endDate = $end->format('Y-m-d');

// Earnings for selected range (based on booking creation date)
$res = $conn->query("SELECT SUM(b.total_fare) AS total
                      FROM booking b
                      WHERE b.status = 'confirmed' AND DATE(b.created_at) BETWEEN '$startDate' AND '$endDate'");
if ($row = $res->fetch_assoc())
  $stats['earnings_range'] = $row['total'] ? $row['total'] : 0.00;

// Bookings in selected range
$res = $conn->query("SELECT COUNT(*) as total FROM booking WHERE DATE(created_at) BETWEEN '$startDate' AND '$endDate'");
if ($row = $res->fetch_assoc())
  $stats['bookings'] = $row['total'];

// Total Earnings
$res = $conn->query("SELECT SUM(total_fare) as total FROM booking WHERE status = 'confirmed'");
if ($row = $res->fetch_assoc())
  $stats['total_earnings'] = $row['total'] ? $row['total'] : 0.00;

// Performance for selected range
$earnings_in_range = $stats['earnings_range'];
// Simple example targets by range
switch ($range) {
  case 'weekly': $earnings_target = 20000; $bookings_target = 100; break;
  case 'monthly': $earnings_target = 50000; $bookings_target = 200; break;
  case 'yearly': $earnings_target = 600000; $bookings_target = 2400; break;
  case 'daily': default: $earnings_target = 5000; $bookings_target = 20; break;
}
$earnings_progress = $earnings_target > 0 ? min(100, $earnings_in_range / $earnings_target * 100) : 0;
$bookings_in_range = $stats['bookings'];
$bookings_progress = $bookings_target > 0 ? min(100, $bookings_in_range / $bookings_target * 100) : 0;

// Completion rate (using trips that have bookings in the selected range)
$res = $conn->query("SELECT COUNT(DISTINCT t.trip_id) as total
                      FROM van_trip t
                      JOIN booking b ON b.trip_id = t.trip_id
                      WHERE DATE(b.created_at) BETWEEN '$startDate' AND '$endDate'");
$total_trips = 0;
if ($row = $res->fetch_assoc())
  $total_trips = (int)$row['total'];

$res = $conn->query("SELECT COUNT(DISTINCT t.trip_id) as total
                      FROM van_trip t
                      JOIN booking b ON b.trip_id = t.trip_id
                      WHERE DATE(b.created_at) BETWEEN '$startDate' AND '$endDate' AND LOWER(t.status) = 'completed'");
$completed_trips = 0;
if ($row = $res->fetch_assoc())
  $completed_trips = (int)$row['total'];
$completion_rate = $total_trips > 0 ? round($completed_trips / $total_trips * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
  <?php include 'includes/header.php'; ?>

  <!-- Main Content Container -->
  <div class="flex-1 px-6 pt-2 pb-6 ml-0 page-content">
    <!-- Welcome Header -->
    <div class="mb-8">
        <div class="bg-blue-600 rounded-xl p-8 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mt-0">
          <div>
            <h1 class="text-4xl font-bold mb-2">Welcome Back, Admin!</h1>
            <p class="text-blue-100 text-lg">Business overview &mdash; <?php echo htmlspecialchars($rangeLabel); ?> (<?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?>)</p>
          </div>
          <div class="mt-6 lg:mt-0 flex items-center space-x-6">
            <div class="text-center">
              <div class="text-2xl font-bold"><?php echo date('j'); ?></div>
              <div class="text-sm text-blue-100"><?php echo date('M'); ?></div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold" id="current-time"></div>
              <div class="text-sm text-blue-100"><?php echo date('l'); ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Range Filter -->
    <div class="mb-6">
      <form method="get" class="inline-flex items-center gap-2 bg-white rounded-lg shadow px-4 py-3">
        <label for="range" class="text-sm text-gray-700">Range:</label>
        <select id="range" name="range" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
          <option value="daily" <?php echo $range==='daily'?'selected':''; ?>>Daily</option>
          <option value="weekly" <?php echo $range==='weekly'?'selected':''; ?>>Weekly</option>
          <option value="monthly" <?php echo $range==='monthly'?'selected':''; ?>>Monthly</option>
          <option value="yearly" <?php echo $range==='yearly'?'selected':''; ?>>Yearly</option>
        </select>
        <noscript>
          <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm">Apply</button>
        </noscript>
      </form>
    </div>

    <!-- Key Performance Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <!-- Bookings Card -->
      <div
        class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
          <div
            class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-check text-white text-xl"></i>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold text-gray-900"><?php echo $stats['bookings']; ?></div>
            <div class="text-sm text-gray-500">Bookings (<?php echo htmlspecialchars($rangeLabel); ?>)</div>
          </div>
        </div>
        <div class="flex items-center text-sm">
          <i class="fas fa-arrow-up text-green-500 mr-1"></i>
          <span class="text-green-600 font-medium">All time bookings</span>
        </div>
      </div>

      <!-- Earnings Card -->
      <div
        class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
          <div
            class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
            <i class="fas fa-dollar-sign text-white text-xl"></i>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold text-gray-900"><?php echo formatCurrency($stats['total_earnings'], '₱'); ?>
            </div>
            <div class="text-sm text-gray-500">Total Earnings</div>
          </div>
        </div>
        <div class="flex items-center text-sm">
          <i class="fas fa-arrow-up text-green-500 mr-1"></i>
          <span class="text-green-600 font-medium">Confirmed revenue</span>
        </div>
      </div>


      <!-- Today's Revenue Card -->
      <div
        class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
          <div
            class="w-14 h-14 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
            <i class="fas fa-chart-line text-white text-xl"></i>
          </div>
          <div class="text-right">
            <div class="text-3xl font-bold text-gray-900"><?php echo formatCurrency($stats['earnings_range'], '₱'); ?>
            </div>
            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($rangeLabel); ?> Revenue</div>
          </div>
        </div>
        <div class="flex items-center text-sm">
          <i class="fas fa-calendar-day text-blue-500 mr-1"></i>
          <span class="text-blue-600 font-medium">Daily earnings</span>
        </div>
      </div>
    </div>

    <!-- Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Monthly Progress -->
      <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-bold text-gray-900">Performance (<?php echo htmlspecialchars($rangeLabel); ?>)</h3>
          <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full"><?php echo htmlspecialchars($startDate . ' to ' . $endDate); ?></span>
        </div>

        <div class="space-y-6">
          <!-- Earnings Progress -->
          <div>
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm font-medium text-gray-700">Earnings Target</span>
              <span class="text-lg font-bold text-gray-900"><?php echo formatCurrency($earnings_in_range, '₱'); ?> /
                <?php echo formatCurrency($earnings_target, '₱'); ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
              <div
                class="bg-gradient-to-r from-blue-500 to-purple-600 h-4 rounded-full transition-all duration-1000 ease-out"
                style="width: <?php echo $earnings_progress; ?>%"></div>
            </div>
            <div class="flex justify-between items-center mt-2 text-sm">
              <span class="text-gray-500">Progress</span>
              <span class="font-semibold text-blue-600"><?php echo round($earnings_progress); ?>%</span>
            </div>
          </div>

          <!-- Bookings Progress -->
          <div>
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm font-medium text-gray-700">Bookings Target</span>
              <span class="text-lg font-bold text-gray-900"><?php echo $bookings_in_range; ?> /
                <?php echo $bookings_target; ?></span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
              <div
                class="bg-gradient-to-r from-green-500 to-teal-600 h-4 rounded-full transition-all duration-1000 ease-out"
                style="width: <?php echo $bookings_progress; ?>%"></div>
            </div>
            <div class="flex justify-between items-center mt-2 text-sm">
              <span class="text-gray-500">Progress</span>
              <span class="font-semibold text-green-600"><?php echo round($bookings_progress); ?>%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Completion Rate -->
      <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
        <div class="text-center">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Trip Completion</h3>
          <div class="relative w-32 h-32 mx-auto mb-4">
            <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
              <circle cx="60" cy="60" r="50" stroke="#e5e7eb" stroke-width="8" fill="none" />
              <circle cx="60" cy="60" r="50" stroke="#3b82f6" stroke-width="8" fill="none" stroke-dasharray="314"
                stroke-dashoffset="<?php echo 314 - (314 * $completion_rate / 100); ?>"
                class="transition-all duration-1000 ease-out" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="text-center">
                <div class="text-3xl font-bold text-gray-900"><?php echo $completion_rate; ?>%</div>
                <div class="text-xs text-gray-500">Complete</div>
              </div>
            </div>
          </div>
          <div class="text-sm text-gray-600">
            <div class="font-medium"><?php echo $completed_trips; ?> of <?php echo $total_trips; ?> trips</div>
            <div class="text-gray-500">completed this month</div>
          </div>
        </div>
      </div>
    </div>


  </div>

  <script>
    // Update current time
    function updateTime() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('en-US', {
        hour12: true,
        hour: '2-digit',
        minute: '2-digit'
      });
      document.getElementById('current-time').textContent = timeString;
    }

    // Update time every second
    updateTime();
    setInterval(updateTime, 1000);
  </script>
  <?php include 'includes/footer.php'; ?>
</body>

</html>