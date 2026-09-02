<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: drivers.php');
    exit();
}
$driver_id = intval($_GET['id']);

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = trim($_POST['dob']);
    // compute age from dob
    $age = 0;
    if ($dob) {
        try {
            $dobDt = new DateTime($dob);
            $now = new DateTime('today');
            $age = (int)$dobDt->diff($now)->y;
        } catch (Exception $e) {
            $age = 0;
        }
    }
    $address = trim($_POST['address']);
    $phone_number = trim($_POST['phone_number']);
    $license_number = trim($_POST['license_number']);
    $status = trim($_POST['status']);

    // get current profile_url
    $current_profile_url = '';
    $cur = $conn->prepare("SELECT profile_url FROM driver WHERE driver_id = ?");
    $cur->bind_param('i', $driver_id);
    $cur->execute();
    $curRes = $cur->get_result();
    if ($curRes && ($row = $curRes->fetch_assoc())) {
        $current_profile_url = $row['profile_url'] ?? '';
    }
    $cur->close();

    // handle file upload if provided
    $profile_url = $current_profile_url;
    if (isset($_FILES['profile']) && is_array($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR . 'driverProfile';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        $origName = $_FILES['profile']['name'];
        $tmpName = $_FILES['profile']['tmp_name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed, true)) {
            $newName = 'driver_' . $driver_id . '_' . uniqid('', true) . '.' . $ext;
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;
            if (move_uploaded_file($tmpName, $destPath)) {
                // use relative URL path for storage
                $profile_url = 'assets/image/driverProfile/' . $newName;
                // optionally remove old file if different
                if (!empty($current_profile_url)) {
                    $oldAbs = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $current_profile_url);
                    if (is_file($oldAbs)) { @unlink($oldAbs); }
                }
            } else {
                $error = 'Failed to upload profile image.';
            }
        } else {
            $error = 'Invalid image type. Allowed: JPG, JPEG, PNG, WEBP.';
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE driver SET first_name=?, last_name=?, dob=?, age=?, address=?, phone_number=?, license_number=?, status=?, profile_url=? WHERE driver_id=?");
        $stmt->bind_param('sssisssssi', $first_name, $last_name, $dob, $age, $address, $phone_number, $license_number, $status, $profile_url, $driver_id);
        if ($stmt->execute()) {
            header('Location: drivers.php');
            exit();
        } else {
            $error = 'Error updating driver.';
        }
        $stmt->close();
    }
}

// Fetch driver data
$stmt = $conn->prepare("SELECT * FROM driver WHERE driver_id = ?");
$stmt->bind_param('i', $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();
$stmt->close();

if (!$driver) {
    echo '<div class="alert alert-danger">Driver not found.</div>';
    include 'includes/footer.php';
    exit();
}
include 'includes/header.php';
?>
<div class="max-w-2xl mx-auto mt-10">
  <nav class="mb-6 text-sm text-gray-500">
    <a href="dashboard.php" class="hover:underline">Dashboard</a> /
    <a href="drivers.php" class="hover:underline">Drivers</a> /
    <span class="text-gray-700">Edit Driver</span>
  </nav>
  <div class="bg-white shadow rounded-lg p-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Driver</h2>
    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-4" enctype="multipart/form-data">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-700 mb-1">First Name</label>
          <input type="text" name="first_name" class="w-full border border-gray-300 rounded px-3 py-2" value="<?php echo htmlspecialchars($driver['first_name']); ?>" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Last Name</label>
          <input type="text" name="last_name" class="w-full border border-gray-300 rounded px-3 py-2" value="<?php echo htmlspecialchars($driver['last_name']); ?>" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Date of Birth</label>
          <input type="date" name="dob" class="w-full border border-gray-300 rounded px-3 py-2" value="<?php echo htmlspecialchars($driver['dob']); ?>" required>
          <p class="text-xs text-gray-500 mt-1">Age is computed automatically from DOB.</p>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Address</label>
          <input type="text" name="address" class="w-full border border-gray-300 rounded px-3 py-2" value="<?php echo htmlspecialchars($driver['address']); ?>" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Phone Number</label>
          <input type="text" name="phone_number" class="w-full border border-gray-300 rounded px-3 py-2" value="<?php echo htmlspecialchars($driver['phone_number']); ?>" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">License Number</label>
          <input type="text" name="license_number" class="w-full border border-gray-300 rounded px-3 py-2" value="<?php echo htmlspecialchars($driver['license_number']); ?>" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Profile Photo</label>
          <?php if (!empty($driver['profile_url'])): ?>
            <div class="mb-2">
              <img src="<?php echo htmlspecialchars($driver['profile_url']); ?>" alt="Profile" class="h-16 w-16 rounded-full object-cover border" onerror="this.style.display='none'">
            </div>
          <?php endif; ?>
          <input type="file" name="profile" accept="image/*" class="w-full border border-gray-300 rounded px-3 py-2" />
          <p class="text-xs text-gray-500 mt-1">Allowed: JPG, JPEG, PNG, WEBP.</p>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Status</label>
          <select name="status" class="w-full border border-gray-300 rounded px-3 py-2" required>
            <option value="Active" <?php if ($driver['status'] == 'Active') echo 'selected'; ?>>Active</option>
            <option value="Inactive" <?php if ($driver['status'] == 'Inactive') echo 'selected'; ?>>Inactive</option>
          </select>
        </div>
      </div>
      <div class="flex justify-between mt-6">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Update Driver</button>
        <a href="drivers.php" class="text-gray-600 hover:underline">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?> 