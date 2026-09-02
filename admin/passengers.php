<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Fetch passengers with optional search filter
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$baseSql = "SELECT * FROM passenger";
if ($search !== '') {
    $like = '%' . $search . '%';
    $where = " WHERE 
        first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?
        OR gender LIKE ? OR type LIKE ?
        OR CAST(no_show AS CHAR) LIKE ?";
    $order = " ORDER BY last_name, first_name";
    $stmt = $conn->prepare($baseSql . $where . $order);
    $stmt->bind_param('sssssss', $like, $like, $like, $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = $baseSql . " ORDER BY last_name, first_name";
    $result = $conn->query($sql);
}
?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <div class="max-w-6xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-blue-700">Passenger Management</h2>
    <form method="get" class="flex items-center gap-2">
      <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search passengers..."
             class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm">Search</button>
      <?php if ($search !== ''): ?>
        <a href="passengers.php" class="text-sm text-gray-600 hover:text-gray-800 underline">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  <div class="overflow-x-auto bg-white rounded-xl shadow-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-blue-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Name</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Gender</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Age</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Type</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">No-Show Count</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Verified</th>
          <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="hover:bg-blue-50 transition">
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                <?php if ((int)$row['no_show'] >= 3): ?>
                  <span class="ml-2 inline-block px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">Restricted</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['gender']); ?></td>
              <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['age']); ?></td>
              <td class="px-4 py-2 whitespace-nowrap text-sm">
                <?php echo getStatusBadge($row['type'], 'passenger'); ?>
              </td>
          
              <td class="px-4 py-2 whitespace-nowrap text-sm">
                <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                  <?php echo $row['no_show'] > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                  <?php echo htmlspecialchars($row['no_show']); ?>
                </span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm">
                <?php $verified = isset($row['is_verified']) ? (int)$row['is_verified'] : 0; ?>
                <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php echo $verified ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                  <?php echo $verified ? 'Verified' : 'Unverified'; ?>
                </span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap text-sm space-x-2">
                <a href="passenger_view.php?id=<?php echo $row['passenger_id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-semibold transition">View</a>
                <?php if ((int)$row['no_show'] >= 3): ?>
                  <a href="#" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs font-semibold transition js-unrestrict-passenger" data-id="<?php echo $row['passenger_id']; ?>">Unrestrict</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center px-4 py-6 text-gray-500">No passengers found.</td>
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
        // Handle Unrestrict action
        document.querySelectorAll('.js-unrestrict-passenger').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const passengerId = this.getAttribute('data-id');
                const unrestrictUrl = 'passenger_unrestrict.php?id=' + passengerId;

                window.ConfirmationModal.show(
                    'Unrestrict Passenger',
                    'This will reset the passenger\'s No-Show count to 0 and remove the restriction. Continue?',
                    unrestrictUrl,
                    'Unrestrict'
                );
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
