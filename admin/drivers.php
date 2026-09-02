<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Fetch drivers with optional search filter
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$baseSql = "SELECT 
            d.driver_id,
            d.first_name,
            d.last_name,
            u.username,
            d.phone_number as phone,
            d.license_number,
            d.status,
            d.profile_url,
            COUNT(vt.trip_id) as total_trips,
            COUNT(CASE WHEN vt.status = 'completed' THEN 1 END) as completed_trips,
            COUNT(CASE WHEN vt.status = 'ongoing' THEN 1 END) as active_trips,
            SUM(CASE WHEN b.status = 'confirmed' THEN b.total_fare ELSE 0 END) as total_earnings,
            NULL as last_trip_date
        FROM driver d
        LEFT JOIN user u ON d.user_id = u.user_id AND u.role = 'driver'
        LEFT JOIN van v ON d.driver_id = v.driver_id
        LEFT JOIN van_trip vt ON v.van_id = vt.van_id
        LEFT JOIN booking b ON vt.trip_id = b.trip_id";

if ($search !== '') {
    $like = '%' . $search . '%';
    $where = " WHERE 
        d.first_name LIKE ? OR d.last_name LIKE ? OR CONCAT(d.first_name, ' ', d.last_name) LIKE ?
        OR u.username LIKE ?
        OR d.phone_number LIKE ?
        OR d.license_number LIKE ?
        OR d.status LIKE ?";
    $groupOrder = " GROUP BY d.driver_id ORDER BY d.driver_id DESC";
    $stmt = $conn->prepare($baseSql . $where . $groupOrder);
    $stmt->bind_param('sssssss', $like, $like, $like, $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = $baseSql . " GROUP BY d.driver_id ORDER BY d.driver_id DESC";
    $result = $conn->query($sql);
}
?>

<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <!-- Header Section -->
  <div class="mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Driver Management</h1>
        <p class="text-gray-600">Manage your drivers and their performance</p>
      </div>
      <div class="mt-4 lg:mt-0 flex items-center gap-3">
        <a href="driver_add.php" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors shadow-lg">
          <i class="fas fa-user-plus mr-2"></i>
          Add New Driver
        </a>
        <form method="get" class="flex items-center gap-2">
          <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search drivers..."
                 class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Search</button>
          <?php if ($search !== ''): ?>
            <a href="drivers.php" class="text-sm text-gray-600 hover:text-gray-800 underline">Clear</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <!-- Success/Error Messages -->
  <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
      <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>Driver has been successfully deleted.</span>
      </div>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php if ($_GET['error'] === 'active_trips'): ?>
          <span>Cannot delete driver with active or ongoing trips. Please complete or cancel active trips first.</span>
        <?php elseif ($_GET['error'] === 'delete_failed'): ?>
          <span>Failed to delete driver. Please try again or contact support.</span>
        <?php else: ?>
          <span>An error occurred while processing your request.</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
  
  <!-- Driver Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php
    $total_drivers = $result ? $result->num_rows : 0;
    $active_count = 0;
    $total_earnings = 0;
    
    if ($result) {
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            if ($row['status'] === 'Active') $active_count++;
            $total_earnings += $row['total_earnings'] ?? 0;
        }
        $result->data_seek(0); // Reset for table display
    }
    
    $todayCode = date('D');
    $today_trips = $conn->query("SELECT COUNT(*) as count FROM schedule WHERE weekdays = '" . $conn->real_escape_string($todayCode) . "'")->fetch_assoc()['count'];
    ?>
    
    <div class="bg-white rounded-xl shadow-lg p-6 card-hover border-l-4 border-blue-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Drivers</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo $total_drivers; ?></p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-users text-blue-600 text-xl"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 card-hover border-l-4 border-green-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Active Drivers</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo $active_count; ?></p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-user-check text-green-600 text-xl"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 card-hover border-l-4 border-orange-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Today's Trips</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo $today_trips; ?></p>
        </div>
        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-route text-orange-600 text-xl"></i>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6 card-hover border-l-4 border-purple-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Earnings</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo formatCurrency($total_earnings); ?></p>
        </div>
        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Drivers Table -->
  <div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-900">Driver Roster</h3>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Earnings</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php $result->data_seek(0); ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12">
                      <?php if (!empty($row['profile_url'])): ?>
                        <img src="<?php echo htmlspecialchars($row['profile_url']); ?>" alt="Profile" class="h-12 w-12 rounded-full object-cover border cursor-pointer hover:opacity-80 transition-opacity js-view-profile" data-image="<?php echo htmlspecialchars($row['profile_url']); ?>" onerror="this.style.display='none'">
                      <?php else: ?>
                        <div class="h-12 w-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                          <span class="text-white font-semibold text-sm"><?php echo strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                      </div>
                      <div class="text-sm text-gray-500">
                        @<?php echo htmlspecialchars($row['username']); ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <div class="flex items-center text-sm text-gray-900">
                      <i class="fas fa-phone text-gray-400 mr-2"></i>
                      <?php echo htmlspecialchars($row['phone']); ?>
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                      <i class="fas fa-id-card text-gray-400 mr-2"></i>
                      License: <?php echo htmlspecialchars($row['license_number']); ?>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php echo getStatusBadge($row['status'], 'driver'); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-900">
                      <i class="fas fa-route text-gray-400 mr-1"></i>
                      <?php echo $row['total_trips']; ?> total trips
                    </div>
                    <div class="text-xs text-gray-500">
                      <span class="text-green-600"><?php echo $row['completed_trips']; ?> completed</span> | 
                      <span class="text-blue-600"><?php echo $row['active_trips']; ?> active</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    <i class="fas fa-dollar-sign text-green-500 mr-1"></i>
                    <?php echo formatCurrency($row['total_earnings'] ?? 0); ?>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">
                    <i class="fas fa-clock text-gray-400 mr-1"></i>
                    <?php echo $row['last_trip_date'] ? date('M j, Y', strtotime($row['last_trip_date'])) : 'No trips'; ?>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-3">
                    <a href="driver_view.php?id=<?php echo $row['driver_id']; ?>" 
                       class="text-gray-700 hover:text-gray-900 transition-colors" title="View Driver">
                      <i class="fas fa-eye text-lg"></i>
                    </a>
                    <a href="driver_edit.php?id=<?php echo $row['driver_id']; ?>" 
                       class="text-blue-600 hover:text-blue-900 transition-colors" title="Edit Driver">
                      <i class="fas fa-edit text-lg"></i>
                    </a>
                    <a href="#" 
                       class="text-red-600 hover:text-red-900 transition-colors js-delete-driver" title="Delete Driver"
                       data-id="<?php echo $row['driver_id']; ?>">
                      <i class="fas fa-trash text-lg"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <i class="fas fa-user-tie text-4xl text-gray-300 mb-4"></i>
                  <h3 class="text-lg font-medium text-gray-900 mb-2">No drivers found</h3>
                  <p class="text-gray-500 mb-4">Get started by adding your first driver to the team.</p>
                  <a href="driver_add.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>
                    Add Driver
                  </a>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/confirmation_modal.php'; ?>

