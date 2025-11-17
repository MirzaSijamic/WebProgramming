<?php
//backend\rest\services\UserService.php
// 1. Path Setup: Adjust this path as necessary based on where your test file is located.
// We are requiring the UserService class that you wrote.
require_once '../UserService.php'; 

// --- Setup ---
echo "## User Service Test Script Start ##\n";
$user_service = new UserService();

// Data for creating a new user (Ensure these values meet your validation rules)
$new_user_data = [
    'name' => 'John Doe Test',
    'email' => 'john.doe.test@example.com',
    'password_hash' => 'SecurePass123'
    
    // Add any other required fields from your database schema
];

$update_user_data = [
    'first_name' => 'Johnny',
    'status' => 'active' // Example of a field you might update
];

// Variable to store the ID of the created user for later operations
$created_user_id = null;


// --- Test Case 1: Add a New User (Create) ---
echo "\n--- 1. Testing addUser (CREATE) ---\n";
try {
    $result = $user_service->addUser($new_user_data);

    //$result = $user_service->addUser($new_user_data);
    
    print_r($result);
    
    if ($result['success'] ?? false) {
        $created_user_id = $result['data']['id'] ?? null;
        echo "Successfully added user with ID: " . $created_user_id . "\n";
    } else {
        echo "ERROR: Failed to add user. Reason: " . ($result['error'] ?? 'Unknown error') . "\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION caught during addUser: " . $e->getMessage() . "\n";
}


// --- Test Case 2: Get All Users (Read All) ---
echo "\n--- 2. Testing getUsers (READ ALL) ---\n";
$users = $user_service->getUsers();
print_r($users);
echo "Total users retrieved: " . count($users['data'] ?? []) . "\n";


// --- Test Case 3: Get User by ID (Read Single) ---
if ($created_user_id) {
    echo "\n--- 3. Testing getUserById (READ SINGLE) ---\n";
    $user_by_id = $user_service->getUserById($created_user_id);
    print_r($user_by_id);
} else {
    echo "\n--- 3. Skipping getUserById because no user ID was generated ---\n";
}


// --- Test Case 4: Edit User (Update) ---
if ($created_user_id) {
    echo "\n--- 4. Testing editUser (UPDATE) ---\n";
    
    // Merge the ID into the update data
    $update_user_data['id'] = $created_user_id; 
    
    try {
        $user_service->editUser($update_user_data);
        // Verify the update by fetching the user again
        $updated_user = $user_service->getUserById($created_user_id);
        echo "Updated User Data:\n";
        print_r($updated_user);
    } catch (Exception $e) {
        echo "EXCEPTION caught during editUser: " . $e->getMessage() . "\n";
    }
} else {
    echo "\n--- 4. Skipping editUser because no user ID was generated ---\n";
}


// --- Test Case 5: Authenticate User ---
echo "\n--- 5. Testing authenticate ---\n";
$authenticated_user = $user_service->authenticate($new_user_data['email'], $new_user_data['password']);
if ($authenticated_user) {
    echo "Authentication successful:\n";
    print_r($authenticated_user);
} else {
    echo "Authentication failed.\n";
}


// --- Test Case 6: Delete User (Clean Up) ---
if ($created_user_id) {
    echo "\n--- 6. Testing deleteUser (DELETE) ---\n";
    $user_service->deleteUser($created_user_id);
    
    // Verify deletion
    $deleted_user_check = $user_service->getUserById($created_user_id);
    if (empty($deleted_user_check)) {
        echo "Successfully deleted and verified user ID " . $created_user_id . " is gone.\n";
    } else {
        echo "WARNING: User ID " . $created_user_id . " was NOT deleted.\n";
    }
} else {
    echo "\n--- 6. Skipping deleteUser because no user ID was generated ---\n";
}

echo "\n## User Service Test Script End ##\n";

?>