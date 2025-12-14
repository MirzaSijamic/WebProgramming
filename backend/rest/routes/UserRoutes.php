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
        $users = Flight::userService()->getUsers();
        Flight::json($users);
    });
});

?>
