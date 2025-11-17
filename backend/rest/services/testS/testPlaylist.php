<?php
// C:\...\backend\rest\services\testS\testPlaylistService.php

// --------------------------------------------------------------------------
// Configuration and Setup
// --------------------------------------------------------------------------

// Adjust the path to correctly include the PlaylistService file
require_once __DIR__ . '/../PlaylistService.php';

// --- Test Constants ---
// CRITICAL: Ensure these IDs exist in your database!
$TEST_USER_ID = 1; 
$TEST_TRACK_ID = 1;
$TEST_TAG_ID = 1;

// Global variables to store results from CREATE test
$test_playlist_id = null;
$new_playlist_data = [
    'title' => 'My Test Playlist ' . time(),
    'description' => 'A temporary playlist created for testing purposes.',
    'user_id' => $TEST_USER_ID,
    'is_public' => 1
];

$update_playlist_data = [
    'title' => 'Updated Test Playlist ' . time(),
    'description' => 'The description has been modified.',
    'is_public' => 0 // Change privacy status
];


// Instantiate the service
$playlist_service = new PlaylistService();

echo "\n## Playlist Service Test Script Start ##\n";

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
$result_add = $playlist_service->add($new_playlist_data);
print_test_result('Successful Add', $result_add, 'Playlist created and data retrieved.');

if (isset($result_add['success']) && $result_add['success'] && isset($result_add['data']['id'])) {
    $test_playlist_id = $result_add['data']['id'];
    echo "  >> Test Playlist ID generated: $test_playlist_id\n";
} else {
    echo "  >> Skipping subsequent tests due to failed playlist creation.\n";
    exit;
}

// Test 1.2: Missing required field (title)
$fail_data_title = $new_playlist_data;
unset($fail_data_title['title']);
$result_fail_title = $playlist_service->add($fail_data_title);
print_test_result('Missing Title Check', $result_fail_title, 'Unexpected success (should fail for missing title)');

// Test 1.3: Non-existent User ID
$fail_data_user = $new_playlist_data;
$fail_data_user['user_id'] = 999999; // Assume this ID does not exist
$result_fail_user = $playlist_service->add($fail_data_user);
print_test_result('Non-existent User Check', $result_fail_user, 'Unexpected success (should fail for non-existent user)');


// --------------------------------------------------------------------------
// 2. Testing READ Operations
// --------------------------------------------------------------------------
echo "\n--- 2. Testing READ Operations ---\n";

// Test 2.1: get_by_id (Successful)
$result_get_id = $playlist_service->get_by_id($test_playlist_id);
print_test_result('get_by_id (Success)', $result_get_id, 'Playlist retrieved successfully.');

// Test 2.2: get_by_id (Not Found)
$result_get_not_found = $playlist_service->get_by_id(999999);
print_test_result('get_by_id (Not Found)', $result_get_not_found, 'Unexpected success (should fail for not found)');

// Test 2.3: get_all
$result_get_all = $playlist_service->get_all();
print_test_result('get_all', $result_get_all, 'All playlists retrieved (Total: ' . count($result_get_all) . ')');

// Test 2.4: get_by_user_id
$result_get_user = $playlist_service->get_by_user_id($TEST_USER_ID);
print_test_result('get_by_user_id', $result_get_user, 'Playlists retrieved for test user (Total: ' . count($result_get_user['data']) . ')');


// --------------------------------------------------------------------------
// 3. Testing ADD/REMOVE TRACKS and TAGS
// --------------------------------------------------------------------------
echo "\n--- 3. Testing Relational Operations (Tracks & Tags) ---\n";

// Test 3.1: Add Track
$result_add_track = $playlist_service->add_track($test_playlist_id, $TEST_TRACK_ID);
print_test_result('add_track', $result_add_track, 'Track successfully added.');

// Test 3.2: Add Track again (should fail with specific error)
$result_add_track_fail = $playlist_service->add_track($test_playlist_id, $TEST_TRACK_ID);
print_test_result('add_track (Duplicate)', $result_add_track_fail, 'Unexpected success (should fail for duplicate)');

// Test 3.3: Remove Track
$result_remove_track = $playlist_service->remove_track($test_playlist_id, $TEST_TRACK_ID);
print_test_result('remove_track', $result_remove_track, 'Track successfully removed.');

// Test 3.4: Add Tag
$result_add_tag = $playlist_service->add_tag($test_playlist_id, $TEST_TAG_ID);
print_test_result('add_tag', $result_add_tag, 'Tag successfully added.');

// Test 3.5: Remove Tag
$result_remove_tag = $playlist_service->remove_tag($test_playlist_id, $TEST_TAG_ID);
print_test_result('remove_tag', $result_remove_tag, 'Tag successfully removed.');


// --------------------------------------------------------------------------
// 4. Testing UPDATE
// --------------------------------------------------------------------------
echo "\n--- 4. Testing update ---\n";

// Test 4.1: Successful Update
$result_update = $playlist_service->update($update_playlist_data, $test_playlist_id);
print_test_result('Successful Update', $result_update, 'Playlist updated successfully.');

// Test 4.2: Update validation (e.g., title too long)
$fail_update_data = ['title' => str_repeat('A', 300)];
$result_fail_update = $playlist_service->update($fail_update_data, $test_playlist_id);
print_test_result('Update Validation Check', $result_fail_update, 'Unexpected success (should fail for long title)');


// --------------------------------------------------------------------------
// 5. Testing DELETE
// --------------------------------------------------------------------------
echo "\n--- 5. Testing delete ---\n";

// Test 5.1: Successful Deletion
$result_delete = $playlist_service->delete($test_playlist_id);
print_test_result('Successful Delete', $result_delete, 'Playlist deleted successfully.');

// Test 5.2: Deleting the same ID again (should fail)
$result_delete_fail = $playlist_service->delete($test_playlist_id);
print_test_result('Delete Failed (Not Found)', $result_delete_fail, 'Unexpected success (should fail for not found)');

echo "\n## Playlist Service Test Script End ##\n";