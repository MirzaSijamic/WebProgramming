<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::group('/users', function() {
    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     description="Retrieve a list of all users",
     *     tags={"users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    Flight::route('GET /', function() {
        $token = Flight::auth_middleware()->getTokenFromHeader();
        Flight::auth_middleware()->verifyToken($token);
        // allow both admin and regular users to view list
        Flight::auth_middleware()->authorizeRoles(['admin', 'user']);

        $users = Flight::userService()->getUsers();
        Flight::json($users);
    });

    // Get single user by id
    Flight::route('GET /@id', function($id) {
        $token = Flight::auth_middleware()->getTokenFromHeader();
        Flight::auth_middleware()->verifyToken($token);
        Flight::auth_middleware()->authorizeRoles(['admin', 'user']);

        $user = Flight::userService()->getUserById($id);
        if ($user) {
            Flight::json(['success' => true, 'data' => $user]);
        } else {
            Flight::json(['success' => false, 'error' => 'User not found'], 404);
        }
    });

    // Create user
    Flight::route('POST /', function() {
        $token = Flight::auth_middleware()->getTokenFromHeader();
        Flight::auth_middleware()->verifyToken($token);
        // only admins can create users via this endpoint
        Flight::auth_middleware()->authorizeRole('admin');

        $body = Flight::request()->data->getData();
        $result = Flight::userService()->addUser($body);
        Flight::json($result);
    });

    // Update user
    Flight::route('PUT /@id', function($id) {
        $token = Flight::auth_middleware()->getTokenFromHeader();
        Flight::auth_middleware()->verifyToken($token);
        // only admins can update any user; you could add checks here
        // to allow users to update their own profile (compare ids)
        Flight::auth_middleware()->authorizeRole('admin');

        $body = Flight::request()->data->getData();
        $body['id'] = $id;
        $result = Flight::userService()->editUser($body);
        Flight::json(['success' => true, 'data' => $result]);
    });

    // Delete user
    Flight::route('DELETE /@id', function($id) {
        $token = Flight::auth_middleware()->getTokenFromHeader();
        Flight::auth_middleware()->verifyToken($token);
        // only admins can delete users
        Flight::auth_middleware()->authorizeRole('admin');

        $ok = Flight::userService()->deleteUser($id);
        Flight::json(['success' => (bool)$ok]);
    });
});

?>
