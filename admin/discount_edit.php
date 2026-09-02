<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: discounts.php');
    exit();
}
$disc_id = intval($_GET['id']);

$error = '';
$success = '';

// Handle form submission BEFORE sending any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $price = intval($_POST['price']);

    $stmt = $conn->prepare("UPDATE discount SET type=?, price=? WHERE disc_id=?");
    $stmt->bind_param('sii', $type, $price, $disc_id);
    if ($stmt->execute()) {
        header('Location: discounts.php');
        exit();
    } else {
        $error = 'Error updating discount.';
    }
    $stmt->close();
}

// Fetch discount data
$stmt = $conn->prepare("SELECT * FROM discount WHERE disc_id = ?");
$stmt->bind_param('i', $disc_id);
$stmt->execute();
$result = $stmt->get_result();
$discount = $result->fetch_assoc();
$stmt->close();

// Now it's safe to include the header (start output)
include 'includes/header.php';

if (!$discount) {
    echo '<div class="max-w-xl mx-auto py-8 px-4"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Discount not found.</div></div>';
    include 'includes/footer.php';
    exit();
}
?>
<div class="max-w-xl mx-auto py-16 px-6">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-3xl font-bold text-blue-700 mb-8 text-center">Edit Discount</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-6">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Discount Type</label>
                <select name="type" class="w-full border-gray-300 rounded px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="PWD" <?php if ($discount['type'] == 'PWD') echo 'selected'; ?>>PWD</option>
                    <option value="Student" <?php if ($discount['type'] == 'Student') echo 'selected'; ?>>Student</option>
                    <option value="Senior Citizen" <?php if ($discount['type'] == 'Senior Citizen') echo 'selected'; ?>>Senior Citizen</option>
                    <option value="Regular" <?php if ($discount['type'] == 'Regular') echo 'selected'; ?>>Regular</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Discount (%)</label>
                <input type="number" name="price" class="w-full border-gray-300 rounded px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500" step="1" min="0" value="<?php echo (int)$discount['price']; ?>" required>
            </div>
            <div class="flex items-center justify-end space-x-3 pt-2">
                <a href="discounts.php" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-3 rounded shadow transition">Update Discount</button>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
