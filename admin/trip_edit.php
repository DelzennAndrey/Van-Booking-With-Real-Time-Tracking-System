<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();

$trip_id = null;
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: trips.php');
    exit();
}
$trip_id = intval($_GET['id']);

// Handle form submission before sending output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $van_id = intval($_POST['van_id']);
    $origin = trim($_POST['origin']);
    $destination = trim($_POST['destination']);
    $available_seat = intval($_POST['available_seat']);
    $status = trim($_POST['status']);
    // Enforce allowed statuses and normalize to lowercase
    $allowed_statuses = ['scheduled','ongoing','completed','disabled'];
    $status = strtolower($status);
    if (!in_array($status, $allowed_statuses, true)) {
        $error = 'Invalid status selected.';
    }

    if (!$van_id || $origin === '' || $destination === '' || $status === '') {
        $error = 'Please fill in all required fields.';
    } else {
        // Ensure origin and destination exist in route table
        $check = $conn->prepare("SELECT COUNT(*) as cnt FROM route WHERE route IN (?, ?)");
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

        if (!$error && empty($schedules)) {
            $error = 'Please add at least one schedule (weekday and time).';
        }
    }

    // Check for conflicts with existing trips across all vans
    if (!$error && !empty($schedules)) {
        $conflictStmt = $conn->prepare("SELECT v.van_number, t.origin, t.destination, s.weekdays, DATE_FORMAT(s.departure_time, '%h:%i %p') AS time12 FROM schedule s JOIN van_trip t ON s.trip_id = t.trip_id JOIN van v ON t.van_id = v.van_id WHERE s.weekdays = ? AND s.departure_time = ? AND t.trip_id != ? LIMIT 1");
        if ($conflictStmt) {
            foreach ($schedules as $sch) {
                $day = $sch['weekday'];
                $timeNorm = $sch['time'];
                $conflictStmt->bind_param('ssi', $day, $timeNorm, $trip_id);
                $conflictStmt->execute();
                $confRes = $conflictStmt->get_result();
                if ($confRes && $confRes->num_rows > 0) {
                    $confRow = $confRes->fetch_assoc();
                    $error = 'A trip already exists on ' . $confRow['weekdays'] . ' at ' . $confRow['time12'] . ' (Van: ' . $confRow['van_number'] . ', Route: ' . $confRow['origin'] . ' to ' . $confRow['destination'] . ').';
                    break;
                }
            }
            $conflictStmt->close();
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE van_trip SET van_id=?, origin=?, destination=?, available_seat=?, status=? WHERE trip_id=?");
        $stmt->bind_param('issisi', $van_id, $origin, $destination, $available_seat, $status, $trip_id);
        if ($stmt->execute()) {
            $stmt->close();

            // Check if there are any bookings for TODAY'S schedules for this trip
            $today = date('l'); // Get current day name (Monday, Tuesday, etc.)
            $checkBookings = $conn->prepare("
                SELECT COUNT(*) as count, 
                       s.weekdays,
                       b.status as booking_status
                FROM booking b 
                JOIN schedule s ON b.sched_id = s.sched_id 
                WHERE s.trip_id = ? AND s.weekdays = ?
                GROUP BY s.weekdays, b.status
            ");
            $checkBookings->bind_param('is', $trip_id, $today);
            $checkBookings->execute();
            $bookingResult = $checkBookings->get_result();
            
            $hasTodayBookings = false;
            $hasOngoingTodayBookings = false;
            
            while ($row = $bookingResult->fetch_assoc()) {
                $hasTodayBookings = true;
                if (in_array($row['booking_status'], ['confirmed', 'ongoing'])) {
                    $hasOngoingTodayBookings = true;
                    break;
                }
            }
            $checkBookings->close();

            if ($hasOngoingTodayBookings) {
                // There are ongoing bookings for today, block only today's schedule editing
                $error = 'Cannot modify today\'s schedule because there are ongoing bookings. You can still edit trip details and schedules for other days.';
            } else {
                // Safe to update all schedules - either no ongoing bookings today or only completed/cancelled bookings
                $conn->begin_transaction();
                
                try {
                    // Get all existing schedules for this trip
                    $existingSchedules = [];
                    $existingByDayTime = [];
                    $getExisting = $conn->prepare("SELECT sched_id, weekdays, departure_time FROM schedule WHERE trip_id = ?");
                    $getExisting->bind_param('i', $trip_id);
                    $getExisting->execute();
                    $existingResult = $getExisting->get_result();
                    while ($row = $existingResult->fetch_assoc()) {
                        $existingSchedules[$row['weekdays']][] = $row;
                        $timeKey = date('H:i:s', strtotime($row['departure_time']));
                        $existingByDayTime[$row['weekdays']][$timeKey] = $row;
                    }
                    $getExisting->close();
                    
                    // Check which schedules have bookings (any day, any status)
                    $bookedScheduleIds = [];
                    $getBooked = $conn->prepare("SELECT DISTINCT s.sched_id FROM schedule s JOIN booking b ON s.sched_id = b.sched_id WHERE s.trip_id = ?");
                    $getBooked->bind_param('i', $trip_id);
                    $getBooked->execute();
                    $bookedResult = $getBooked->get_result();
                    while ($row = $bookedResult->fetch_assoc()) {
                        $bookedScheduleIds[] = $row['sched_id'];
                    }
                    $getBooked->close();
                    
                    // Process new schedules - preserve booked schedules, update others
                    $schedStmt = $conn->prepare("INSERT INTO schedule (trip_id, weekdays, departure_time) VALUES (?, ?, ?)");
                    $schedulesToKeep = [];
                    
                    foreach ($schedules as $sch) {
                        $day = $sch['weekday'];
                        $timeNorm = $sch['time'];
                        $matched = false;

                        if (isset($existingByDayTime[$day][$timeNorm])) {
                            $schedulesToKeep[] = (int)$existingByDayTime[$day][$timeNorm]['sched_id'];
                            $matched = true;
                        }

                        if (!$matched && isset($existingSchedules[$day])) {
                            foreach ($existingSchedules[$day] as $existing) {
                                $existingTimeNorm = date('H:i:s', strtotime($existing['departure_time']));
                                if ($existingTimeNorm === $timeNorm && in_array($existing['sched_id'], $bookedScheduleIds)) {
                                    // This schedule has bookings, keep it as-is
                                    $schedulesToKeep[] = (int)$existing['sched_id'];
                                    $matched = true;
                                    break;
                                }
                            }
                        }
                        
                        // If not matched to an existing schedule, create new one
                        if (!$matched) {
                            $schedStmt->bind_param('iss', $trip_id, $day, $timeNorm);
                            if (!$schedStmt->execute()) {
                                throw new Exception('Error creating new schedule.');
                            }
                        }
                    }
                    $schedStmt->close();
                    
                    // Delete old schedules that weren't kept and don't have bookings
                    $allScheduleIds = [];
                    foreach ($existingSchedules as $daySchedules) {
                        foreach ($daySchedules as $sched) {
                            $allScheduleIds[] = $sched['sched_id'];
                        }
                    }
                    
                    // Get schedules to delete (those not in keep list AND not booked)
                    $schedulesToDelete = array_diff($allScheduleIds, $schedulesToKeep, $bookedScheduleIds);
                    
                    if (!empty($schedulesToDelete)) {
                        $idsToDelete = array_map('intval', $schedulesToDelete);
                        $sql = "DELETE FROM schedule WHERE sched_id IN (" . implode(',', $idsToDelete) . ")";
                        if (!$conn->query($sql)) {
                            throw new Exception('Error deleting old schedules.');
                        }
                    }
                    
                    $conn->commit();
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Error updating schedules: ' . $e->getMessage();
                }
            }

            if (!$error) {
                header('Location: trips.php');
                exit();
            }
        } else {
            $error = 'Error updating trip: ' . $stmt->error;
            $stmt->close();
        }
    }
}

// Fetch current trip details
$stmt = $conn->prepare("SELECT * FROM van_trip WHERE trip_id = ?");
$stmt->bind_param('i', $trip_id);
$stmt->execute();
$result = $stmt->get_result();
$trip = $result->fetch_assoc();
$stmt->close();

if (!$trip) {
    include 'includes/header.php';
    echo '<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">Trip not found.</div>';
    include 'includes/footer.php';
    exit();
}

$schedules = [];
$schedStmt = $conn->prepare("SELECT sched_id, weekdays, departure_time FROM schedule WHERE trip_id = ? ORDER BY weekdays, departure_time");
if ($schedStmt) {
    $schedStmt->bind_param('i', $trip_id);
    $schedStmt->execute();
    $schedResult = $schedStmt->get_result();
    while ($row = $schedResult->fetch_assoc()) {
        $schedules[] = $row;
    }
    $schedStmt->close();
}

// Fetch vans for dropdown (with capacity)
$vans = $conn->query("SELECT van_id, van_number, capacity FROM van WHERE status = 'active'");
// Fetch routes for origin/destination selects
$routes_result = $conn->query("SELECT route FROM route ORDER BY route");
$routeOptions = [];
if ($routes_result) {
    while ($r = $routes_result->fetch_assoc()) {
        $routeOptions[] = $r['route'];
    }
}

// Check if today has ongoing bookings for this trip
$today = date('l');
$hasOngoingTodayBookings = false;
$checkTodayBookings = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM booking b 
    JOIN schedule s ON b.sched_id = s.sched_id 
    WHERE s.trip_id = ? AND s.weekdays = ? AND b.status IN ('confirmed', 'ongoing')
");
$checkTodayBookings->bind_param('is', $trip_id, $today);
$checkTodayBookings->execute();
$todayResult = $checkTodayBookings->get_result();
$todayRow = $todayResult->fetch_assoc();
$hasOngoingTodayBookings = $todayRow['count'] > 0;
$checkTodayBookings->close();

// Safe to start output
include 'includes/header.php';
?>
<div class="max-w-6xl mx-auto py-10">
    <div class="bg-white rounded-lg shadow-md p-8">
        <h2 class="text-3xl font-bold text-blue-700 mb-2">Edit Trip</h2>
        <p class="text-sm text-gray-600 mb-8">Update trip details and schedules. Today’s schedules may be locked if there are ongoing bookings.</p>

        <?php if ($hasOngoingTodayBookings): ?>
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-amber-600"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800">Today's Schedule Restricted</h3>
                    <div class="mt-2 text-sm text-amber-700">
                        <p>This trip has ongoing bookings for today (<strong><?php echo $today; ?></strong>). You can edit:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>✓ Van assignment</li>
                            <li>✓ Origin and destination</li>
                            <li>✓ Available seats</li>
                            <li>✓ Trip status</li>
                            <li>✓ Schedules for other days</li>
                        </ul>
                        <p class="mt-2"><strong>Cannot edit:</strong> Today's schedule times (locked to maintain booking integrity)</p>
                        <p class="mt-1 text-xs text-amber-600">Note: Once today's bookings are completed or cancelled, today's schedule editing will be available again.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
                                    <option value="<?php echo $v['van_id']; ?>" data-capacity="<?php echo (int)$v['capacity']; ?>" <?php echo ($trip['van_id'] == $v['van_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($v['van_number']); ?>
                                    </option>
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
                                <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($trip['origin'] === $opt) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($opt); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Destination</label>
                        <select name="destination" id="destination" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Select Destination</option>
                            <?php foreach ($routeOptions as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo ($trip['destination'] === $opt) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($opt); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Schedule Section -->
            <section>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Schedule (Weekday & Time)</h3>
                <p class="text-sm text-gray-600 mb-4">Click a day to add one or more departure times for that day. You can edit or remove any time before saving. Today's schedule may be locked if there are ongoing bookings.</p>
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
                    foreach ($days as $code => $label):
                        $isToday = ($label === date('l'));
                        $hasTodayBookings = false;

                        // Check if today has ongoing bookings
                        if ($isToday) {
                            $checkTodayBookings = $conn->prepare("
                                SELECT COUNT(*) as count 
                                FROM booking b 
                                JOIN schedule s ON b.sched_id = b.sched_id 
                                WHERE s.trip_id = ? AND s.weekdays = ? AND b.status IN ('confirmed', 'ongoing')
                            ");
                            $checkTodayBookings->bind_param('is', $trip_id, $label);
                            $checkTodayBookings->execute();
                            $todayResult = $checkTodayBookings->get_result();
                            $todayRow = $todayResult->fetch_assoc();
                            $hasTodayBookings = $todayRow['count'] > 0;
                            $checkTodayBookings->close();
                        }
                    ?>
                        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50 <?php echo $isToday && $hasTodayBookings ? 'border-red-300 bg-red-50' : ''; ?>">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <span class="font-semibold text-gray-800"><?php echo $label; ?></span>
                                    <?php if ($isToday): ?>
                                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Today</span>
                                    <?php endif; ?>
                                    <?php if ($isToday && $hasTodayBookings): ?>
                                        <span class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded">
                                            <i class="fas fa-lock mr-1"></i>Locked
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <button type="button"
                                        class="add-time-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm font-medium
                                               <?php echo $isToday && $hasTodayBookings ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                        data-day="<?php echo $code; ?>"
                                        <?php echo $isToday && $hasTodayBookings ? 'disabled' : ''; ?>>
                                    + Add
                                </button>
                            </div>
                            <?php if ($isToday && $hasTodayBookings): ?>
                                <div class="text-xs text-red-600 mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Today's schedule locked due to ongoing bookings. You can edit trip details but not today's schedule.
                                </div>
                            <?php endif; ?>
                            <div class="space-y-2 times-list" data-day="<?php echo $code; ?>">
                                <?php foreach ($schedules as $sch): if ($sch['weekdays'] === $code): ?>
                                    <div class="flex flex-col md:flex-row md:items-center gap-2 time-row">
                                        <input type="hidden" name="weekdays[]" value="<?php echo $code; ?>">
                                        <input type="time"
                                               name="departure_times[]"
                                               value="<?php echo htmlspecialchars(substr($sch['departure_time'], 0, 5)); ?>"
                                               class="w-full border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500
                                                      <?php echo $isToday && $hasTodayBookings ? 'bg-gray-100 opacity-60 cursor-not-allowed' : ''; ?>"
                                               <?php echo $isToday && $hasTodayBookings ? 'readonly' : ''; ?>>
                                        <button type="button"
                                                class="remove-time bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm
                                                       <?php echo $isToday && $hasTodayBookings ? 'opacity-50 cursor-not-allowed' : ''; ?>"
                                                <?php echo $isToday && $hasTodayBookings ? 'disabled' : ''; ?>>
                                            Remove
                                        </button>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
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
                        <input type="number" name="available_seat" id="available_seat" min="0" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?php echo (int)$trip['available_seat']; ?>" required>
                    </div>
                    <div>
                        <label class="block font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <?php $curr = strtolower($trip['status']); ?>
                            <option value="scheduled" <?php echo ($curr === 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="ongoing" <?php echo ($curr === 'ongoing') ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo ($curr === 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="disabled" <?php echo ($curr === 'disabled') ? 'selected' : ''; ?>>Disabled</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t">
                <a href="trips.php" class="text-gray-600 hover:text-gray-800 underline">← Back to Trips</a>
                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow transition">Update Trip</button>
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
  function maybeSetCapacity() {
    var selected = vanSelect.options[vanSelect.selectedIndex];
    var cap = selected ? parseInt(selected.getAttribute('data-capacity') || '0', 10) : 0;
    if (!isNaN(cap) && cap > 0) {
      availableSeatInput.value = cap;
    }
  }
  if (vanSelect) {
    vanSelect.addEventListener('change', maybeSetCapacity);
  }
  var scheduleDays = document.getElementById('schedule-days');

  function addTimeRow(dayCode) {
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
  }

  if (scheduleDays) {
    scheduleDays.addEventListener('click', function(e) {
      var target = e.target;
      if (target.classList.contains('add-time-btn')) {
        // Check if button is disabled (today with bookings)
        if (target.disabled || target.classList.contains('cursor-not-allowed')) {
          e.preventDefault();
          alert('Cannot modify today\'s schedule due to ongoing bookings. You can edit trip details and schedules for other days.');
          return;
        }
        var day = target.getAttribute('data-day');
        addTimeRow(day);
      } else if (target.classList.contains('remove-time')) {
        // Check if button is disabled
        if (target.disabled || target.classList.contains('cursor-not-allowed')) {
          e.preventDefault();
          alert('Cannot modify today\'s schedule due to ongoing bookings.');
          return;
        }
        var row = target.closest('.time-row');
        if (row) {
          row.remove();
        }
      }
    });
  }
  
  // Form submission validation
  var tripForm = document.querySelector('form');
  if (tripForm) {
    tripForm.addEventListener('submit', function(e) {
      var todayLabel = '<?php echo date('l'); ?>';
      var todaySection = Array.from(document.querySelectorAll('.border.rounded.p-3')).find(function(section) {
        return section.textContent.includes(todayLabel);
      });
      
      if (todaySection && todaySection.querySelector('.fa-lock')) {
        // Today has ongoing bookings, check if user is trying to modify today's schedule
        var todayInputs = todaySection.querySelectorAll('input[name="departure_times[]"]');
        var hasTodayChanges = false;
        
        todayInputs.forEach(function(input) {
          // If somehow a readonly input was changed, block it
          if (input.value !== input.defaultValue) {
            hasTodayChanges = true;
          }
        });
        
        if (hasTodayChanges) {
          var confirmMsg = 'Today\'s schedule has ongoing bookings and cannot be modified. However, you can still update trip details (van, origin, destination, capacity) and schedules for other days. Do you want to continue saving only the trip details and other day schedules?';
          if (!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
          }
        }
      }
    });
  }
});
</script>
