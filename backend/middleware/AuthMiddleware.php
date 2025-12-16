<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {

    public function verifyToken($token){
        if(!$token)
            Flight::halt(401, "Missing authentication header");

        $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));

        Flight::set('user', $decoded_token->user);
        Flight::set('jwt_token', $token);
        return TRUE;
    }

    /**
     * Extract Bearer token from Authorization header in the request.
     * Returns the raw token string or null if not present.
     */
    public function getTokenFromHeader() {
        $headers = null;
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback for non-Apache environments
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$key] = $value;
                }
            }
        }

        $auth = null;
        if (isset($headers['Authorization'])) $auth = $headers['Authorization'];
        elseif (isset($headers['authorization'])) $auth = $headers['authorization'];

        if (!$auth) return null;
        if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) return $matches[1];
        return null;
    }

    public function authorizeRole($requiredRole) {
        $user = Flight::get('user');
        if ($user->role !== $requiredRole) {
            Flight::halt(403, 'Access denied: insufficient privileges');
        }
    }

    public function authorizeRoles($roles) {
        $user = Flight::get('user');
        if (!in_array($user->role, $roles)) {
            Flight::halt(403, 'Forbidden: role not allowed');
        }
    }

    function authorizePermission($permission) {
        $user = Flight::get('user');
        if (!in_array($permission, $user->permissions)) {
            Flight::halt(403, 'Access denied: permission missing');
        }
    }    
}