<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: passengers.php');
    exit();
}
$passenger_id = intval($_GET['id']);

// Fetch passenger details
$stmt = $conn->prepare("SELECT * FROM passenger WHERE passenger_id = ?");
$stmt->bind_param('i', $passenger_id);
$stmt->execute();
$result = $stmt->get_result();
$passenger = $result->fetch_assoc();
$stmt->close();

if (!$passenger) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative my-4">Passenger not found.</div>';
    include 'includes/footer.php';
    exit();
}

// Fetch passenger's booking history (schedule aggregated per trip)
$booking_sql = "SELECT b.*, vt.origin, vt.destination, v.van_number,
                sc.weekdays_list, sc.times_list
                FROM booking b
                LEFT JOIN van_trip vt ON b.trip_id = vt.trip_id
                LEFT JOIN van v ON vt.van_id = v.van_id
                LEFT JOIN (
                    SELECT trip_id,
                           GROUP_CONCAT(DISTINCT weekdays ORDER BY FIELD(weekdays,'Mon','Tue','Wed','Thu','Fri','Sat','Sun') SEPARATOR ', ') AS weekdays_list,
                           GROUP_CONCAT(DISTINCT DATE_FORMAT(departure_time, '%h:%i %p') ORDER BY departure_time SEPARATOR ', ') AS times_list
                    FROM schedule
                    GROUP BY trip_id
                ) sc ON sc.trip_id = vt.trip_id
                WHERE b.passenger_id = ?
                ORDER BY b.booking_id DESC";
