<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    protected $table_name;

    public function __construct()
    {
        $this->table_name = "users";
        parent::__construct($this->table_name);
    }

    public function getByEmail($email) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getUsers() {
        $stmt = $this->connection->prepare("SELECT id, name, email, role FROM users");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserByID($id) {
        $stmt = $this->connection->prepare("SELECT id, name, email, role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function editUser($id, $data) {
        // Leverage BaseDao->update to perform the update
        return $this->update($data, $id);
    }

    public function deleteUser($id) {
        return $this->delete($id);
    }
}
?>