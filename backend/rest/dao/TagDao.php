<?php

require_once 'BaseDao.php';

class TagDao extends BaseDao {
 protected $table_name;

    public function __construct()
    {
        $this->table_name = "tags"; 
        parent::__construct($this->table_name);
    }

    public function getTagByName($name)
    {
        $query = "SELECT * FROM tags WHERE name = :name";
        
        // The inherited query_unique method fetches a single row.
        return $this->query_unique($query, ['name' => $name]);
    }
}
?>