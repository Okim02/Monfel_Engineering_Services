<?php
class Auth {
    public static function login($email, $password) {
        $query = db()->prepare("SELECT * FROM users WHERE email = ? AND active = 1 AND role = 'owner' LIMIT 1");
        $query->execute(array($email));
        $user = $query->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = array(
            'id' => (int) $user['id'],
            'employee_id' => $user['employee_id'] ? (int) $user['employee_id'] : null,
            'full_name' => $user['full_name'],
            'role' => $user['role']
        );
        events()->dispatch('auth.login', array('user' => $_SESSION['user']));
        return true;
    }

    public static function registerOwner($fullName, $email, $password) {
        // CRUD: Create an owner login account during registration.
        $query = db()->prepare("INSERT INTO users (full_name, email, password_hash, role, active) VALUES (?, ?, ?, 'owner', 1)");
        $query->execute(array($fullName, $email, password_hash($password, PASSWORD_DEFAULT)));
        return (int) db()->lastInsertId();
    }

    public static function logout() {
        $user = self::user();
        events()->dispatch('auth.logout', array('user' => $user));
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function user() {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    public static function requireLogin() {
        if (!self::user()) {
            redirect('signin.php');
        }
    }

    public static function requireRole($roles) {
        self::requireLogin();
        $user = self::user();
        $query = db()->prepare('SELECT role, active FROM users WHERE id = ? LIMIT 1');
        $query->execute(array($user['id']));
        $account = $query->fetch();
        if (!$account || !(int) $account['active']) {
            self::logout();
            http_response_code(401);
            exit('Your account is inactive.');
        }
        $_SESSION['user']['role'] = $account['role'];
        if (!in_array($account['role'], $roles, true)) {
            http_response_code(403);
            exit('You are not authorized to perform this action.');
        }
    }
}
