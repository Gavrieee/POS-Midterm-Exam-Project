<?php
namespace App\Helpers;
use App\Models\User;

class Auth
{
    private static $user = null;

    public static function attempt($username, $password)
    {
        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user) {
            return false;
        }

        if ($user['suspended']) {
            return false;
        }

        if (password_verify($password, $user['password_hash'])) {
            self::$user = $user;
            $_SESSION['user_id'] = $user['id'];
            return true;
        }

        return false;
    }

    public static function user()
    {
        if (self::$user === null && isset($_SESSION['user_id'])) {
            $userModel = new User();
            self::$user = $userModel->findById($_SESSION['user_id']);
        }
        return self::$user;
    }

    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    public static function isSuperAdmin()
    {
        $user = self::user();
        if (!$user || !is_array($user)) {
            return false;
        }
        return ($user['role'] ?? null) === 'superadmin';
    }

    public static function isAdmin()
    {
        $user = self::user();
        if (!$user || !is_array($user)) {
            return false;
        }
        return ($user['role'] ?? null) === 'admin';
    }

    public static function id()
    {
        $user = self::user();
        if (!$user || !is_array($user)) {
            return null;
        }
        return isset($user['id']) ? (int) $user['id'] : null;
    }

    public static function logout()
    {
        self::$user = null;
        unset($_SESSION['user_id']);
        session_destroy();
    }

    public static function require($role = null)
    {
        if (!self::check()) {
            header('Location: /');
            exit;
        }

        if ($role === 'superadmin' && !self::isSuperAdmin()) {
            header('Location: /');
            exit;
        }

        if ($role === 'admin' && !self::isAdmin()) {
            header('Location: /');
            exit;
        }
    }
}