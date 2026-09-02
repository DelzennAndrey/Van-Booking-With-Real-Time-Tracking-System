<?php
// Test script to simulate the complete ETA flow
require_once 'backend/config/database.php';

echo "=== Testing Complete ETA Flow ===\n\n";

// 1. Test driver location retrieval (same as frontend)
echo "1. Testing Driver Location API:\n";
$driver_id = 12;
$driver_url = "https://smtsc-booking.proplocator.online/backend/api/drivers/get_driver_location.php?driver_id=" . $driver_id;
$driver_response = file_get_contents($driver_url);
$driver_data = json_decode($driver_response, true);

echo "Driver API Response: " . $driver_response . "\n";

if ($driver_data['success']) {
    $driver_lat = $driver_data['location']['latitude'];
    $driver_lng = $driver_data['location']['longitude'];
    echo "Driver Location: {$driver_lat}, {$driver_lng}\n\n";
    
    // 2. Test ETA calculation with sample passenger location
    echo "2. Testing ETA Calculation:\n";
    // Sample passenger location (nearby to driver)
    $passenger_lat = 8.06799130;
    $passenger_lng = 123.73216030;
    
    $eta_url = "https://smtsc-booking.proplocator.online/backend/api/trips/get_eta.php?origin_lat={$driver_lat}&origin_lng={$driver_lng}&destination_lat={$passenger_lat}&destination_lng={$passenger_lng}";
    $eta_response = file_get_contents($eta_url);
    $eta_data = json_decode($eta_response, true);
    
    echo "ETA API Request: {$eta_url}\n";
    echo "ETA API Response: " . $eta_response . "\n";
    
    if ($eta_data['success']) {
        echo "✅ SUCCESS: ETA = {$eta_data['eta']} minutes, Distance = {$eta_data['distance']} km\n";
        echo "Method: " . ($eta_data['method'] ?? 'N/A') . "\n";
    } else {
        echo "❌ FAILED: " . ($eta_data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ Driver location failed: " . ($driver_data['message'] ?? 'Unknown error') . "\n";
}

echo "\n=== Test Complete ===\n";
?>
