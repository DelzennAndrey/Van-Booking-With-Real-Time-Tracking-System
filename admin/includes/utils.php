<?php
/**
 * Utility Functions for SMTSC Admin System
 * Common functions used across multiple files
 */

/**
 * Sanitize and validate input data
 * @param string $data - Input data to sanitize
 * @param string $type - Type of validation (string, int, email, etc.)
 * @return mixed - Sanitized data
 */
function sanitizeInput($data, $type = 'string') {
    $data = trim($data);
    
    switch ($type) {
        case 'int':
            return filter_var($data, FILTER_VALIDATE_INT) ?: 0;
        case 'float':
            return filter_var($data, FILTER_VALIDATE_FLOAT) ?: 0.0;
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        default:
            return filter_var($data, FILTER_SANITIZE_STRING);
    }
}

/**
 * Format currency for display
 * @param float $amount - Amount to format
 * @param string $currency - Currency symbol (default: ₱)
 * @return string - Formatted currency string
 */
function formatCurrency($amount, $currency = '₱') {
    return $currency . number_format($amount, 2);
}

/**
 * Format number with thousands separator
 * @param int|float $number - Number to format
 * @param int $decimals - Number of decimal places
 * @return string - Formatted number string
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals);
}

/**
 * Get status badge HTML with appropriate styling
 * @param string $status - Status value
 * @param string $type - Type of status (booking, trip, van, etc.)
 * @return string - HTML badge
 */
function getStatusBadge($status, $type = 'default') {
    $status = strtolower($status);
    $badgeClasses = '';
    
    switch ($type) {
        case 'booking':
            $badgeClasses = match($status) {
                'confirmed' => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
                'pending' => 'bg-yellow-100 text-yellow-700',
                default => 'bg-gray-100 text-gray-700'
            };
            break;
        case 'trip':
            $badgeClasses = match($status) {
                'scheduled' => 'bg-blue-100 text-blue-700',
                'ongoing' => 'bg-green-100 text-green-700',
                'completed' => 'bg-gray-100 text-gray-700',
                'disabled' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700'
            };
            break;
        case 'van':
            $badgeClasses = match($status) {
                'active' => 'bg-green-100 text-green-700',
                'maintenance' => 'bg-yellow-100 text-yellow-700',
                'inactive' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700'
            };
            break;
        case 'driver':
            $badgeClasses = match($status) {
                'active' => 'bg-green-100 text-green-700',
                'inactive' => 'bg-gray-100 text-gray-700',
                default => 'bg-gray-100 text-gray-700'
            };
            break;
        case 'passenger':
            $badgeClasses = match($status) {
                'pwd' => 'bg-purple-100 text-purple-700',
                'student' => 'bg-blue-100 text-blue-700',
                'senior citizen' => 'bg-yellow-100 text-yellow-700',
                'regular' => 'bg-gray-100 text-gray-700',
                default => 'bg-gray-100 text-gray-700'
            };
            break;
        default:
            $badgeClasses = 'bg-gray-100 text-gray-700';
    }
    
    return '<span class="inline-block px-2 py-1 rounded text-xs font-semibold ' . $badgeClasses . '">' . 
           htmlspecialchars(ucfirst($status)) . '</span>';
}

/**
 * Get current page name for navigation highlighting
 * @return string - Current page name
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF'], '.php');
}

/**
 * Check if current page matches given page name
 * @param string $pageName - Page name to check
 * @return bool - True if current page matches
 */
function isCurrentPage($pageName) {
    return getCurrentPage() === $pageName;
}

/**
 * Generate navigation item classes with active state
 * @param string $pageName - Page name to check
 * @return string - CSS classes for navigation item
 */
function getNavItemClasses($pageName) {
    $baseClasses = 'nav-item flex items-center px-4 py-3 rounded-lg text-gray-700 font-medium transition-all duration-300';
    
    if (isCurrentPage($pageName)) {
        return $baseClasses . ' bg-blue-50 text-blue-700 border-r-2 border-blue-600 active';
    }
    
    return $baseClasses . ' hover:bg-gray-50';
}

/**
 * Generate confirmation modal data for delete actions
 * @param string $itemType - Type of item being deleted
 * @param string $itemId - ID of the item
 * @param string $deleteUrl - URL for delete action
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
 * Log admin actions for audit trail
 * @param string $action - Action performed
 * @param string $details - Additional details
 * @param int $adminId - Admin user ID
 */
function logAdminAction($action, $details = '', $adminId = null) {
    // This could be expanded to write to a log file or database
    // For now, we'll just return true
    return true;
}

/**
 * Validate date format
 * @param string $date - Date string to validate
 * @param string $format - Expected date format (default: Y-m-d)
 * @return bool - True if valid date
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Get time ago string
 * @param string $datetime - DateTime string
 * @return string - Human readable time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31536000) return floor($time/2592000) . ' months ago';
    
    return floor($time/31536000) . ' years ago';
}
?>
