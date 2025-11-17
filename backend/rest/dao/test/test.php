<?php
require_once '../UserDao.php'; 
require_once '../TrackDao.php'; 
require_once '../PlaylistDao.php'; 
require_once '../TagDao.php'; 

$userDao = new UserDao();
$allUsers = $userDao->getAll(); // Inherited from BaseDao

echo "--- All Users Found ---\n";
print_r($allUsers);
//$userDao = new UserDao();

/*
$userDao->add([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
    'role' => 'user'
]);
*/

/*
$trackDao = new TrackDao();
$testSpotifyId = '6FyvYjS0H5h87F4r'; // A placeholder ID

// Test Create (C)
$trackData = $trackDao->add([
    'spotify_track_id' => $testSpotifyId,
    'name' => 'My Test Track',
    'artists' => 'The Coders',
    'album' => 'Milestone Album',
    'duration_ms' => 180000,
]);

// FIX: Call getAll() on the TrackDao object, not the inserted track data array.
$tracks = $trackDao->getAll(); 
echo "--- All Tracks in Database ---\n";
print_r($tracks);
*/
/*
$playlistDao = new PlaylistDao();


$playlist_name = 'My First Playlist ' . $unique_marker;

// Test Create (C)
$playlist_data = $playlistDao->add([
    'user_id' => $user_id, // Link to the user created above
    'name' => $playlist_name,
    'description' => 'A playlist created via DAO test.',
    'public' => 1 // true
]);
$playlist_id = $playlist_data['id'] ?? 0;
*/


?>