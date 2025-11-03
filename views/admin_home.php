<?php
session_start();

require_once __DIR__ . '/../src/bootstrap.php';

use App\Helpers\Auth;
// Auth::require('admin');

$pageTitle = 'POS System - Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <div class="container mt-4">

        <!-- Products Section -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Products</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addProductModal">
                            Add Product
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="productsList" class="row g-3">
                            <!-- Products will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Section -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Current Order</h5>
                    </div>
                    <div class="card-body">
                        <div id="currentOrder">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderItems">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td colspan="2"><strong id="orderTotal">₱0.00</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-3">
                                <textarea id="orderNotes" class="form-control mb-2"
                                    placeholder="Order notes..."></textarea>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" id="amountTendered" class="form-control" step="0.01" min="0"
                                        placeholder="Amount tendered">
                                </div>
                                <button id="submitOrder" class="btn btn-success w-100">Complete Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Transaction Reports</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <input type="date" id="startDate" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <input type="date" id="endDate" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <button id="filterReport" class="btn btn-primary">Filter</button>
                                <button id="generatePdf" class="btn btn-secondary">Generate PDF</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="transactionsTable">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Served By</th>
                                        <th>Amount</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- reports will be loaded here -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td colspan="2"><strong id="reportTotal">₱0.00</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveProduct">Save Product</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const API = window.location.pathname.includes('/views/') ? '../public/api.php' : 'api.php';
    const IMG_BASE = window.location.pathname.includes('/views/') ? '../public/' : '';
    // Load products on page load
    loadProducts();
    loadTransactions();

    function loadProducts() {
        fetch(`${API}?action=get_products`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const productsHtml = data.products.map(p => `
    <div class="col-md-4">
            <div class="card h-100">
              ${p.image_path ? `<img src="${IMG_BASE}${p.image_path}" class="card-img-top" alt="${p.name}">` : ''}
              <div class="card-body">
                <h5 class="card-title">${p.name}</h5>
                <p class="card-text">₱${parseFloat(p.price).toFixed(2)}</p>
                
              </div>
              <div class="card-footer">
                <button class="btn btn-primary btn-sm w-100" onclick="addToOrder(${p.id}, '${p.name}', ${p.price})">Add to Order</button>
              </div>
            </div>
          </div>
`).join('');
                    document.getElementById('productsList').innerHTML = productsHtml;
                }
            });
    }

    function loadTransactions() {
        const startDate = document.getElementById('startDate').value || new Date().toISOString().split('T')[0];
        const endDate = document.getElementById('endDate').value || new Date().toISOString().split('T')[0];

        fetch(
                `${API}?action=get_order_report&start_date=${encodeURIComponent(startDate + ' 00:00:00')}&end_date=${encodeURIComponent(endDate + ' 23:59:59')}`
            )
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.querySelector('#transactionsTable tbody');
                    tbody.innerHTML = data.transactions.map(t => `
                            <tr>
                                <td>${t.id}</td>
                                <td>${new Date(t.date_added).toLocaleString()}</td>
                                <td>${t.served_by_name}</td>
                                <td>₱${parseFloat(t.total_amount).toFixed(2)}</td>
                                <td>${t.notes || ''}</td>
                            </tr>
                        `).join('');
                    document.getElementById('reportTotal').textContent = `₱${parseFloat(data.total).toFixed(2)}`;
                }
            });
    }

    // Current order management
    let currentOrder = [];

    function addToOrder(productId, name, price) {
        const existing = currentOrder.find(item => item.product_id === productId);
        if (existing) {
            existing.qty++;
            existing.subtotal = existing.qty * existing.price;
        } else {
            currentOrder.push({
                product_id: productId,
                name: name,
                price: price,
                qty: 1,
                subtotal: price
            });
        }
        updateOrderDisplay();
    }

    function removeFromOrder(index) {
        currentOrder.splice(index, 1);
        updateOrderDisplay();
    }

    function updateOrderDisplay() {
        const tbody = document.getElementById('orderItems');
        tbody.innerHTML = currentOrder.map((item, index) => `
                <tr>
                    <td>${item.name}</td>
                    <td>
                        <input type="number" min="1" value="${item.qty}" 
                               onchange="updateQuantity(${index}, this.value)" class="form-control form-control-sm" style="width: 70px">
                    </td>
                    <td>₱${item.price.toFixed(2)}</td>
                    <td>₱${item.subtotal.toFixed(2)}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="removeFromOrder(${index})">×</button>
                    </td>
                </tr>
            `).join('');

        const total = currentOrder.reduce((sum, item) => sum + item.subtotal, 0);
        document.getElementById('orderTotal').textContent = `₱${total.toFixed(2)}`;
    }

    function updateQuantity(index, qty) {
        qty = parseInt(qty);
        if (qty < 1) qty = 1;
        currentOrder[index].qty = qty;
        currentOrder[index].subtotal = qty * currentOrder[index].price;
        updateOrderDisplay();
    }

    // Event Listeners
    document.getElementById('saveProduct').addEventListener('click', function() {
        const form = document.getElementById('addProductForm');
        const formData = new FormData(form);

        fetch(`${API}?action=add_product`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    form.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
                    loadProducts();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error adding product'
                    });
                }
            });
    });

    document.getElementById('submitOrder').addEventListener('click', function() {
        if (currentOrder.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Empty order',
                text: 'Please add items to the order'
            });
            return;
        }

        const total = currentOrder.reduce((sum, item) => sum + item.subtotal, 0);
        const amount = parseFloat(document.getElementById('amountTendered').value || '0');
        if (!Number.isFinite(amount) || amount < total) {
            const msg = !Number.isFinite(amount) ?
                'Please enter a valid amount.' :
                `Insufficient funds. Total is ₱${total.toFixed(2)}.`;
            Swal.fire({
                icon: 'error',
                title: 'Insufficient funds',
                text: msg
            });
            return; // do NOT reset the order
        }

        const orderData = new FormData();
        orderData.append('items', JSON.stringify(currentOrder.map(item => ({
            product_id: item.product_id,
            qty: item.qty,
            price: item.price
        }))));
        orderData.append('notes', document.getElementById('orderNotes').value);

        fetch(`${API}?action=create_order`, {
                method: 'POST',
                body: orderData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentOrder = [];
                    updateOrderDisplay();
                    document.getElementById('orderNotes').value = '';
                    document.getElementById('amountTendered').value = '';
                    loadTransactions();
                    Swal.fire({
                        icon: 'success',
                        title: 'Order completed',
                        timer: 1200,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error creating order'
                    });
                }
            });
    });

    document.getElementById('filterReport').addEventListener('click', loadTransactions);

    document.getElementById('generatePdf').addEventListener('click', function() {
        const startDate = document.getElementById('startDate').value || new Date().toISOString().split('T')[0];
        const endDate = document.getElementById('endDate').value || new Date().toISOString().split('T')[0];
        window.location.href = `${API}?action=generate_pdf_report&start_date=${startDate}&end_date=${endDate}`;
    });
    </script>
</body>

</html>