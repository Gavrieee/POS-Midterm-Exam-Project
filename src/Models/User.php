<?php
namespace App\Models;
use App\Database;


class User
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    public function createSuperAdmin($username, $password, $fullName)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username,password_hash,full_name,role) VALUES (?, ?, ?, 'superadmin')");
        $stmt->execute([$username, $hash, $fullName]);
        return $this->db->lastInsertId();
    }


    public function createAdminBySuperadmin($username, $password, $fullName, $createdById)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username,password_hash,full_name,role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$username, $hash, $fullName]);
        // optionally log who created the account in audit_logs ????????
        return $this->db->lastInsertId();
    }


    public function suspendUser($userId, $suspend = true)
    {
        $stmt = $this->db->prepare("UPDATE users SET suspended = ? WHERE id = ?");
        return $stmt->execute([$suspend ? 1 : 0, $userId]);
    }


    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllAdmins()
    {
        return $this->db->query("SELECT id, username, full_name, suspended, date_added, last_login FROM users WHERE role = 'admin' ORDER BY date_added DESC")->fetchAll();
    }

    public function updateLastLogin($userId)
    {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }
}