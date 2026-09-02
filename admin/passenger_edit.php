<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: passengers.php');
    exit();
}
$passenger_id = intval($_GET['id']);

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $gender = trim($_POST['gender']);
    $age = intval($_POST['age']);
    $type = trim($_POST['type']);
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    $no_show = intval($_POST['no_show']);

    $stmt = $conn->prepare("UPDATE passenger SET first_name=?, last_name=?, gender=?, age=?, type=?, address=?, contact_number=?, no_show=? WHERE passenger_id=?");
    $stmt->bind_param('sssisssii', $first_name, $last_name, $gender, $age, $type, $address, $contact_number, $no_show, $passenger_id);
    if ($stmt->execute()) {
        $success = 'Passenger updated successfully!';
    } else {
        $error = 'Error updating passenger.';
    }
    $stmt->close();
}

// Fetch passenger data
$stmt = $conn->prepare("SELECT * FROM passenger WHERE passenger_id = ?");
$stmt->bind_param('i', $passenger_id);
$stmt->execute();
$result = $stmt->get_result();
$passenger = $result->fetch_assoc();
$stmt->close();

if (!$passenger) {
    echo '<div class="alert alert-danger">Passenger not found.</div>';
    include 'includes/footer.php';
    exit();
}
?>
<h2>Edit Passenger</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($passenger['first_name']); ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($passenger['last_name']); ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select" required>
            <option value="Male" <?php if ($passenger['gender'] == 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if ($passenger['gender'] == 'Female') echo 'selected'; ?>>Female</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Age</label>
        <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($passenger['age']); ?>" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
            <option value="Regular" <?php if ($passenger['type'] == 'Regular') echo 'selected'; ?>>Regular</option>
            <option value="Student" <?php if ($passenger['type'] == 'Student') echo 'selected'; ?>>Student</option>
            <option value="Senior Citizen" <?php if ($passenger['type'] == 'Senior Citizen') echo 'selected'; ?>>Senior Citizen</option>
            <option value="PWD" <?php if ($passenger['type'] == 'PWD') echo 'selected'; ?>>PWD</option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($passenger['address']); ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Contact Number</label>
        <input type="text" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($passenger['contact_number']); ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">No-Show Count</label>
        <input type="number" name="no_show" class="form-control" value="<?php echo htmlspecialchars($passenger['no_show']); ?>" min="0" required>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Update Passenger</button>
        <a href="passenger_view.php?id=<?php echo $passenger_id; ?>" class="btn btn-secondary">Cancel</a>
        <a href="passengers.php" class="btn btn-info">Back to Passengers</a>
    </div>
</form>
<?php include 'includes/footer.php'; ?>
