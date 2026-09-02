<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

$error = '';

// Fetch drivers for dropdown - only show active drivers who are NOT already assigned to a van
$drivers = $conn->query("SELECT d.driver_id, d.first_name, d.last_name 
                         FROM driver d 
                         WHERE d.status = 'active' 
                         AND d.driver_id NOT IN (SELECT DISTINCT driver_id FROM van WHERE driver_id IS NOT NULL)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $van_number = intval($_POST['van_number']);
    $plate_number = trim($_POST['plate_number']);
    $model = trim($_POST['model']);
    $color = trim($_POST['color']);
    $capacity = intval($_POST['capacity']);
    $status = strtolower(trim($_POST['status']));
    $driver_id = isset($_POST['driver_id']) && $_POST['driver_id'] !== '' ? intval($_POST['driver_id']) : null;

    // Check if driver is already assigned to another van
    if ($driver_id) {
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM van WHERE driver_id = ?");
        $checkStmt->bind_param('i', $driver_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $checkStmt->close();
        
        if ($count > 0) {
            $error = 'Selected driver is already assigned to another van.';
        }
    }

    // Check if van number already exists
    if (empty($error)) {
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM van WHERE van_number = ?");
        $checkStmt->bind_param('i', $van_number);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $checkStmt->close();
        
        if ($count > 0) {
            $error = 'Van number already exists.';
        }
    }

    // Check if plate number already exists
    if (empty($error)) {
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM van WHERE plate_number = ?");
        $checkStmt->bind_param('s', $plate_number);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $checkStmt->close();
        
        if ($count > 0) {
            $error = 'Plate number already exists.';
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO van (van_number, plate_number, capacity, status, driver_id, model, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('isisiss', $van_number, $plate_number, $capacity, $status, $driver_id, $model, $color);
        if ($stmt->execute()) {
            header('Location: vans.php');
            exit();
        } else {
            $error = 'Error adding van.';
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>
<div class="max-w-2xl mx-auto mt-10 bg-white rounded-xl shadow-lg p-8">
  <h2 class="text-2xl font-bold text-blue-700 mb-6">Add New Van</h2>
  <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 border border-red-200 text-sm"><?php echo $error; ?></div>
  <?php endif; ?>
  <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
          <label class="block mb-1 font-medium text-gray-700">Van Number</label>
          <input type="number" name="van_number" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" required>
      </div>
      <div>
          <label class="block mb-1 font-medium text-gray-700">Plate Number</label>
          <input type="text" name="plate_number" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" required>
      </div>
      <div>
          <label class="block mb-1 font-medium text-gray-700">Model</label>
          <input type="text" name="model" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" required>
      </div>
      <div>
          <label class="block mb-1 font-medium text-gray-700">Color</label>
          <input type="text" name="color" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" required>
      </div>
      <div>
          <label class="block mb-1 font-medium text-gray-700">Capacity</label>
          <input type="number" name="capacity" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" required>
      </div>
      <div>
          <label class="block mb-1 font-medium text-gray-700">Status</label>
          <select name="status" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
          </select>
      </div>
      <div class="md:col-span-2">
          <label class="block mb-1 font-medium text-gray-700">Driver</label>
          <select name="driver_id" class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 p-3 text-gray-800">
              <option value="">— No Driver Assigned —</option>
              <?php if ($drivers && $drivers->num_rows > 0): ?>
                  <?php while ($d = $drivers->fetch_assoc()): ?>
                      <option value="<?php echo $d['driver_id']; ?>">
                          <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?>
                      </option>
                  <?php endwhile; ?>
              <?php else: ?>
                  <option value="">No available drivers (all are already assigned)</option>
              <?php endif; ?>
          </select>
          <p class="text-xs text-gray-500 mt-1">Driver selection is optional. Only unassigned drivers are shown.</p>
      </div>
      <div class="md:col-span-2 flex gap-4 mt-2">
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition">Add Van</button>
          <a href="vans.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-lg transition">Cancel</a>
      </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