<!-- Profile Image Viewer Modal -->
<div id="profileImageViewer" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center">
    <div class="relative max-w-4xl max-h-full p-4">
        <button id="closeProfileViewer" class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75 transition-colors z-10">
            <i class="fas fa-times"></i>
        </button>
        <img id="profileImageFull" src="" alt="Profile Picture" class="max-w-full max-h-full rounded-lg shadow-2xl">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Profile image viewer functionality
        const profileViewer = document.getElementById('profileImageViewer');
        const profileImageFull = document.getElementById('profileImageFull');
        const closeProfileViewer = document.getElementById('closeProfileViewer');
        
        // Handle profile image clicks
        document.querySelectorAll('.js-view-profile').forEach(function (img) {
            img.addEventListener('click', function (e) {
                e.preventDefault();
                const imageUrl = this.getAttribute('data-image');
                if (imageUrl) {
                    profileImageFull.src = imageUrl;
                    profileViewer.classList.remove('hidden');
                    document.body.style.overflow = 'hidden'; // Prevent background scrolling
                }
            });
        });
        
        // Close profile viewer when clicking close button
        if (closeProfileViewer) {
            closeProfileViewer.addEventListener('click', function () {
                profileViewer.classList.add('hidden');
                document.body.style.overflow = ''; // Restore scrolling
            });
        }
        
        // Close profile viewer when clicking background
        if (profileViewer) {
            profileViewer.addEventListener('click', function (e) {
                if (e.target === profileViewer) {
                    profileViewer.classList.add('hidden');
                    document.body.style.overflow = ''; // Restore scrolling
                }
            });
        }
        
        // Close profile viewer with Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !profileViewer.classList.contains('hidden')) {
                profileViewer.classList.add('hidden');
                document.body.style.overflow = ''; // Restore scrolling
            }
        });
        
        // Delete driver functionality
        document.querySelectorAll('.js-delete-driver').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const driverId = this.getAttribute('data-id');
                const deleteUrl = 'driver_delete.php?id=' + driverId;
                
                window.ConfirmationModal.show(
                    'Delete Driver',
                    'Are you sure you want to delete this driver? This action cannot be undone.',
                    deleteUrl,
                    'Delete'
                );
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
