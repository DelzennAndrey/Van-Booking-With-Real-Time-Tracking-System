<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();


$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = trim($_POST['dob']);
    $address = trim($_POST['address']);
    $phone_number = trim($_POST['phone_number']);
    $license_number = trim($_POST['license_number']);
    $status = trim($_POST['status']);

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

    // handle profile upload (optional)
    $profile_url = '';
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
            $newName = 'driver_' . uniqid('', true) . '.' . $ext;
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;
            if (move_uploaded_file($tmpName, $destPath)) {
                $profile_url = 'assets/image/driverProfile/' . $newName;
            } else {
                $error = 'Failed to upload profile image.';
            }
        } else {
            $error = 'Invalid image type. Allowed: JPG, JPEG, PNG, WEBP.';
        }
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = 'Username already exists.';
    } else {
        // Insert into user table
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt_insert_user = $conn->prepare("INSERT INTO user (username, password, role) VALUES (?, ?, 'driver')");
        $stmt_insert_user->bind_param('ss', $username, $hashed_password);
        if ($stmt_insert_user->execute()) {
            $user_id = $stmt_insert_user->insert_id;
            // Insert into driver table with profile_url
            $stmt_insert_driver = $conn->prepare("INSERT INTO driver (user_id, first_name, last_name, dob, age, address, phone_number, license_number, status, profile_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert_driver->bind_param('isssisssss', $user_id, $first_name, $last_name, $dob, $age, $address, $phone_number, $license_number, $status, $profile_url);
            if ($stmt_insert_driver->execute()) {
                header('Location: drivers.php');
                exit();
            } else {
                $error = 'Error adding driver.';
            }
            $stmt_insert_driver->close();
        } else {
            $error = 'Error creating user account.';
        }
        $stmt_insert_user->close();
    }
    $stmt->close();
}
?>
<?php include 'includes/header.php'; ?>
<div class="max-w-2xl mx-auto mt-10">
  <nav class="mb-6 text-sm text-gray-500">
    <a href="dashboard.php" class="hover:underline">Dashboard</a> /
    <a href="drivers.php" class="hover:underline">Drivers</a> /
    <span class="text-gray-700">Add Driver</span>
  </nav>
  <div class="bg-white shadow rounded-lg p-8">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Add New Driver</h2>

    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4" enctype="multipart/form-data">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-700 mb-1">Username</label>
          <input type="text" name="username" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Password</label>
          <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300" required>
          <label class="inline-flex items-center mt-2 text-sm text-gray-600">
            <input type="checkbox" id="togglePassword" class="mr-2">
            Show password
          </label>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">First Name</label>
          <input type="text" name="first_name" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Last Name</label>
          <input type="text" name="last_name" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Date of Birth</label>
          <input type="date" name="dob" class="w-full border border-gray-300 rounded px-3 py-2" required>
          <p class="text-xs text-gray-500 mt-1">Age is computed automatically from DOB.</p>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Address</label>
          <input type="text" name="address" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Phone Number</label>
          <input type="text" name="phone_number" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">License Number</label>
          <input type="text" name="license_number" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Profile Photo</label>
          <input type="file" name="profile" accept="image/*" class="w-full border border-gray-300 rounded px-3 py-2" />
          <p class="text-xs text-gray-500 mt-1">Allowed: JPG, JPEG, PNG, WEBP.</p>
        </div>
        <div>
          <label class="block text-gray-700 mb-1">Status</label>
          <select name="status" class="w-full border border-gray-300 rounded px-3 py-2" required>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="flex justify-between mt-6">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Add Driver</button>
        <a href="drivers.php" class="text-gray-600 hover:underline">Cancel</a>
      </div>
    </form>
    
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var passwordInput = document.getElementById('password');
    var togglePassword = document.getElementById('togglePassword');

    if (passwordInput && togglePassword) {
      togglePassword.addEventListener('change', function () {
        passwordInput.type = this.checked ? 'text' : 'password';
      });
    }
  });
</script>
