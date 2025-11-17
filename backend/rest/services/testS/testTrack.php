<?php
// C:\...\backend\rest\services\testS\testTrackService.php

// --------------------------------------------------------------------------
// Configuration and Setup
// --------------------------------------------------------------------------

// Adjust the path to correctly include the TrackService file
require_once __DIR__ . '/../TrackService.php';

// --- Test Constants ---
// CRITICAL: Ensure this Playlist ID exists in your database for relational tests!
$TEST_PLAYLIST_ID = 1; 

// Global variables to store results from CREATE test
$test_track_id = null;
$timestamp = time();

// Data for creating a new track
$new_track_data = [
    'spotify_track_id' => 'spotify_test_id_' . $timestamp,
    'name' => 'Test Track Name ' . $timestamp,
    'artists' => 'Test Artist, Another One',
    'album' => 'Test Album',
    'duration_ms' => 180000,
    'preview_url' => 'http://example.com/preview/' . $timestamp,
    'external_url' => 'http://example.com/external/' . $timestamp
];

$update_track_data = [
    'name' => 'Updated Track Name ' . $timestamp,
    'duration_ms' => 195000,
    'artists' => 'Only The Best Artist'
];

// Instantiate the service
$track_service = new TrackService();

echo "\n## Track Service Test Script Start ##\n";

// Helper function for cleaner output
function print_test_result($label, $result, $success_message) {
    echo "  >> " . $label . ": ";
    if (isset($result['success']) && $result['success']) {
        echo "✅ SUCCESS: " . $success_message . "\n";
    } elseif (isset($result['error'])) {
        echo "❌ ERROR: " . $result['error'] . "\n";
    } else {
        echo "⚠️ UNEXPECTED RESULT:\n";
        print_r($result);
    }
}

// --------------------------------------------------------------------------
// 1. Testing ADD (CREATE)
// --------------------------------------------------------------------------
echo "\n--- 1. Testing add (CREATE) ---\n";

// Test 1.1: Successful creation
$result_add = $track_service->add($new_track_data);
print_test_result('Successful Add', $result_add, 'Track created and data retrieved.');

if (isset($result_add['success']) && $result_add['success'] && isset($result_add['data']['id'])) {
    $test_track_id = $result_add['data']['id'];
    echo "  >> Test Track ID generated: $test_track_id\n";
} else {
    echo "  >> Skipping subsequent tests due to failed track creation.\n";
    exit;
}

// Test 1.2: Duplicate Spotify ID check
$result_fail_duplicate = $track_service->add($new_track_data);
print_test_result('Duplicate Spotify ID', $result_fail_duplicate, 'Unexpected success (should fail for duplicate ID)');

// Test 1.3: Missing required field (name)
$fail_data_name = $new_track_data;
unset($fail_data_name['name']);
$fail_data_name['spotify_track_id'] = 'unique_fail_id';
$result_fail_name = $track_service->add($fail_data_name);
print_test_result('Missing Name Check', $result_fail_name, 'Unexpected success (should fail for missing name)');


// --------------------------------------------------------------------------
// 2. Testing READ Operations
// --------------------------------------------------------------------------
echo "\n--- 2. Testing READ Operations ---\n";

// Test 2.1: get_by_id (Successful)
$result_get_id = $track_service->get_by_id($test_track_id);
print_test_result('get_by_id (Success)', $result_get_id, 'Track retrieved successfully.');

// Test 2.2: get_by_id (Not Found)
$result_get_not_found = $track_service->get_by_id(999999);
print_test_result('get_by_id (Not Found)', $result_get_not_found, 'Unexpected success (should fail for not found)');

// Test 2.3: get_by_spotify_id (Successful)
$result_get_spotify = $track_service->get_by_spotify_id($new_track_data['spotify_track_id']);
print_test_result('get_by_spotify_id (Success)', $result_get_spotify, 'Track retrieved by Spotify ID.');

// Test 2.4: get_by_spotify_id (Not Found)
$result_get_spotify_fail = $track_service->get_by_spotify_id('non_existent_id_xyz');
print_test_result('get_by_spotify_id (Not Found)', $result_get_spotify_fail, 'Unexpected success (should fail for not found)');

// Test 2.5: get_all
$result_get_all = $track_service->get_all();
echo "  >> get_all: Total tracks retrieved: " . count($result_get_all) . "\n";


// --------------------------------------------------------------------------
// 3. Testing UPDATE
// --------------------------------------------------------------------------
echo "\n--- 3. Testing update ---\n";

// Test 3.1: Successful Update
$result_update = $track_service->update($update_track_data, $test_track_id);
print_test_result('Successful Update', $result_update, 'Track updated successfully.');

// Test 3.2: Update validation (e.g., artists too long)
$fail_update_data = ['artists' => str_repeat('A', 600)];
$result_fail_update = $track_service->update($fail_update_data, $test_track_id);
print_test_result('Update Validation Check', $result_fail_update, 'Unexpected success (should fail for long artists field)');

// Test 3.3: Updating spotify_track_id to an existing one (should fail)
$existing_id_data = ['spotify_track_id' => $new_track_data['spotify_track_id']];
$new_track_data_2 = [
    'spotify_track_id' => 'spotify_test_id_2_' . time(),
    'name' => 'Second Track'
];
$track_service->add($new_track_data_2); // Create a second track to test collision
$result_fail_collision = $track_service->update($existing_id_data, $test_track_id);
print_test_result('Update Collision Check', $result_fail_collision, 'Unexpected success (should fail due to ID collision)');


// --------------------------------------------------------------------------
// 4. Testing DELETE
// --------------------------------------------------------------------------
echo "\n--- 4. Testing delete ---\n";

// Test 4.1: Successful Deletion
$result_delete = $track_service->delete($test_track_id);
print_test_result('Successful Delete', $result_delete, 'Track deleted successfully.');

// Test 4.2: Verify track is gone
$result_verify_delete = $track_service->get_by_id($test_track_id);
print_test_result('Verify Deletion', $result_verify_delete, 'Unexpected success (track should not be found)');

// Test 4.3: Deleting the same ID again (should fail)
$result_delete_fail = $track_service->delete($test_track_id);
print_test_result('Delete Failed (Not Found)', $result_delete_fail, 'Unexpected success (should fail for not found)');

echo "\n## Track Service Test Script End ##\n";