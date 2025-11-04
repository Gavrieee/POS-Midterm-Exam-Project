<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Helpers\Auth;

class ApiController
{
    private $user;
    private $product;
    private $order;

    public function __construct()
    {
        $this->user = new User();
        $this->product = new Product();
        $this->order = new Order();
    }

    public function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (Auth::attempt($username, $password)) {

            try {
                $this->user->updateLastLogin(Auth::id());
            } catch (\Throwable $e) {

            }
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Invalid credentials'];
    }

    public function createAdmin()
    {
        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $fullName = $_POST['full_name'] ?? '';

        try {
            $id = $this->user->createAdminBySuperadmin($username, $password, $fullName, Auth::id());
            return ['success' => true, 'id' => $id];
        } catch (\PDOException $e) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Username already exists'];
        }
    }

    public function suspendAdmin()
    {
        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $userId = $_POST['user_id'] ?? 0;

        $suspend = filter_var($_POST['suspend'] ?? 'true', FILTER_VALIDATE_BOOLEAN);

        if ($this->user->suspendUser($userId, $suspend)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'User not found'];
    }

    public function addProduct()
    {
        if (!Auth::check()) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $name = $_POST['name'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $image = $_FILES['image'] ?? null;
        $imagePath = null;

        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                return ['success' => false, 'message' => 'Invalid image format'];
            }

            $imagePath = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($image['tmp_name'], __DIR__ . '/../../public/' . $imagePath);
        }

        try {
            $id = $this->product->addProduct($name, $price, $imagePath, Auth::id());
            return ['success' => true, 'id' => $id];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Error adding product'];
        }
    }

    public function createOrder()
    {
        if (!Auth::check()) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $items = json_decode($_POST['items'] ?? '[]', true);
        $notes = $_POST['notes'] ?? null;

        if (empty($items)) {
            return ['success' => false, 'message' => 'No items in order'];
        }

        try {
            $id = $this->order->create($items, Auth::id(), $notes);
            return ['success' => true, 'id' => $id];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Error creating order'];
        }
    }

    public function getOrderReport()
    {
        if (!Auth::check()) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-d 00:00:00');
        $endDate = $_GET['end_date'] ?? date('Y-m-d 23:59:59');

        try {
            $transactions = $this->order->transactionsBetween($startDate, $endDate);
            $total = array_reduce($transactions, function ($carry, $item) {
                return $carry + $item['total_amount'];
            }, 0);

            return [
                'success' => true,
                'transactions' => $transactions,
                'total' => $total
            ];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Error fetching report'];
        }
    }

    public function getProducts()
    {
        if (!Auth::check()) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        try {
            $products = $this->product->all();
            return ['success' => true, 'products' => $products];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Error fetching products'];
        }
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
        }
        return ['success' => true];
    }

    public function generatePdfReport()
    {
        if (!Auth::check()) {
            http_response_code(403);
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-d 00:00:00');
        $endDate = $_GET['end_date'] ?? date('Y-m-d 23:59:59');

        try {
            $transactions = $this->order->transactionsBetween($startDate, $endDate);
            $total = array_reduce($transactions, function ($carry, $item) {
                return $carry + $item['total_amount'];
            }, 0);

            return [
                'success' => true,
                'message' => 'PDF generation is temporarily disabled. Here is the data:',
                'transactions' => $transactions,
                'total' => $total
            ];
        } catch (\Exception $e) {
            http_response_code(400);
            return ['success' => false, 'message' => 'Error generating PDF'];
        }
    }
}