<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Handle status messages
$status = '';
$message = '';
if (isset($_GET['status']) && isset($_GET['message'])) {
  $status = htmlspecialchars($_GET['status']);
  $message = htmlspecialchars(urldecode($_GET['message']));
}

// Fetch routes with optional search filter
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$baseSql = "SELECT route_id, route FROM route";
if ($search !== '') {
  $like = '%' . $search . '%';
  $where = " WHERE route LIKE ?";
  $order = " ORDER BY route_id DESC";
  $stmt = $conn->prepare($baseSql . $where . $order);
  $stmt->bind_param('s', $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $sql = $baseSql . " ORDER BY route_id DESC";
  $result = $conn->query($sql);
}
?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <div class="max-w-3xl mx-auto">
    <!-- Status Messages -->
    <?php if ($status && $message): ?>
      <div class="mb-4">
        <div class="p-4 rounded-md <?php echo $status === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
          <div class="flex">
            <div class="flex-shrink-0">
              <?php if ($status === 'success'): ?>
                <i class="fas fa-check-circle text-green-400"></i>
              <?php else: ?>
                <i class="fas fa-exclamation-circle text-red-400"></i>
              <?php endif; ?>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium <?php echo $status === 'success' ? 'text-green-800' : 'text-red-800'; ?>">
                <?php echo $message; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Routes</h1>
      <div class="flex items-center gap-3">
        <form method="get" class="flex items-center gap-2">
          <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search routes..."
                 class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Search</button>
          <?php if ($search !== ''): ?>
            <a href="routes.php" class="text-sm text-gray-600 hover:text-gray-800 underline">Clear</a>
          <?php endif; ?>
        </form>
        <a href="route_add.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">Add New Route</a>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <?php echo htmlspecialchars($row['route']); ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                  <a href="route_edit.php?id=<?php echo $row['route_id']; ?>" class="text-blue-600 hover:text-blue-900 mx-2">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="#" class="text-red-600 hover:text-red-900 mx-2 js-delete-route" data-id="<?php echo $row['route_id']; ?>">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="2" class="text-center py-6 text-gray-500">
                No routes found. <a class="text-blue-600 hover:underline" href="route_add.php">Add one</a>.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'includes/confirmation_modal.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-delete-route').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        const deleteUrl = 'route_delete.php?id=' + id;
        if (window.ConfirmationModal && typeof window.ConfirmationModal.show === 'function') {
          window.ConfirmationModal.show(
            'Delete Route',
            'Are you sure you want to delete this route? This action cannot be undone.',
            deleteUrl,
            'Delete'
          );
        } else {
          if (confirm('Delete this route?')) {
            window.location.href = deleteUrl;
          }
        }
      });
    });
  });
</script>

<?php include 'includes/footer.php'; ?>
