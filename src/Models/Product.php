<?php
namespace App\Models;
use App\Database;


class Product
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    public function addProduct($name, $price, $imagePath, $addedBy)
    {
        $stmt = $this->db->prepare("INSERT INTO products (name,price,image_path,added_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $imagePath, $addedBy]);
        return $this->db->lastInsertId();
    }


    public function all()
    {
        return $this->db->query("SELECT p.*, u.full_name as added_by_name FROM products p LEFT JOIN users u ON p.added_by=u.id ORDER BY p.date_added DESC")->fetchAll();
    }
}