<?php
$bootstrap = __DIR__ . '/../src/bootstrap.php';
require_once $bootstrap;

session_start();

use App\Controllers\ApiController;

header('Content-Type: application/json');

$controller = new ApiController();
$action = $_GET['action'] ?? '';

try {
    $result = match ($action) {
        'login' => $controller->login(),
        'logout' => $controller->logout(),
        'create_admin' => $controller->createAdmin(),
        'suspend_admin' => $controller->suspendAdmin(),
        'add_product' => $controller->addProduct(),
        'get_products' => $controller->getProducts(),
        'create_order' => $controller->createOrder(),
        'get_order_report' => $controller->getOrderReport(),
        'generate_pdf_report' => $controller->generatePdfReport(),
        default => ['success' => false, 'message' => 'Invalid action']
    };

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
