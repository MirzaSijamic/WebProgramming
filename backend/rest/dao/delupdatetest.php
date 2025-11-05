<?php
// test_crud_update_delete.php

// Ensure all necessary DAOs are included
require_once __DIR__ . '/UserDao.php'; 
// LogsDao is automatically included via BaseDao, but good practice to require if used directly.

// --- Setup ---
$unique_marker = time();
$userDao = new UserDao();

echo "=================================================\n";
echo "           UPDATE AND DELETE CRUD TEST           \n";
echo "=================================================\n\n";

// --- 1. Create a User for Testing (The 'C' in CRUD) ---
$original_name = 'Test User ' . $unique_marker;
$original_email = 'test_user_' . $unique_marker . '@dao.com';

$newUserData = $userDao->add([
    'name' => $original_name,
    'email' => $original_email,
    'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
    'role' => 'user'
]);
$user_id = $newUserData['id'] ?? 0;

if (!$user_id) {
    echo "❌ SETUP FAILURE: Could not create user record. Stopping test.\n";
    exit;
}
echo "✅ SETUP: User created with ID: {$user_id}\n";
echo "-------------------------------------------------\n\n";


// =========================================================
// 2. Test UPDATE (The 'U' in CRUD)
// =========================================================
$new_name = 'Updated Name ' . $unique_marker;
$update_data = ['name' => $new_name];

echo "--- Testing UPDATE ---\n";

// Call the inherited update() method
$updatedEntity = $userDao->update($update_data, $user_id);

// Verification: Read the record back to check the change
$fetched_user = $userDao->getById($user_id);

if ($fetched_user && $fetched_user['name'] === $new_name) {
    echo "✅ UPDATE SUCCESS: Name updated successfully from '{$original_name}' to '{$new_name}'.\n";
    // echo "Updated User Data:\n";
    // print_r($fetched_user);
} else {
    echo "❌ UPDATE FAILURE: Name in database ('{$fetched_user['name']}') does not match expected ('{$new_name}').\n";
}
echo "-------------------------------------------------\n\n";


// =========================================================
// 3. Test DELETE (The 'D' in CRUD)
// =========================================================
echo "--- Testing DELETE ---\n";

// Call the inherited delete() method
$delete_result = $userDao->delete($user_id);

if ($delete_result) {
    echo "✅ DELETE SUCCESS: Record deleted successfully.\n";
    
    // Verification: Try to read the record back
    $fetched_user_after_delete = $userDao->getById($user_id);
    
    if (empty($fetched_user_after_delete)) {
        echo "✅ DELETE VERIFICATION: getById returned nothing (record is gone).\n";
    } else {
        echo "❌ DELETE VERIFICATION FAILURE: getById still returned data after deletion.\n";
        print_r($fetched_user_after_delete);
    }
} else {
    echo "❌ DELETE FAILURE: delete() method reported an issue.\n";
}

echo "-------------------------------------------------\n\n";
echo "=================================================\n";
?>