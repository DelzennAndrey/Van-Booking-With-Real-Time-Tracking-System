<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: routes.php');
    exit();
}
$route_id = intval($_GET['id']);

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route = trim($_POST['route']);

    if ($route === '') {
        $error = 'Route name is required.';
    } else {
        // Optional: uniqueness check excluding current record
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM route WHERE route = ? AND route_id != ?");
        $checkStmt->bind_param('si', $route, $route_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $exists = $result && ($result->fetch_assoc()['count'] ?? 0) > 0;
        $checkStmt->close();

        if ($exists) {
            $error = 'Another route with the same name already exists.';
        } else {
            $stmt = $conn->prepare("UPDATE route SET route = ? WHERE route_id = ?");
            $stmt->bind_param('si', $route, $route_id);
            if ($stmt->execute()) {
                header('Location: routes.php');
                exit();
            } else {
                $error = 'Error updating route.';
            }
            $stmt->close();
        }
    }
}

// Fetch current route
$stmt = $conn->prepare("SELECT * FROM route WHERE route_id = ?");
$stmt->bind_param('i', $route_id);
$stmt->execute();
$result = $stmt->get_result();
$routeRow = $result->fetch_assoc();
$stmt->close();

if (!$routeRow) {
    include 'includes/header.php';
    echo '<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">Route not found.</div>';
    include 'includes/footer.php';
    exit();
}

include 'includes/header.php';
?>
<div class="max-w-xl mx-auto mt-10 bg-white rounded-xl shadow-lg p-8">
  <h2 class="text-2xl font-bold text-blue-700 mb-6">Edit Route</h2>
  <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 border border-red-200 text-sm"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="POST" class="space-y-6">
      <div>
          <label class="block mb-1 font-medium text-gray-700">Route</label>
          <input type="text" name="route" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" value="<?php echo htmlspecialchars($routeRow['route']); ?>" required>
      </div>
      <div class="flex gap-3 pt-2">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition">Update Route</button>
          <a href="routes.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-lg transition">Cancel</a>
      </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
