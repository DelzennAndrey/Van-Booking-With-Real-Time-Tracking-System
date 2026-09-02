<?php
require_once 'includes/session.php';
require_once 'includes/db_connect.php';
check_admin_login();
include 'includes/header.php';

// Fetch all discounts
$sql = "SELECT * FROM discount ORDER BY type";
$result = $conn->query($sql);
?>
<div class="max-w-4xl mx-auto py-16 px-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-blue-700">Discount Management</h2>
        <button type="button" id="openAddDiscount"
            class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition">Add
            New Discount</button>
    </div>
    <div class="bg-white rounded-lg shadow overflow-x-auto p-8">
        <table class="min-w-full text-base text-center">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="py-5 px-8 font-semibold">Type</th>
                    <th class="py-5 px-8 font-semibold">Discount (%)</th>
                    <th class="py-5 px-8 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-4 px-8">
                                <span class="inline-block px-3 py-1.5 rounded text-sm font-semibold <?php
                                echo $row['type'] == 'PWD' ? 'bg-purple-200 text-purple-800' :
                                    ($row['type'] == 'Student' ? 'bg-blue-200 text-blue-800' :
                                        ($row['type'] == 'Senior Citizen' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-800'));
                                ?>">
                                    <?php echo htmlspecialchars($row['type']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-8"><?php echo (int)$row['price']; ?>%</td>
                            <td class="py-4 px-8 space-x-3">
                                <a href="discount_edit.php?id=<?php echo $row['disc_id']; ?>"
                                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold transition">Edit</a>
                                <a href="discount_delete.php?id=<?php echo $row['disc_id']; ?>"
                                    class="inline-block bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-semibold transition js-delete-discount">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-8 text-gray-500">No discounts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Discount Modal -->
<div id="addDiscountModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-md items-center justify-center hidden opacity-0 transition-opacity duration-200 z-50">
    <div id="addDiscountPanel"
        class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 transform transition-all duration-200 opacity-0 scale-95">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Add New Discount</h3>
            <button type="button" id="closeAddDiscount" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <form action="discount_add.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type"
                    class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                    <option value="PWD">PWD</option>
                    <option value="Student">Student</option>
                    <option value="Senior Citizen">Senior Citizen</option>
                    <option value="Regular">Regular</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount (%)</label>
                <input type="number" name="price" step="1" min="0"
                    class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" id="cancelAddDiscount"
                    class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">Add
                    Discount</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/confirmation_modal.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Delete modal
        document.querySelectorAll('.js-delete-discount').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const deleteUrl = this.getAttribute('href');
                
                window.ConfirmationModal.show(
                    'Delete Discount',
                    'Are you sure you want to delete this discount? This action cannot be undone.',
                    deleteUrl,
                    'Delete'
                );
            });
        });

        // Add Discount modal
        const addModal = document.getElementById('addDiscountModal');
        const addPanel = document.getElementById('addDiscountPanel');
        const openAdd = document.getElementById('openAddDiscount');
        const closeAdd = document.getElementById('closeAddDiscount');
        const cancelAdd = document.getElementById('cancelAddDiscount');

        // Local helpers in case global ones are not present
        function openModal(modal, panel) {
            if (!modal || !panel) return;
            modal.classList.remove('hidden', 'opacity-0');
            modal.classList.add('flex');
            // allow next frame for transition
            requestAnimationFrame(() => {
                panel.classList.remove('opacity-0', 'scale-95');
                panel.classList.add('opacity-100', 'scale-100');
            });
        }

        function closeModal(modal, panel) {
            if (!modal || !panel) return;
            panel.classList.add('opacity-0', 'scale-95');
            panel.classList.remove('opacity-100', 'scale-100');
            // match CSS duration (200ms)
            setTimeout(() => {
                modal.classList.add('opacity-0', 'hidden');
                modal.classList.remove('flex');
            }, 200);
        }

        function openAddModal() { openModal(addModal, addPanel); }
        function closeAddModal() { closeModal(addModal, addPanel); }

        if (openAdd) openAdd.addEventListener('click', openAddModal);
        if (closeAdd) closeAdd.addEventListener('click', closeAddModal);
        if (cancelAdd) cancelAdd.addEventListener('click', closeAddModal);
        if (addModal) addModal.addEventListener('click', function (e) { if (e.target === addModal) closeAddModal(); });
        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && addModal && !addModal.classList.contains('hidden')) {
                closeAddModal();
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>