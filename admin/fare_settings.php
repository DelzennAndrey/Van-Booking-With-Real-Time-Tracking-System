<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

$error = '';
$success = '';

// Fetch fare settings (assuming only one row in fare table)
$sql = "SELECT * FROM fare LIMIT 1";
$result = $conn->query($sql);
$fare = $result ? $result->fetch_assoc() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_fare = floatval($_POST['base_fare']);
    $base_km = intval($_POST['base_km']);
    $per_km_rate = floatval($_POST['per_km_rate']);

    if ($fare) {
        // Update existing
        $stmt = $conn->prepare("UPDATE fare SET base_fare=?, base_km=?, per_km_rate=? WHERE fare_id=?");
        $stmt->bind_param('didi', $base_fare, $base_km, $per_km_rate, $fare['fare_id']);
        if ($stmt->execute()) {
            $success = 'Fare settings updated!';
        } else {
            $error = 'Error updating fare settings.';
        }
        $stmt->close();
    } else {
        // Insert new
        $stmt = $conn->prepare("INSERT INTO fare (base_fare, base_km, per_km_rate) VALUES (?, ?, ?)");
        $stmt->bind_param('did', $base_fare, $base_km, $per_km_rate);
        if ($stmt->execute()) {
            $success = 'Fare settings saved!';
        } else {
            $error = 'Error saving fare settings.';
        }
        $stmt->close();
    }
    // Refresh fare data
    $result = $conn->query($sql);
    $fare = $result ? $result->fetch_assoc() : null;
}
?>
<div class="max-w-xl mx-auto py-16 px-6">
    <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-3xl font-bold text-blue-700 mb-8 text-center">Fare Settings</h2>
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" id="fareForm" class="space-y-7">
            <div>
                <label class="block font-medium text-gray-700 mb-1">Base Fare (₱)</label>
                <input type="number" name="base_fare"
                    class="w-full border-gray-300 rounded px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                    step="0.01" min="0" value="<?php echo htmlspecialchars($fare['base_fare'] ?? ''); ?>" required>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Base KM</label>
                <input type="number" name="base_km"
                    class="w-full border-gray-300 rounded px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                    min="0" value="<?php echo htmlspecialchars($fare['base_km'] ?? ''); ?>" required>
            </div>
            <div>
                <label class="block font-medium text-gray-700 mb-1">Per KM Rate (₱)</label>
                <input type="number" name="per_km_rate"
                    class="w-full border-gray-300 rounded px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                    step="0.01" min="0" value="<?php echo htmlspecialchars($fare['per_km_rate'] ?? ''); ?>" required>
            </div>
            <div>
                <button type="button" id="openConfirm"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-5 rounded shadow transition w-full">Save
                    Settings</button>
            </div>
        </form>
    </div>
</div>
<!-- Confirm Save Modal -->
<div id="fareConfirmModal" class="fixed inset-0 bg-black/50 backdrop-blur-md items-center justify-center hidden opacity-0 transition-opacity duration-200 z-50">
    <div id="fareConfirmPanel" class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 transform transition-all duration-200 opacity-0 scale-95">
        <h3 class="text-lg font-semibold mb-2">Confirm Changes</h3>
        <p class="text-sm text-gray-600 mb-6">Are you sure you want to save these fare settings?</p>
        <div class="flex justify-end space-x-3">
            <button type="button" id="fareCancel" class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="button" id="fareConfirm" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Confirm</button>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('fareConfirmModal');
        var panel = document.getElementById('fareConfirmPanel');
        var openBtn = document.getElementById('openConfirm');
        var cancelBtn = document.getElementById('fareCancel');
        var confirmBtn = document.getElementById('fareConfirm');
        var form = document.getElementById('fareForm');

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            requestAnimationFrame(function(){
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                panel.classList.remove('opacity-0','scale-95');
                panel.classList.add('opacity-100','scale-100');
            });
        }
        function closeModal() {
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            panel.classList.remove('opacity-100','scale-100');
            panel.classList.add('opacity-0','scale-95');
            setTimeout(function(){
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            },200);
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
        if (confirmBtn) confirmBtn.addEventListener('click', function(){ form.submit(); });
    });
    </script>
</div>
<?php include 'includes/footer.php'; ?>