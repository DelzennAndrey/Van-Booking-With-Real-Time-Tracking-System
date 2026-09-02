<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

$driver_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$driver = null;

if ($driver_id) {
    $sql = "SELECT 
                d.driver_id,
                d.first_name,
                d.last_name,
                d.dob,
                d.age,
                d.address,
                d.phone_number AS phone,
                d.license_number,
                d.status,
                d.profile_url,
                u.username,
                /* aggregates */
                (SELECT COUNT(vt.trip_id)
                   FROM van v
                   LEFT JOIN van_trip vt ON v.van_id = vt.van_id
                  WHERE v.driver_id = d.driver_id) AS total_trips,
                (SELECT COUNT(vt.trip_id)
                   FROM van v
                   LEFT JOIN van_trip vt ON v.van_id = vt.van_id
                  WHERE v.driver_id = d.driver_id AND vt.status = 'completed') AS completed_trips,
                (SELECT COALESCE(SUM(CASE WHEN b.status = 'confirmed' THEN b.total_fare ELSE 0 END), 0)
                   FROM van v
                   LEFT JOIN van_trip vt ON v.van_id = vt.van_id
                   LEFT JOIN booking b ON vt.trip_id = b.trip_id
                  WHERE v.driver_id = d.driver_id) AS total_earnings,
                NULL AS last_trip_date
            FROM driver d
            LEFT JOIN user u ON d.user_id = u.user_id AND u.role = 'driver'
            WHERE d.driver_id = ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $driver = $result->fetch_assoc();
    $stmt->close();
}
?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <a href="drivers.php" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors">
          <i class="fas fa-arrow-left mr-2"></i>
          Back
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Driver Details</h2>
      </div>
      <?php if ($driver): ?>
      <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold">
        <?php echo getStatusBadge($driver['status'] ?? '', 'driver'); ?>
      </span>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
      <?php if ($driver): ?>
      <div class="p-6 border-b border-gray-100 flex items-center">
        <?php if (!empty($driver['profile_url'])): ?>
          <img src="<?php echo htmlspecialchars($driver['profile_url']); ?>" alt="Profile" class="h-14 w-14 rounded-full object-cover mr-4 border" onerror="this.style.display='none'">
        <?php else: ?>
          <div class="h-14 w-14 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-lg font-semibold mr-4">
            <?php echo strtoupper(substr($driver['first_name'], 0, 1) . substr($driver['last_name'], 0, 1)); ?>
          </div>
        <?php endif; ?>
        <div>
          <div class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?></div>
          <div class="text-gray-500">@<?php echo htmlspecialchars($driver['username'] ?? ''); ?></div>
        </div>
        <div class="ml-auto">
          <a href="driver_edit.php?id=<?php echo $driver['driver_id']; ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
            <i class="fas fa-edit mr-2"></i> Edit
          </a>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Phone</div>
          <div class="text-gray-900 font-medium flex items-center"><i class="fas fa-phone mr-2 text-gray-400"></i><?php echo htmlspecialchars($driver['phone'] ?? '—'); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Address</div>
          <div class="text-gray-900 font-medium flex items-center"><i class="fas fa-home mr-2 text-gray-400"></i><?php echo htmlspecialchars($driver['address'] ?? '—'); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">License Number</div>
          <div class="text-gray-900 font-medium flex items-center"><i class="fas fa-id-card mr-2 text-gray-400"></i><?php echo htmlspecialchars($driver['license_number'] ?? '—'); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Date of Birth</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-calendar-day mr-2 text-gray-400"></i><?php echo !empty($driver['dob']) ? date('M j, Y', strtotime($driver['dob'])) : '—'; ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Age</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-user-clock mr-2 text-gray-400"></i><?php 
            $computedAge = '—';
            if (!empty($driver['dob'])) {
              try { $dobDt = new DateTime($driver['dob']); $today = new DateTime('today'); $computedAge = (int)$dobDt->diff($today)->y; } catch (Exception $e) { $computedAge = '—'; }
            }
            echo htmlspecialchars($computedAge);
          ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Total Trips</div>
          <div class="text-gray-900 font-medium flex items-center"><i class="fas fa-route mr-2 text-gray-400"></i><?php echo (int)($driver['total_trips'] ?? 0); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Completed Trips</div>
          <div class="text-gray-900 font-medium flex items-center"><i class="fas fa-check-circle mr-2 text-gray-400"></i><?php echo (int)($driver['completed_trips'] ?? 0); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Total Earnings</div>
          <div class="text-gray-900 font-semibold flex items-center"><i class="fas fa-dollar-sign mr-2 text-green-500"></i><?php echo formatCurrency($driver['total_earnings'] ?? 0); ?></div>
        </div>
        <div class="space-y-1">
          <div class="text-gray-500 text-xs uppercase">Last Trip Date</div>
          <div class="text-gray-900 flex items-center"><i class="fas fa-clock mr-2 text-gray-400"></i><?php echo $driver['last_trip_date'] ? date('M j, Y', strtotime($driver['last_trip_date'])) : '—'; ?></div>
        </div>
      </div>
      <?php else: ?>
      <div class="p-8 text-center">
        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-50 flex items-center justify-center text-red-500">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <p class="text-red-600 font-semibold">Driver not found.</p>
        <a href="drivers.php" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition-colors">
          <i class="fas fa-arrow-left mr-2"></i> Back to Drivers
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
