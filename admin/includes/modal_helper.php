<?php
// Helper functions for confirmation modals

/**
 * Generate delete confirmation modal data
 * @param string $itemType - Type of item being deleted (e.g., 'trip', 'driver', 'passenger')
 * @param string $itemId - ID of the item being deleted
 * @param string $deleteUrl - URL for the delete action
 * @return array - Modal configuration data
 */
function getDeleteModalData($itemType, $itemId, $deleteUrl) {
    $itemType = ucfirst($itemType);
    return [
        'title' => "Delete {$itemType}",
        'message' => "Are you sure you want to delete this {$itemType}? This action cannot be undone.",
        'confirmUrl' => $deleteUrl,
        'confirmText' => 'Delete'
    ];
}

/**
 * Generate confirmation modal data for any action
 * @param string $title - Modal title
 * @param string $message - Modal message
 * @param string $confirmUrl - URL for the confirm action
 * @param string $confirmText - Text for confirm button (default: 'Confirm')
 * @return array - Modal configuration data
 */
function getConfirmationModalData($title, $message, $confirmUrl, $confirmText = 'Confirm') {
    return [
        'title' => $title,
        'message' => $message,
        'confirmUrl' => $confirmUrl,
        'confirmText' => $confirmText
    ];
}
?>
