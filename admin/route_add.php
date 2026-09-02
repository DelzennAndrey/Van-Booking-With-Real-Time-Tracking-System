<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route = trim($_POST['route']);

    if ($route === '') {
        $error = 'Route name is required.';
    } else {
        // Enforce strong uniqueness: equal, contains, or contained by (case-insensitive)
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM route 
                                     WHERE LOWER(route) = LOWER(?)
                                        OR LOCATE(LOWER(?), LOWER(route)) > 0
                                        OR LOCATE(LOWER(route), LOWER(?)) > 0");
        $checkStmt->bind_param('sss', $route, $route, $route);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $exists = $result && ($result->fetch_assoc()['count'] ?? 0) > 0;
        $checkStmt->close();

        if ($exists) {
            $error = 'Route already exists.';
        } else {
            $stmt = $conn->prepare("INSERT INTO route (route) VALUES (?)");
            $stmt->bind_param('s', $route);
            if ($stmt->execute()) {
                header('Location: routes.php');
                exit();
            } else {
                $error = 'Error adding route.';
            }
            $stmt->close();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="max-w-xl mx-auto mt-10 bg-white rounded-xl shadow-lg p-8">
  <h2 class="text-2xl font-bold text-blue-700 mb-6">Add New Route</h2>
  <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 border border-red-200 text-sm"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="POST" class="space-y-6">
      <div>
          <label class="block mb-1 font-medium text-gray-700">Route</label>
          <input type="text" name="route" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" placeholder="e.g., Molave" required>
      </div>
      <div class="flex gap-3 pt-2">
          <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition">Add Route</button>
          <a href="routes.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-lg transition">Cancel</a>
      </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
