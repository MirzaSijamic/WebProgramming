<?php

require_once 'BaseDao.php';

class TrackDao extends BaseDao {
 protected $table_name;

    public function __construct()
    {
        $this->table_name = "tracks"; 
        parent::__construct($this->table_name);
    }

    public function getTrackBySpotifyId($spotifyTrackId)
    {
        $query = "SELECT * FROM tracks WHERE spotify_track_id = :spotify_track_id";

        return $this->query_unique($query, ['spotify_track_id' => $spotifyTrackId]);
    }
}
?>