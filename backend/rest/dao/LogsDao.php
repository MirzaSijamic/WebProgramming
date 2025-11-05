<?php
require_once 'BaseDao.php';

class LogsDao extends BaseDao {
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "logs";
        parent::__construct($this->table_name);
    }

    public function logAction(
        string $action, 
        array $details = [], 
    ) {
        $data = [
            'action' => $action,
            'details' => json_encode($details), 
        ];

        $data = array_filter($data, fn($value) => $value !== null);

        return $this->add($data); 
    }
}
?>