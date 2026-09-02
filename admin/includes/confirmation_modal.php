<?php
// Reusable Confirmation Modal Component
// Usage: include this file and call showConfirmationModal($title, $message, $confirmUrl, $itemType)
?>

<!-- Delete Confirmation Modal -->
<div id="confirmationModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-md items-center justify-center hidden opacity-0 transition-opacity duration-200 z-50">
    <div id="confirmationPanel"
        class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 p-6 transform transition-all duration-200 opacity-0 scale-95">
        <h3 id="modalTitle" class="text-lg font-semibold mb-2">Confirm Action</h3>
        <p id="modalMessage" class="text-sm text-gray-600 mb-6">Are you sure you want to proceed?</p>
        <div class="flex justify-end space-x-3">
            <button id="cancelConfirm" type="button"
                class="px-4 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
            <a id="confirmAction" href="#" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Confirm</a>
        </div>
    </div>
</div>

<script>
    // Global Confirmation Modal Functions
    window.ConfirmationModal = {
        modal: null,
        panel: null,
        confirmBtn: null,
        cancelBtn: null,
        title: null,
        message: null,

        init: function() {
            this.modal = document.getElementById('confirmationModal');
            this.panel = document.getElementById('confirmationPanel');
            this.confirmBtn = document.getElementById('confirmAction');
            this.cancelBtn = document.getElementById('cancelConfirm');
            this.title = document.getElementById('modalTitle');
            this.message = document.getElementById('modalMessage');

            // Event listeners
            this.cancelBtn.addEventListener('click', () => this.closeModal());
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) this.closeModal();
            });
        },

        show: function(title, message, confirmUrl, confirmText = 'Confirm') {
            if (!this.modal || !this.confirmBtn || !this.title || !this.message) {
                this.init();
            }
            this.title.textContent = title;
            this.message.textContent = message;
            this.confirmBtn.textContent = confirmText;
            this.confirmBtn.setAttribute('href', confirmUrl);
            this.openModal();
        },

        openModal: function() {
            this.modal.classList.remove('hidden');
            this.modal.classList.add('flex');
            requestAnimationFrame(() => {
                this.modal.classList.remove('opacity-0');
                this.modal.classList.add('opacity-100');
                this.panel.classList.remove('opacity-0', 'scale-95');
                this.panel.classList.add('opacity-100', 'scale-100');
            });
        },

        closeModal: function() {
            this.modal.classList.remove('opacity-100');
            this.modal.classList.add('opacity-0');
            this.panel.classList.remove('opacity-100', 'scale-100');
            this.panel.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                this.modal.classList.add('hidden');
                this.modal.classList.remove('flex');
            }, 200);
        }
    };

    // Initialize immediately if DOM is ready; otherwise wait for DOMContentLoaded
    (function() {
        const init = function() { window.ConfirmationModal.init(); };
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>
