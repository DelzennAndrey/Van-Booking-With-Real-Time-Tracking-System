<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $price = intval($_POST['price']);

    // Do not allow duplicate discount type
    $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM discount WHERE LOWER(type) = LOWER(?)");
    $chk->bind_param('s', $type);
    $chk->execute();
    $res = $chk->get_result();
    $row = $res ? $res->fetch_assoc() : ['cnt' => 0];
    $chk->close();

    if (!empty($row['cnt']) && (int)$row['cnt'] > 0) {
        $error = 'A discount for this type already exists.';
    } else {
        $stmt = $conn->prepare("INSERT INTO discount (type, price) VALUES (?, ?)");
        $stmt->bind_param('si', $type, $price);
        if ($stmt->execute()) {
            header('Location: discounts.php');
            exit();
        } else {
            $error = 'Error adding discount.';
        }
        $stmt->close();
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="max-w-lg mx-auto py-10">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-2xl font-bold text-blue-700 mb-6">Add New Discount</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"> <?php echo $error; ?> </div>
        <?php endif; ?>
        <form method="POST" class="space-y-6">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Discount Type</label>
                <select name="type" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select Type</option>
                    <option value="PWD">PWD</option>
                    <option value="Student">Student</option>
                    <option value="Senior Citizen">Senior Citizen</option>
                    <option value="Regular">Regular</option>
                </select>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Discount (%)</label>
                <input type="number" name="price" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" step="1" min="0" required>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition">Add Discount</button>
                <a href="discounts.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded shadow transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
