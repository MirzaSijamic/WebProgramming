<?php

Flight::group('/api/playlists', function() {

    /**
     * @OA\Get(
     *     path="/api/playlists",
     *     summary="Get all playlists",
     *     description="Retrieve a list of all playlists",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of playlists",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Playlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    Flight::route("GET /", function () {
        $playlists = Flight::playlist_service()->get_all();
        Flight::json($playlists);
    });

    /**
     * @OA\Get(
     *     path="/api/playlists/{id}",
     *     summary="Get playlist by ID",
     *     description="Retrieve a specific playlist with its tracks and tags",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Playlist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Playlist details",
     *         @OA\JsonContent(ref="#/components/schemas/Playlist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Playlist not found"
     *     )
     * )
     */
    Flight::route("GET /@id", function ($id) {
        $response = Flight::playlist_service()->get_by_id($id);
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    });

    /**
     * @OA\Get(
     *     path="/api/playlists/user/{userId}",
     *     summary="Get playlists by user ID",
     *     description="Retrieve all playlists belonging to a specific user",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user playlists",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Playlist")
     *         )
     *     )
     * )
     */
    Flight::route("GET /user/@userId", function ($userId) {
        $response = Flight::playlist_service()->get_by_user_id($userId);
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(500, $response['error']);
        }
    });

    /**
     * @OA\Post(
     *     path="/api/playlists",
     *     summary="Create a new playlist",
     *     description="Create a new playlist",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "user_id"},
     *             @OA\Property(property="title", type="string", example="My Favorite Songs", description="Playlist title"),
     *             @OA\Property(property="description", type="string", example="A collection of my favorite tracks", description="Playlist description"),
     *             @OA\Property(property="user_id", type="integer", example=1, description="User ID who owns the playlist"),
     *             @OA\Property(property="is_public", type="boolean", example=false, description="Whether the playlist is public")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Playlist created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Playlist")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    Flight::route("POST /", function () {
        $data = Flight::request()->data->getData();
        $response = Flight::playlist_service()->add($data);
        
        if ($response['success']) {
            Flight::json($response['data'], 201);
        } else {
            Flight::halt(400, $response['error']);
        }
    });

    /**
     * @OA\Put(
     *     path="/api/playlists/{id}",
     *     summary="Update a playlist",
     *     description="Update an existing playlist",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Playlist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Playlist Title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="is_public", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Playlist updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Playlist")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Playlist not found"
     *     )
     * )
     */
    Flight::route("PUT /@id", function ($id) {
        $data = Flight::request()->data->getData();
        $response = Flight::playlist_service()->update($data, $id);
        
        if ($response['success']) {
            Flight::json($response['data']);
        } else {
            Flight::halt(404, $response['error']);
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/playlists/{id}",
     *     summary="Delete a playlist",
     *     description="Delete a playlist by ID",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Playlist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Playlist deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Playlist not found"
     *     )
     * )
     */
    Flight::route("DELETE /@id", function ($id) {
        $response = Flight::playlist_service()->delete($id);
        
        if ($response['success']) {
            Flight::json(['message' => $response['message']]);
        } else {
            Flight::halt(404, $response['error']);
        }
    });

    /**
     * @OA\Post(
     *     path="/api/playlists/{id}/tracks/{trackId}",
     *     summary="Add track to playlist",
     *     description="Add a track to a playlist",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Playlist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="trackId",
     *         in="path",
     *         required=true,
     *         description="Track ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Track added to playlist"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Track already in playlist or error"
     *     )
     * )
     */
    Flight::route("POST /@id/tracks/@trackId", function ($id, $trackId) {
        $response = Flight::playlist_service()->add_track($id, $trackId);
        
        if ($response['success']) {
            Flight::json($response['data'], 201);
        } else {
            Flight::halt(400, $response['error']);
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/playlists/{id}/tracks/{trackId}",
     *     summary="Remove track from playlist",
     *     description="Remove a track from a playlist",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Playlist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="trackId",
     *         in="path",
     *         required=true,
     *         description="Track ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Track removed from playlist"
     *     )
     * )
     */
    Flight::route("DELETE /@id/tracks/@trackId", function ($id, $trackId) {
        $response = Flight::playlist_service()->remove_track($id, $trackId);
        
        if ($response['success']) {
            Flight::json(['message' => $response['message']]);
        } else {
            Flight::halt(400, $response['error']);
        }
    });

    /**
     * @OA\Post(
     *     path="/api/playlists/{id}/tags/{tagId}",
     *     summary="Add tag to playlist",
     *     description="Add a tag to a playlist",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Playlist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tagId",
     *         in="path",
     *         required=true,
     *         description="Tag ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag added to playlist"
     *     )
     * )
     */
    Flight::route("POST /@id/tags/@tagId", function ($id, $tagId) {
        $response = Flight::playlist_service()->add_tag($id, $tagId);
        
        if ($response['success']) {
            Flight::json($response['data'], 201);
        } else {
            Flight::halt(400, $response['error']);
        }
    });

    /**
     * @OA\Delete(
     *     path="/api/playlists/{id}/tags/{tagId}",
     *     summary="Remove tag from playlist",
     *     description="Remove a tag from a playlist",
     *     tags={"playlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Playlist ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tagId",
     *         in="path",
     *         required=true,
     *         description="Tag ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag removed from playlist"
     *     )
     * )
     */
    Flight::route("DELETE /@id/tags/@tagId", function ($id, $tagId) {
        $response = Flight::playlist_service()->remove_tag($id, $tagId);
        
        if ($response['success']) {
            Flight::json(['message' => $response['message']]);
        } else {
            Flight::halt(400, $response['error']);
        }
    });
});

?>