$booking_stmt = $conn->prepare($booking_sql);
$booking_stmt->bind_param('i', $passenger_id);
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
$booking_stmt->close();
?>
<div class="max-w-5xl mx-auto py-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Passenger Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h5 class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Personal Information</h5>
            <div class="space-y-2">
                <p><span class="font-medium text-gray-700">First Name:</span> <?php echo htmlspecialchars($passenger['first_name']); ?></p>
                <p><span class="font-medium text-gray-700">Last Name:</span> <?php echo htmlspecialchars($passenger['last_name']); ?></p>
                <p><span class="font-medium text-gray-700">Gender:</span> <?php echo htmlspecialchars($passenger['gender']); ?></p>
                <p><span class="font-medium text-gray-700">Age:</span> <?php echo htmlspecialchars($passenger['age']); ?></p>
                <p><span class="font-medium text-gray-700">Type:</span> 
                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php 
                        echo $passenger['type'] == 'PWD' ? 'bg-purple-200 text-purple-800' : 
                            ($passenger['type'] == 'Student' ? 'bg-blue-200 text-blue-800' : 
                            ($passenger['type'] == 'Senior Citizen' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-800')); 
                    ?>">
                        <?php echo htmlspecialchars($passenger['type']); ?>
                    </span>
                </p>
                <p><span class="font-medium text-gray-700">Address:</span> <?php echo htmlspecialchars($passenger['address']); ?></p>
                <p><span class="font-medium text-gray-700">ID Number:</span> <?php echo htmlspecialchars($passenger['id_number'] ?? ''); ?></p>
                <?php 
                    $idPic = isset($passenger['idPic_url']) ? trim($passenger['idPic_url']) : '';
                    $isVerified = isset($passenger['is_verified']) ? (int)$passenger['is_verified'] : 0;
                ?>
                <p class="mt-2"><span class="font-medium text-gray-700">Verification:</span>
                    <span class="inline-block ml-2 px-2 py-1 rounded text-xs font-semibold <?php echo $isVerified ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800'; ?>">
                        <?php echo $isVerified ? 'Verified' : 'Unverified'; ?>
                    </span>
                </p>
                <?php if ($idPic !== ''): ?>
                    <div class="mt-3">
                        <span class="block font-medium text-gray-700 mb-1">ID Picture:</span>
                        <!-- Thumbnail that opens the lightbox -->
                        <img src="<?php echo htmlspecialchars($idPic); ?>" alt="ID Picture" id="idPicThumb" class="max-h-48 rounded border cursor-pointer" onerror="this.style.display='none'" role="button" tabindex="0" />
                    </div>
                <?php else: ?>
                    <div class="mt-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded p-3">
                        No ID picture attached. Please request the passenger to upload a valid ID before verification.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h5 class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Account Information</h5>
            <div class="space-y-2">
                <p><span class="font-medium text-gray-700">No-Show Count:</span> 
                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php echo $passenger['no_show'] > 0 ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800'; ?>">
                        <?php echo htmlspecialchars($passenger['no_show']); ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h5 class="text-lg font-semibold text-blue-700 mb-4 border-b pb-2">Booking History</h5>
        <?php if ($booking_result && $booking_result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="py-2 px-3 font-semibold">Booking ID</th>
                            <th class="py-2 px-3 font-semibold">Route</th>
                            <th class="py-2 px-3 font-semibold">Schedule</th>
                            <th class="py-2 px-3 font-semibold">Status</th>
                            <th class="py-2 px-3 font-semibold">Payment</th>
                            <th class="py-2 px-3 font-semibold">Fare</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $booking_result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-3"><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td class="py-2 px-3"><?php echo htmlspecialchars($booking['origin'] . ' → ' . $booking['destination']); ?></td>
                                <td class="py-2 px-3">
                                    <?php
                                        $wl = isset($booking['weekdays_list']) ? $booking['weekdays_list'] : '';
                                        $tl = isset($booking['times_list']) ? $booking['times_list'] : '';
                                        if ($wl || $tl) {
                                            echo htmlspecialchars($wl . ($tl ? ' — ' . $tl : ''));
                                        } else {
                                            echo '—';
                                        }
                                    ?>
                                </td>
                                <td class="py-2 px-3">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php 
                                        echo $booking['status'] == 'Confirmed' ? 'bg-green-200 text-green-800' : 
                                            ($booking['status'] == 'Cancelled' ? 'bg-red-200 text-red-800' : 'bg-blue-200 text-blue-800'); 
                                    ?>">
                                        <?php echo htmlspecialchars($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="py-2 px-3">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php echo $booking['payment_status'] == 'Paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'; ?>">
                                        <?php echo htmlspecialchars($booking['payment_status']); ?>
                                    </span>
                                </td>
                                <td class="py-2 px-3">₱<?php echo number_format($booking['total_fare'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500 italic">No booking history found.</p>
        <?php endif; ?>
    </div>
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="passengers.php" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded shadow text-center">Back to Passengers</a>
        <?php if (!$isVerified): ?>
            <form id="verifyForm" method="post" action="passenger_verify.php">
                <input type="hidden" name="id" value="<?php echo $passenger_id; ?>">
                <button id="verifyBtn" type="button" class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow text-center">Verify</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

<!-- Lightbox modal for viewing ID picture larger -->
<div id="idPicModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden" aria-hidden="true" aria-labelledby="idPicModalLabel" role="dialog">
    <div class="relative max-w-4xl w-full px-4">
        <button id="idPicModalClose" class="absolute top-2 right-2 text-white text-2xl leading-none focus:outline-none" aria-label="Close image">&times;</button>
        <div class="bg-white rounded shadow-lg overflow-hidden">
            <div class="p-3 text-right bg-gray-50">
                <span id="idPicModalLabel" class="sr-only">ID picture preview</span>
            </div>
            <div class="p-4 flex items-center justify-center bg-gray-900">
                <img id="idPicModalImg" src="" alt="ID Picture Large" class="max-h-[80vh] max-w-full rounded" />
            </div>
        </div>
    </div>
</div>

<script>
// Lightbox for ID picture
(function(){
    var thumb = document.getElementById('idPicThumb');
    var modal = document.getElementById('idPicModal');
    var modalImg = document.getElementById('idPicModalImg');
    var closeBtn = document.getElementById('idPicModalClose');

    if (!thumb || !modal || !modalImg || !closeBtn) return;

    function openModal() {
        modalImg.src = thumb.src;
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        // focus close button for keyboard users
        closeBtn.focus();
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        modalImg.src = '';
        document.body.style.overflow = '';
        // return focus to thumbnail
        thumb.focus();
    }

    thumb.addEventListener('click', openModal);
    thumb.addEventListener('keydown', function(e){ if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(); } });

    closeBtn.addEventListener('click', closeModal);
    // Close when clicking outside the image
    modal.addEventListener('click', function(e){ if (e.target === modal) closeModal(); });
    // Close on ESC
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') closeModal(); });
})();
</script>

    <!-- Confirmation modal for Verify action -->
    <div id="verifyConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" aria-hidden="true" role="dialog" aria-labelledby="verifyConfirmTitle">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <h3 id="verifyConfirmTitle" class="text-lg font-semibold text-gray-800 mb-3">Verify passenger</h3>
            <p class="text-gray-600 mb-4">Are you sure you want to verify this passenger?</p>
            <div class="flex justify-end gap-3">
                <button id="verifyCancel" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">Cancel</button>
                <button id="verifyConfirm" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">Verify</button>
            </div>
        </div>
    </div>

    <!-- Success modal after verification -->
    <div id="verifySuccessModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden" aria-hidden="true" role="dialog" aria-labelledby="verifySuccessTitle">
        <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6 text-center">
            <h3 id="verifySuccessTitle" class="text-lg font-semibold text-green-700 mb-2">Verified</h3>
            <p id="verifySuccessMessage" class="text-gray-700 mb-4">Passenger successfully verified.</p>
            <div class="flex justify-center">
                <button id="verifySuccessOk" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded">OK</button>
            </div>
        </div>
    </div>

    <script>
    // Verify flow: open confirm modal, submit via fetch, show success modal and update UI
    (function(){
        var verifyBtn = document.getElementById('verifyBtn');
        var verifyForm = document.getElementById('verifyForm');
        var confirmModal = document.getElementById('verifyConfirmModal');
        var confirmBtn = document.getElementById('verifyConfirm');
        var cancelBtn = document.getElementById('verifyCancel');
        var successModal = document.getElementById('verifySuccessModal');
        var successOk = document.getElementById('verifySuccessOk');

        if (!verifyBtn || !verifyForm) return;

        function openConfirm() {
            confirmModal.classList.remove('hidden');
            confirmModal.setAttribute('aria-hidden','false');
            confirmBtn.focus();
            document.body.style.overflow = 'hidden';
        }
        function closeConfirm() {
            confirmModal.classList.add('hidden');
            confirmModal.setAttribute('aria-hidden','true');
            document.body.style.overflow = '';
            verifyBtn.focus();
        }
        function openSuccess() {
            successModal.classList.remove('hidden');
            successModal.setAttribute('aria-hidden','false');
            successOk.focus();
            document.body.style.overflow = 'hidden';
        }
        function closeSuccess() {
            successModal.classList.add('hidden');
            successModal.setAttribute('aria-hidden','true');
            document.body.style.overflow = '';
        }

        verifyBtn.addEventListener('click', function(e){ e.preventDefault(); openConfirm(); });
        cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeConfirm(); });

        confirmBtn.addEventListener('click', function(e){
            e.preventDefault();
            // submit via fetch
            var formData = new FormData(verifyForm);
            fetch(verifyForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            }).then(function(resp){
                return resp.json();
            }).then(function(data){
                closeConfirm();
                if (data && data.success) {
                    // update verification badge
                    var badge = document.querySelector('p span.inline-block.ml-2');
                    if (badge) {
                        badge.className = 'inline-block ml-2 px-2 py-1 rounded text-xs font-semibold bg-green-200 text-green-800';
                        badge.textContent = 'Verified';
                    }
                    // remove verify button/form
                    if (verifyForm) verifyForm.remove();
                    openSuccess();
                } else {
                    // show error in success modal area
                    var msg = document.getElementById('verifySuccessMessage');
                    if (msg) msg.textContent = data && data.error ? data.error : 'An error occurred while verifying.';
                    // change title to Error
                    var title = document.getElementById('verifySuccessTitle');
                    if (title) { title.textContent = 'Error'; title.className = 'text-lg font-semibold text-red-700 mb-2'; }
                    openSuccess();
                }
            }).catch(function(err){
                closeConfirm();
                var msg = document.getElementById('verifySuccessMessage');
                if (msg) msg.textContent = 'Network error. Please try again.';
                var title = document.getElementById('verifySuccessTitle');
                if (title) { title.textContent = 'Error'; title.className = 'text-lg font-semibold text-red-700 mb-2'; }
                openSuccess();
            });
        });

        successOk.addEventListener('click', function(e){ e.preventDefault(); closeSuccess(); });
        // Close modals on ESC
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') { if (confirmModal && confirmModal.getAttribute('aria-hidden')==='false') closeConfirm(); if (successModal && successModal.getAttribute('aria-hidden')==='false') closeSuccess(); } });
    })();
    </script>
