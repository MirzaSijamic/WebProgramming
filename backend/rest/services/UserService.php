<?php

require_once __DIR__ . "/../dao/UserDao.php";
require_once __DIR__ . "/BaseService.php";

class UserService extends BaseService {
    private $userDao;

    public function __construct() {
        $this->userDao = new UserDao();
        parent::__construct($this->userDao);
    }

    public function addUser($user) {

        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
        if($email_exists){
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $entity['password'] = password_hash($entity['password'], PASSWORD_BCRYPT);

        $entity = parent::add($entity);

        unset($entity['password']);
        
        return ['success' => true, 'data' => $entity];  
    }

    public function getUsers() {
        $data = $this->userDao->getUsers();
        return ["data" => $data];
    }

    public function getUserById($user_id) {
        return $this->userDao->getUserByID($user_id);
    }

    public function deleteUser($user_id) {
        $this->userDao->deleteUser($user_id);
    }

    public function editUser($user) {
        $user_id = $user['id'];
        unset($user['id']);

        // Handle password hashing if password is being updated
        if (isset($user['password']) && !empty($user['password'])) {
            $user['password'] = password_hash($user['password'], PASSWORD_BCRYPT);
        } else {
            // Don't update password if not provided
            unset($user['password']);
        }

        $this->userDao->editUser($user_id, $user);
    }

    // Additional method for authentication
    public function authenticate($email, $password) {
        $user = $this->userDao->getByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>