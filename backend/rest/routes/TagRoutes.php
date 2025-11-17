<?php

class TagRoutes {
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     summary="Get all tags",
     *     description="Retrieve a list of all tags",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of tags",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Tag")
     *         )
     *     )
     * )
     */
    public static function getAll() {
        $tags = Flight::tag_service()->get_all();
        Flight::json($tags);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/{id}",
     *     summary="Get tag by ID",
     *     description="Retrieve a specific tag with playlists that use it",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tag ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag details",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public static function getById($id) {
        $response = Flight::tag_service()->get_by_id($id);
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tags/name/{name}",
     *     summary="Get tag by name",
     *     description="Retrieve a tag by its name",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="path",
     *         required=true,
     *         description="Tag name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag details",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public static function getByName($name) {
        $response = Flight::tag_service()->get_by_name($name);
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tags",
     *     summary="Create a new tag",
     *     description="Create a new tag",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="rock", description="Tag name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or duplicate tag name"
     *     )
     * )
     */
    public static function create() {
        $data = Flight::request()->data->getData();
        $response = Flight::tag_service()->add($data);
        
        if ($response['success']) {
            Flight::json($response['data'], 201);
        } else {
            Flight::halt(400, $response['error']);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tags/{id}",
     *     summary="Update a tag",
     *     description="Update an existing tag",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tag ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="updated-tag-name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Tag")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public static function update($id) {
        $data = Flight::request()->data->getData();
        $response = Flight::tag_service()->update($data, $id);
        
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tags/{id}",
     *     summary="Delete a tag",
     *     description="Delete a tag by ID",
     *     tags={"tags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tag ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     )
     * )
     */
    public static function delete($id) {
        $response = Flight::tag_service()->delete($id);
        
        if ($response['success']) {
            Flight::json(['message' => $response['message']]);
        } else {
            Flight::halt(404, $response['error']);
        }
    }
}

Flight::group('/api/tags', function() {
    Flight::route("GET /", [TagRoutes::class, 'getAll']);
    Flight::route("GET /@id", [TagRoutes::class, 'getById']);
    Flight::route("GET /name/@name", [TagRoutes::class, 'getByName']);
    Flight::route("POST /", [TagRoutes::class, 'create']);
    Flight::route("PUT /@id", [TagRoutes::class, 'update']);
    Flight::route("DELETE /@id", [TagRoutes::class, 'delete']);
});

?>
