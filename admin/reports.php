<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Default date range: today
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

?>
<!-- Main Content Container -->
<div class="flex-1 p-6 page-content">
  <div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Reports</h1>
    
    <form method="GET" class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
          <input type="date" name="start_date" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($start_date); ?>" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
          <input type="date" name="end_date" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($end_date); ?>" required>
        </div>
        <div class="flex items-end">
          <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Filter</button>
        </div>
        <div class="flex items-end space-x-2">
          <a href="#" class="flex-1 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-center">Export CSV</a>
          <a href="#" class="flex-1 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition text-center">Export PDF</a>
        </div>
      </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Booking Summary</h3>
        <p class="text-gray-500">Booking summary table or chart goes here.</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Van Trip Summary</h3>
        <p class="text-gray-500">Van trip summary table or chart goes here.</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Van Summary</h3>
        <p class="text-gray-500">Van summary table or chart goes here.</p>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Passenger Summary</h3>
        <p class="text-gray-500">Passenger summary table or chart goes here.</p>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
