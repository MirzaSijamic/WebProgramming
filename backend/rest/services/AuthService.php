<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {
    private $auth_dao;
    public function __construct() {
        $this->auth_dao = new AuthDao();
        parent::__construct(new AuthDao);
    }

    public function get_user_by_email($email){
        return $this->auth_dao->get_user_by_email($email);
    }

    public function register($entity) {   
        
        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
        if($email_exists){
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        $entity['password_hash'] = password_hash($entity['password'], PASSWORD_BCRYPT);

        // Remove plain password before inserting into DB so BaseDao->add
        // doesn't try to insert a non-existent `password` column.
        unset($entity['password']);

        $entity = parent::add($entity);

        // Don't return the password hash to the client
        if (isset($entity['password_hash'])) {
            unset($entity['password_hash']);
        }

        return ['success' => true, 'data' => $entity];
                   
    }

    public function login($entity) {   
        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->auth_dao->get_user_by_email($entity['email']);
        if(!$user){
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        if(!$user || !password_verify($entity['password'], $user['password_hash']))
            return ['success' => false, 'error' => 'Invalid username or password.'];

        // Remove password hash before returning user data / building JWT
        if (isset($user['password_hash'])) {
            unset($user['password_hash']);
        }
        
        $jwt_payload = [
            'user' => $user,
            'iat' => time(),
            // If this parameter is not set, JWT will be valid for life. This is not a good approach
            'exp' => time() + (60 * 60 * 24) // valid for day
        ];

        $token = JWT::encode(
            $jwt_payload,
            Config::JWT_SECRET(),
            'HS256'
        );

        return ['success' => true, 'data' => array_merge($user, ['token' => $token])];              
    }
}