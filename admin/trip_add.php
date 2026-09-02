<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

$error = '';

// Fetch vans for dropdown (include capacity)
$vans = $conn->query("\n    SELECT v.van_id, v.van_number, v.capacity\n    FROM van v\n    WHERE v.status = 'active'\n      AND NOT EXISTS (\n          SELECT 1 FROM van_trip t\n          WHERE t.van_id = v.van_id\n      )\n");
// Fetch routes for origin/destination selects
$routes_result = $conn->query("SELECT route FROM route ORDER BY route");
$routeOptions = [];
if ($routes_result) {
    while ($r = $routes_result->fetch_assoc()) {
        $routeOptions[] = $r['route'];
    }
}
$copyTrips = [];
$copyTripsResult = $conn->query("SELECT t.trip_id, v.van_number, t.origin, t.destination FROM van_trip t LEFT JOIN van v ON t.van_id = v.van_id ORDER BY t.trip_id DESC");
if ($copyTripsResult) {
    while ($ct = $copyTripsResult->fetch_assoc()) {
        $copyTrips[] = $ct;
    }
}
$copySchedulesByTrip = [];
if (!empty($copyTrips)) {
    $tripIds = [];
    foreach ($copyTrips as $ct) {
        $tripIds[] = (int)$ct['trip_id'];
    }
    $tripIds = array_values(array_unique($tripIds));
    if (!empty($tripIds)) {
        $in = implode(',', array_map('intval', $tripIds));
        $schedSql = "SELECT trip_id, weekdays, DATE_FORMAT(departure_time, '%H:%i') AS time24 FROM schedule WHERE trip_id IN ($in) ORDER BY weekdays, departure_time";
        $schedRes = $conn->query($schedSql);
        if ($schedRes) {
            while ($row = $schedRes->fetch_assoc()) {
                $tid = (int)$row['trip_id'];
                if (!isset($copySchedulesByTrip[$tid])) {
                    $copySchedulesByTrip[$tid] = [];
                }
                $copySchedulesByTrip[$tid][] = [
                    'weekday' => $row['weekdays'],
                    'time' => $row['time24'],
                ];
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $van_id = intval($_POST['van_id']);
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $available_seat = isset($_POST['available_seat']) ? (int)$_POST['available_seat'] : 16;
    $status = trim($_POST['status']);
    $copy_trip_id = isset($_POST['copy_trip_id']) ? (int)$_POST['copy_trip_id'] : 0;
    $allowed_statuses = ['scheduled','ongoing','completed','disabled'];
    $status = strtolower($status);
    if (!in_array($status, $allowed_statuses, true)) {
        $error = 'Invalid status selected.';
    }

    // Validate origin and destination are existing routes
    if (!$error) {
        $check = $conn->prepare("SELECT COUNT(*) as cnt FROM route WHERE route IN (?, ?) ");
        $check->bind_param('ss', $origin, $destination);
        $check->execute();
        $res = $check->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $check->close();
        if (!$row || (int)$row['cnt'] < 2) {
            $error = 'Origin and Destination must be valid routes.';
        }
    }

    $weekdays = isset($_POST['weekdays']) && is_array($_POST['weekdays']) ? $_POST['weekdays'] : [];
    $departure_times = isset($_POST['departure_times']) && is_array($_POST['departure_times']) ? $_POST['departure_times'] : [];
    $schedules = [];

    if (!$error) {
        foreach ($weekdays as $idx => $day) {
            $day = trim($day);
            $timeRaw = isset($departure_times[$idx]) ? trim($departure_times[$idx]) : '';

            if ($day === '' && $timeRaw === '') {
                continue;
            }

            if ($day === '' || $timeRaw === '') {
                $error = 'Each schedule row must have both weekday and time.';
                break;
            }

            $timeNorm = date('H:i:s', strtotime($timeRaw));
            $schedules[] = ['weekday' => $day, 'time' => $timeNorm];
        }

        if (!$error && empty($schedules)) {
            $error = 'Please add at least one schedule (weekday and time).';
        }
    }

    if (!$error && !empty($schedules)) {
        $unique = [];
        $deduped = [];
        foreach ($schedules as $sch) {
            $key = $sch['weekday'] . '|' . $sch['time'];
            if (!isset($unique[$key])) {
                $unique[$key] = true;
                $deduped[] = $sch;
            }
        }
        $schedules = $deduped;
    }

    
    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO van_trip (van_id, origin, destination, available_seat, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issis', $van_id, $origin, $destination, $available_seat, $status);

        if ($stmt->execute()) {
            $newTripId = $stmt->insert_id;
            $stmt->close();

            $schedStmt = $conn->prepare("INSERT INTO schedule (trip_id, weekdays, departure_time) VALUES (?, ?, ?)");
            if ($schedStmt) {
                foreach ($schedules as $sch) {
                    $day = $sch['weekday'];
                    $time = $sch['time'];
                    $schedStmt->bind_param('iss', $newTripId, $day, $time);
                    if (!$schedStmt->execute()) {
                        $error = 'Error adding schedule.';
                        break;
                    }
                }
                $schedStmt->close();
            } else {
                $error = 'Error preparing schedule insertion.';
            }

            if (!$error) {
                header('Location: trips.php');
                exit();
            }
        } else {
            $error = 'Error adding trip.';
            $stmt->close();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="max-w-6xl mx-auto py-10">
    <div class="bg-white rounded-lg shadow-md p-8">
        <h2 class="text-3xl font-bold text-blue-700 mb-2">Add New Trip</h2>
        <p class="text-sm text-gray-600 mb-8">Create a new trip and set its schedule. You can also copy a schedule from an existing trip.</p>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-8">
            <!-- Trip Details Section -->
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Trip Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Van</label>
                        <select name="van_id" id="van_id" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select Van</option>
                            <?php if ($vans && $vans->num_rows > 0): ?>
                                <?php while ($v = $vans->fetch_assoc()): ?>
                                    <option value="<?php echo $v['van_id']; ?>" data-capacity="<?php echo (int)$v['capacity']; ?>"><?php echo htmlspecialchars($v['van_number']); ?></option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="">No active vans</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Origin</label>
                        <select name="origin" id="origin" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select Origin</option>
                            <?php foreach ($routeOptions as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Destination</label>
                        <select name="destination" id="destination" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select Destination</option>
                            <?php foreach ($routeOptions as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Copy Schedule Section -->
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Copy Schedule (Optional)</h3>
                <div>
                    <label class="block font-medium text-gray-700 mb-2">Copy Schedule From Trip</label>
                    <select name="copy_trip_id" id="copy_trip_id" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Do not copy schedule</option>
                        <?php if (!empty($copyTrips)): ?>
                            <?php foreach ($copyTrips as $ct): ?>
                                <option value="<?php echo (int)$ct['trip_id']; ?>">
                                    Trip #<?php echo (int)$ct['trip_id']; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </section>

            <!-- Schedule Section -->
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Schedule (Weekday & Time)</h3>
                <p class="text-sm text-gray-600 mb-4">Click a day to add one or more departure times for that day. You can edit or remove any time before saving.</p>
                <div id="schedule-days" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php
                    $days = [
                        'Mon' => 'Monday',
                        'Tue' => 'Tuesday',
                        'Wed' => 'Wednesday',
                        'Thu' => 'Thursday',
                        'Fri' => 'Friday',
                        'Sat' => 'Saturday',
                        'Sun' => 'Sunday',
                    ];
                    foreach ($days as $code => $label): ?>
                        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-semibold text-gray-800"><?php echo $label; ?></span>
                                <button type="button" class="add-time-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium" data-day="<?php echo $code; ?>">+ Add</button>
                            </div>
                            <div class="space-y-2 times-list" data-day="<?php echo $code; ?>"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Seats & Status Section -->
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Seats & Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Available Seats</label>
                        <input type="number" name="available_seat" id="available_seat" min="0" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="16" required>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="scheduled">Scheduled</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t">
                <a href="trips.php" class="text-gray-600 hover:text-gray-800 underline">← Back to Trips</a>
                <div class="flex gap-3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow transition">Add Trip</button>
                    <a href="trips.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-6 rounded-lg shadow transition">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var vanSelect = document.getElementById('van_id');
  var availableSeatInput = document.getElementById('available_seat');

  function setDefaultsFromSelectedVan() {
    var selected = vanSelect ? vanSelect.options[vanSelect.selectedIndex] : null;
    var cap = selected ? parseInt(selected.getAttribute('data-capacity') || '0', 10) : 0;
    if (!isNaN(cap) && cap > 0) {
      availableSeatInput.value = cap;
    }
  }

  if (vanSelect && availableSeatInput) {
    vanSelect.addEventListener('change', setDefaultsFromSelectedVan);
    // Initialize on load if a van is preselected
    setDefaultsFromSelectedVan();
  }

  var scheduleDays = document.getElementById('schedule-days');

  var copyTripSelect = document.getElementById('copy_trip_id');
  var copySchedulesByTrip = <?php echo json_encode($copySchedulesByTrip); ?> || {};

  function clearAllScheduleRows() {
    if (!scheduleDays) return;
    var rows = scheduleDays.querySelectorAll('.time-row');
    rows.forEach(function(row) { row.remove(); });
  }

  function addTimeRow(dayCode, timeValue) {
    if (!scheduleDays || !dayCode) return;
    var list = scheduleDays.querySelector('.times-list[data-day="' + dayCode + '"]');
    if (!list) return;

    var row = document.createElement('div');
    row.className = 'flex flex-col md:flex-row md:items-center gap-2 time-row';
    row.innerHTML =
      '<input type="hidden" name="weekdays[]" value="' + dayCode + '">' +
      '<input type="time" name="departure_times[]" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>' +
      '<button type="button" class="remove-time bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm">Remove</button>';

    list.appendChild(row);

    if (typeof timeValue === 'string' && timeValue !== '') {
      var input = row.querySelector('input[name="departure_times[]"]');
      if (input) {
        input.value = timeValue;
      }
    }
  }

  if (scheduleDays) {
    scheduleDays.addEventListener('click', function(e) {
      var target = e.target;
      if (target.classList.contains('add-time-btn')) {
        var day = target.getAttribute('data-day');
        addTimeRow(day);
      } else if (target.classList.contains('remove-time')) {
        var row = target.closest('.time-row');
        if (row) {
          row.remove();
        }
      }
    });
  }

  if (copyTripSelect) {
    copyTripSelect.addEventListener('change', function() {
      var tripId = copyTripSelect.value;
      if (!scheduleDays) return;

      // Clear current rows then load copied schedule (if any)
      clearAllScheduleRows();

      var id = parseInt(tripId, 10);
      if (!id || !copySchedulesByTrip[id]) {
        return;
      }

      var scheds = copySchedulesByTrip[id];
      scheds.forEach(function(sch) {
        if (!sch || !sch.weekday || !sch.time) return;
        addTimeRow(sch.weekday, sch.time);
      });
    });
  }
});
</script>
