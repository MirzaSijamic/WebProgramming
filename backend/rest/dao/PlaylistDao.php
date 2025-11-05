<?php

require_once 'BaseDao.php';

class PlaylistDao extends BaseDao {
 protected $table_name;

    public function __construct()
    {
        $this->table_name = "playlists"; 
        parent::__construct($this->table_name);
    }

    public function getPlaylistsByUserId($userId)
    {
        // Query to select all playlists belonging to a specific user, ordered by creation date.
        $query = "SELECT * FROM playlists WHERE user_id = :user_id ORDER BY created_at DESC";
        
        // The inherited query method fetches multiple rows.
        return $this->query($query, ['user_id' => $userId]);
    }
}
?>