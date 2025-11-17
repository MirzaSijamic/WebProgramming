<?php
// Ensure all necessary DAOs are included
require_once 'UserDao.php'; 
require_once 'TrackDao.php'; 
require_once 'PlaylistDao.php'; 
require_once 'TagDao.php'; 

// --- Setup Variables ---
$unique_marker = time();
$userDao = new UserDao();
$playlistDao = new PlaylistDao();

echo "=================================================\n";
echo "           PLAYLIST DAO TEST START               \n";
echo "=================================================\n\n";

/*
// -----------------------------------------------------------
// 1. USER CREATION (MUST BE UNCOMMENTED TO GET $user_id)
// -----------------------------------------------------------
$user_email = 'playlist_test_user_' . $unique_marker . '@dao.com';
$user_data = $userDao->add([
    'name' => 'Playlist Test User',
    'email' => $user_email,
    'password_hash' => password_hash('securepass', PASSWORD_DEFAULT), 
    'role' => 'user'
]);
$user_id = $user_data['id'] ?? 0;

if (!$user_id) {
    echo "❌ USER: ERROR: Failed to create required user. Stopping test.\n";
    exit;
}
echo "✅ USER: Created required user with ID: {$user_id}\n";
echo "-------------------------------------------------\n\n";


// -----------------------------------------------------------
// 2. PLAYLIST DAO TESTS
// -----------------------------------------------------------
$playlist_name = 'My First Playlist ' . $unique_marker;

// Test Create (C)
$playlist_data = $playlistDao->add([
    'user_id' => $user_id, // This is now a valid ID
    'title' => $playlist_name,
    'description' => 'A playlist created via DAO test.',
    'is_public' => 1 // true
]);
$playlist_id = $playlist_data['id'] ?? 0;

if ($playlist_id) {
    echo "✅ PLAYLIST: Created playlist '{$playlist_name}' with ID: {$playlist_id}\n";
} else {
    echo "❌ PLAYLIST: Playlist creation failed.\n";
}

// Test Read (R)
$fetched_playlist = $playlistDao->getById($playlist_id);

// FIX: Change 'name' to the actual column name you used (e.g., 'title')
if ($fetched_playlist && $fetched_playlist['title'] === $playlist_name) {
    echo "✅ PLAYLIST: getById successful. Found playlist: " . $fetched_playlist['title'] . "\n";
} else {
    echo "❌ PLAYLIST: getById failed. (Returned data: " . print_r($fetched_playlist, true) . ")\n";
}
echo "-------------------------------------------------\n\n";

/*
// -----------------------------------------------------------
// 3. CLEANUP
// -----------------------------------------------------------
$playlistDao->delete($playlist_id);
$userDao->delete($user_id);
echo "INFO: Cleaned up created records (User and Playlist).\n";
echo "=================================================\n";
*/

$tagDao = new TagDao();
$test_tag_name = 'Indie'; 

// 1. Test Create (C)
$tag_data = $tagDao->add(['name' => $test_tag_name]);
$tag_id = $tag_data['id'] ?? 0;

echo "✅ TAG: Created tag '{$test_tag_name}' with ID: {$tag_id}\n";

// 2. Test Custom Read (getTagByName)
$fetched_tag = $tagDao->getTagByName($test_tag_name);

if ($fetched_tag && $fetched_tag['id'] == $tag_id) {
    echo "✅ TAG: getTagByName successful. Found tag: " . $fetched_tag['name'] . "\n";
} else {
    echo "❌ TAG: getTagByName failed.\n";
}

?>