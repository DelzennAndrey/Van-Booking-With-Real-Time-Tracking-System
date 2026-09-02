<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
// Note: Include header after all potential redirects to avoid 'headers already sent' warnings

$van_id = $_GET['id'] ?? null;
$van = null;
$drivers = [];

if ($van_id) {
    // Fetch van details
    $stmt = $conn->prepare("SELECT * FROM van WHERE van_id = ?");
    $stmt->bind_param("i", $van_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $van = $result->fetch_assoc();
    $stmt->close();

    if (!$van) {
        $_SESSION['error_message'] = "Van not found.";
        header("Location: vans.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "No van ID provided.";
    header("Location: vans.php");
    exit();
}

// Fetch available drivers - exclude drivers already assigned to other vans, but include current van's driver
$drivers_result = $conn->query("SELECT d.driver_id, d.first_name, d.last_name 
                                FROM driver d 
                                WHERE LOWER(d.status) = 'active' 
                                AND (d.driver_id NOT IN (SELECT DISTINCT driver_id FROM van WHERE driver_id IS NOT NULL) 
                                     OR d.driver_id = " . intval($van['driver_id']) . ")
                                ORDER BY d.first_name");
if ($drivers_result) {
    while ($row = $drivers_result->fetch_assoc()) {
        $drivers[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $van_number = trim($_POST['van_number']);
    $van_number_int = (int)$van_number;
    $plate_number = trim($_POST['plate_number']);
    $model = trim($_POST['model']);
    $color = trim($_POST['color']);
    $capacity = (int)$_POST['capacity'];
    $driver_id = $_POST['driver_id'] === '' ? null : (int)$_POST['driver_id'];
    $status = $_POST['status'];
    $error_message = '';

    if (empty($van_number) || empty($plate_number) || empty($capacity) || empty($status)) {
        $error_message = "All fields are required.";
    } else {
        // Check if driver is already assigned to another van (excluding current van)
        if ($driver_id && $driver_id != $van['driver_id']) {
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM van WHERE driver_id = ? AND van_id != ?");
            $checkStmt->bind_param('ii', $driver_id, $van_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $checkStmt->close();
            
            if ($count > 0) {
                $error_message = 'Selected driver is already assigned to another van.';
            }
        }

        // Check if van number already exists (excluding current van)
        if (empty($error_message) && $van_number != $van['van_number']) {
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM van WHERE van_number = ? AND van_id != ?");
            $checkStmt->bind_param('ii', $van_number_int, $van_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $checkStmt->close();
            
            if ($count > 0) {
                $error_message = 'Van number already exists.';
            }
        }

        // Check if plate number already exists (excluding current van)
        if (empty($error_message) && $plate_number != $van['plate_number']) {
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM van WHERE plate_number = ? AND van_id != ?");
            $checkStmt->bind_param('si', $plate_number, $van_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $count = $result->fetch_assoc()['count'];
            $checkStmt->close();
            
            if ($count > 0) {
                $error_message = 'Plate number already exists.';
            }
        }

        if (empty($error_message)) {
            $setDriver = ($driver_id === null) ? "NULL" : "?";
            $sql = "UPDATE van SET van_number = ?, plate_number = ?, capacity = ?, status = ?, model = ?, color = ?, driver_id = $setDriver WHERE van_id = ?";
            $stmt = $conn->prepare($sql);
            if ($driver_id === null) {
                $stmt->bind_param("isisssi", $van_number_int, $plate_number, $capacity, $status, $model, $color, $van_id);
            } else {
                $stmt->bind_param("isisssii", $van_number_int, $plate_number, $capacity, $status, $model, $color, $driver_id, $van_id);
            }

            if ($stmt->execute()) {
                header("Location: vans.php?status=success&message=" . urlencode('Van updated successfully'));
                exit();
            } else {
                $error_message = "Error updating van: " . $conn->error;
            }
            $stmt->close();
        }
    }

    if (!empty($error_message)) {
        $_SESSION['error_message'] = $error_message;
    }
}
?>

<?php include 'includes/header.php'; ?>

<!-- Main Content Container -->
<div class="flex-1 p-4">
  <!-- Header Section -->
  <div class="mb-8">
    <div class="flex items-center space-x-4 mb-4">
      <a href="vans.php" class="text-gray-500 hover:text-gray-700 transition-colors">
        <i class="fas fa-arrow-left text-xl"></i>
      </a>
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Van</h1>
        <p class="text-gray-600">Update van information and assignments</p>
      </div>
    </div>
  </div>

  <!-- Alert Messages -->
  <?php if (isset($_SESSION['error_message'])): ?>
    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-circle text-red-400"></i>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-700"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        </div>
      </div>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['success_message'])): ?>
    <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-check-circle text-green-400"></i>
        </div>
        <div class="ml-3">
          <p class="text-sm text-green-700"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Form Container -->
  <div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-8">

      <form action="van_edit.php?id=<?php echo htmlspecialchars($van_id); ?>" method="POST" class="space-y-6">
        <!-- Van Information Section -->
        <div class="border-b border-gray-200 pb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-bus text-blue-600 mr-2"></i>
            Van Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="van_number" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-hashtag text-gray-400 mr-1"></i>
                Van Number
              </label>
              <input type="text" id="van_number" name="van_number" 
                     value="<?php echo htmlspecialchars($van['van_number'] ?? ''); ?>" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                     placeholder="Enter van number" required>
            </div>
            <div>
              <label for="plate_number" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-id-card text-gray-400 mr-1"></i>
                Plate Number
              </label>
              <input type="text" id="plate_number" name="plate_number" 
                     value="<?php echo htmlspecialchars($van['plate_number'] ?? ''); ?>" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                     placeholder="Enter plate number" required>
            </div>
            <div>
              <label for="model" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-car-side text-gray-400 mr-1"></i>
                Model
              </label>
              <input type="text" id="model" name="model" 
                     value="<?php echo htmlspecialchars($van['model'] ?? ''); ?>" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                     placeholder="Enter model" required>
            </div>
            <div>
              <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-palette text-gray-400 mr-1"></i>
                Color
              </label>
              <input type="text" id="color" name="color" 
                     value="<?php echo htmlspecialchars($van['color'] ?? ''); ?>" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                     placeholder="Enter color" required>
            </div>
          </div>
        </div>

        <!-- Capacity and Assignment Section -->
        <div class="border-b border-gray-200 pb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-users text-green-600 mr-2"></i>
            Capacity & Assignment
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user-friends text-gray-400 mr-1"></i>
                Capacity
              </label>
              <input type="number" id="capacity" name="capacity" 
                     value="<?php echo htmlspecialchars($van['capacity'] ?? ''); ?>" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                     placeholder="Enter capacity" min="1" max="50" required>
            </div>
            <div>
              <label for="driver_id" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user-tie text-gray-400 mr-1"></i>
                Assigned Driver
              </label>
              <select id="driver_id" name="driver_id" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                <option value="">-- No Driver Assigned --</option>
                <?php foreach ($drivers as $driver): ?>
                  <option value="<?php echo htmlspecialchars($driver['driver_id']); ?>" 
                          <?php echo (isset($van['driver_id']) && $van['driver_id'] == $driver['driver_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                    <?php if (isset($van['driver_id']) && $van['driver_id'] == $driver['driver_id']): ?>
                      (Currently Assigned)
                    <?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <p class="text-xs text-gray-500 mt-1">Only unassigned drivers and the current driver are available for selection.</p>
            </div>
          </div>
        </div>

        <!-- Status Section -->
        <div class="pb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-cog text-purple-600 mr-2"></i>
            Status
          </h3>
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
              <i class="fas fa-toggle-on text-gray-400 mr-1"></i>
              Van Status
            </label>
            <select id="status" name="status" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" required>
              <option value="active" <?php echo (isset($van['status']) && $van['status'] == 'active') ? 'selected' : ''; ?>>
                <i class="fas fa-check-circle text-green-500 mr-2"></i>Active
              </option>
              <option value="maintenance" <?php echo (isset($van['status']) && $van['status'] == 'maintenance') ? 'selected' : ''; ?>>
                <i class="fas fa-tools text-yellow-500 mr-2"></i>Maintenance
              </option>
              <option value="inactive" <?php echo (isset($van['status']) && $van['status'] == 'inactive') ? 'selected' : ''; ?>>
                <i class="fas fa-ban text-red-500 mr-2"></i>Inactive
              </option>
            </select>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
          <a href="vans.php" 
             class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Vans
          </a>
          <button type="submit" 
                  class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
            <i class="fas fa-save mr-2"></i>
            Update Van
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
