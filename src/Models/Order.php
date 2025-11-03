<?php
namespace App\Models;
use App\Database;


class Order
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    public function create($items, $servedBy, $notes = '')
    {
        $this->db->beginTransaction();
        $total = 0;
        foreach ($items as $it) {
            $total += $it['qty'] * $it['price'];
        }
        $stmt = $this->db->prepare("INSERT INTO orders (total_amount, served_by, notes) VALUES (?, ?, ?)");
        $stmt->execute([$total, $servedBy, $notes]);
        $orderId = $this->db->lastInsertId();
        $stmtItem = $this->db->prepare("INSERT INTO order_items (order_id,product_id,qty,price_at_sale,subtotal) VALUES (?,?,?,?,?)");
        foreach ($items as $it) {
            $subtotal = $it['qty'] * $it['price'];
            $stmtItem->execute([$orderId, $it['product_id'], $it['qty'], $it['price'], $subtotal]);
        }
        $this->db->commit();
        return $orderId;
    }


    public function transactionsBetween($startDate, $endDate)
    {
        $stmt = $this->db->prepare("SELECT o.*, u.full_name as served_by_name FROM orders o LEFT JOIN users u ON o.served_by=u.id WHERE o.date_added BETWEEN ? AND ? ORDER BY o.date_added DESC");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}