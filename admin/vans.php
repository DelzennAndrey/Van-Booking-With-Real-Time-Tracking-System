<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Fetch vans with optional search filter
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$baseSql = "SELECT van.*, CONCAT(driver.first_name, ' ', driver.last_name) AS driver_name 
        FROM van 
        LEFT JOIN driver ON van.driver_id = driver.driver_id";
if ($search !== '') {
  $like = '%' . $search . '%';
  $where = " WHERE 
    van.van_number LIKE ? OR van.plate_number LIKE ? OR van.model LIKE ? OR van.color LIKE ? OR CAST(van.capacity AS CHAR) LIKE ?
    OR CONCAT(driver.first_name, ' ', driver.last_name) LIKE ? OR driver.first_name LIKE ? OR driver.last_name LIKE ?
    OR van.status LIKE ?";
  $order = " ORDER BY van.van_id DESC";
  $stmt = $conn->prepare($baseSql . $where . $order);
  $stmt->bind_param('sssssssss', $like, $like, $like, $like, $like, $like, $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $sql = $baseSql . " ORDER BY van.van_id DESC";
  $result = $conn->query($sql);
}
?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
    <?php
      $status = $_GET['status'];
      $message = htmlspecialchars($_GET['message']);
      $isSuccess = $status === 'success';
    ?>
    <div class="mb-4 px-4 py-3 rounded border <?php echo $isSuccess ? 'bg-green-50 border-green-400 text-green-800' : 'bg-red-50 border-red-400 text-red-800'; ?>">
      <?php echo $message; ?>
    </div>
  <?php endif; ?>
  <!-- Header Section -->
  <div class="mb-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Van Management</h1>
        <p class="text-gray-600">Manage your fleet of vans and their assignments</p>
      </div>
      <div class="mt-4 lg:mt-0 flex items-center gap-3">
        <a href="van_add.php"
          class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
          <i class="fas fa-plus mr-2 text-lg"></i>
          Add New Van
        </a>
        <form method="get" class="flex items-center gap-2">
          <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search vans..."
                 class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Search</button>
          <?php if ($search !== ''): ?>
            <a href="vans.php" class="text-sm text-gray-600 hover:text-gray-800 underline">Clear</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <!-- Van Statistics -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php
    $total_vans = $result ? $result->num_rows : 0;
    $active_vans = $maintenance_vans = $inactive_vans = 0;

    if ($result) {
      $result->data_seek(0);
      while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status']);
        if ($status === 'active')
          $active_vans++;
        elseif ($status === 'maintenance')
          $maintenance_vans++;
        else
          $inactive_vans++;
      }
      $result->data_seek(0); // Reset for table display
    }
    ?>

    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Total Vans</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo $total_vans; ?></p>
        </div>
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-bus text-blue-600 text-2xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Active Vans</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo $active_vans; ?></p>
        </div>
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-check-circle text-green-600 text-2xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Maintenance</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo $maintenance_vans; ?></p>
        </div>
        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-tools text-yellow-600 text-2xl"></i>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-medium text-gray-600">Inactive Vans</p>
          <p class="text-3xl font-bold text-gray-900"><?php echo $inactive_vans; ?></p>
        </div>
        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
          <i class="fas fa-ban text-red-600 text-2xl"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="bg-white shadow rounded-lg overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Van</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plate Number</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?php echo htmlspecialchars($row['van_number']); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?php echo htmlspecialchars($row['plate_number']); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?php echo htmlspecialchars($row['model'] ?? ''); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?php echo htmlspecialchars($row['color'] ?? ''); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?php echo htmlspecialchars($row['capacity']); ?> seats
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <?php echo $row['driver_name'] ? htmlspecialchars($row['driver_name']) : '<span class="italic text-gray-400">Unassigned</span>'; ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <?php echo getStatusBadge($row['status'], 'van'); ?>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                <a href="van_edit.php?id=<?php echo $row['van_id']; ?>" class="text-blue-600 hover:text-blue-900 mx-1"
                  title="Edit Van">
                  <i class="fas fa-edit text-lg"></i>
                </a>
                <a href="#" class="text-red-600 hover:text-red-900 mx-1 js-delete-van"
                  data-id="<?php echo $row['van_id']; ?>" title="Delete Van">
                  <i class="fas fa-trash text-lg"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center py-6 text-gray-500">
              No vans found.
              <a href="van_add.php" class="text-blue-600 hover:underline">Add one</a>.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/confirmation_modal.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-delete-van').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const vanId = this.getAttribute('data-id');
                const deleteUrl = 'van_delete.php?id=' + vanId;
                
                window.ConfirmationModal.show(
                    'Delete Van',
                    'Are you sure you want to delete this van? This action cannot be undone.',
                    deleteUrl,
                    'Delete'
                );
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>