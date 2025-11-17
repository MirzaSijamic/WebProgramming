<?php
// test_base_dao_logging.php

// Use robust pathing to ensure all DAOs are loaded
require_once __DIR__ . '/UserDao.php'; 
require_once __DIR__ . '/LogsDao.php'; 

// --- Setup ---
$unique_marker = time();
$userDao = new UserDao();
$logsDao = new LogsDao();

echo "=================================================\n";
echo "       BASE DAO AUTOMATED LOGGING TEST           \n";
echo "=================================================\n\n";

// --- 1. Get initial log count for comparison ---
$initialLogs = $logsDao->getAll();
$initialCount = count($initialLogs);
echo "INFO: Initial log count: {$initialCount}\n";

// --- 2. Create a Test User (This automatically triggers BaseDao::add() logging) ---
$user_email = 'base_log_test_' . $unique_marker . '@dao.com';

echo "--- Creating New User ---\n";
$newUserData = $userDao->add([
    'name' => 'Automated Logger Test',
    'email' => $user_email,
    'password_hash' => password_hash('securepass', PASSWORD_DEFAULT),
    'role' => 'user'
]);
$user_id = $newUserData['id'] ?? 0;

if (!$user_id) {
    echo "❌ USER FAILURE: Could not create user record. Stopping test.\n";
    exit;
}
echo "✅ USER SUCCESS: User created with ID: {$user_id}\n";
echo "-------------------------------------------------\n\n";


// --- 3. Verification ---
$finalLogs = $logsDao->getAll();
$finalCount = count($finalLogs);

echo "--- Verifying Log Entry ---\n";

if ($finalCount === $initialCount + 1) {
    echo "✅ LOGGING SUCCESS: Log count increased by one (from {$initialCount} to {$finalCount}).\n";
    
    // Check the latest log entry
    $latestEntry = end($finalLogs);
    
    // Expected action is USERS_CREATED based on table name 'users'
    $expectedAction = 'USERS_CREATED'; 
    $details = json_decode($latestEntry['details'], true);

    if (
        $latestEntry['action'] === $expectedAction &&
        $details['new_id'] == $user_id &&
        $details['table'] === 'users'
    ) {
        echo "✅ LOGGING DATA MATCH: The log entry is correct.\n";
        echo "   -> Action: " . $latestEntry['action'] . "\n";
        echo "   -> Logged ID: " . $details['new_id'] . "\n";
    } else {
        echo "❌ LOGGING DATA MISMATCH: Latest entry was found but data does not match expectations.\n";
        echo "   (Check your LogsDao table name and BaseDao logging logic)\n";
        print_r($latestEntry);
    }
} else {
    echo "❌ LOGGING FAILURE: Expected 1 new log, but found " . ($finalCount - $initialCount) . " new logs.\n";
}

// --- 4. Cleanup ---
$userDao->delete($user_id);
echo "\nINFO: Cleaned up created user record (ID: {$user_id}).\n";
echo "=================================================\n";

?>