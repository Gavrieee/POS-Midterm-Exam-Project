<?php
require_once __DIR__ . '/../src/bootstrap.php';
session_start();

use App\Helpers\Auth;
use App\Models\User;

Auth::require('superadmin');

$userModel = new User();
$admins = $userModel->getAllAdmins();
$pageTitle = 'POS System - Super Admin Dashboard';
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
        <h1>Super Admin Dashboard</h1>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Admin Users</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addAdminModal">
                            Create New Admin
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Date Added</th>
                                        <th>Last Login</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($admin['id']) ?></td>
                                        <td><?= htmlspecialchars($admin['username']) ?></td>
                                        <td><?= htmlspecialchars($admin['full_name']) ?></td>
                                        <td><?= htmlspecialchars($admin['date_added']) ?></td>
                                        <td><?= $admin['last_login'] ? htmlspecialchars($admin['last_login']) : 'Never' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $admin['suspended'] ? 'danger' : 'success' ?>">
                                                <?= $admin['suspended'] ? 'Suspended' : 'Active' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-sm btn-<?= $admin['suspended'] ? 'success' : 'danger' ?>"
                                                onclick="toggleSuspension(<?= $admin['id'] ?>, <?= $admin['suspended'] ? 'false' : 'true' ?>)">
                                                <?= $admin['suspended'] ? 'Activate' : 'Suspend' ?>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addAdminForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveAdmin">Create Admin</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const API = window.location.pathname.includes('/views/') ? '../public/api.php' : 'api.php';
    document.getElementById('saveAdmin').addEventListener('click', function() {
        const form = document.getElementById('addAdminForm');
        const formData = new FormData(form);

        fetch(`${API}?action=create_admin`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Admin created',
                            timer: 1200,
                            showConfirmButton: false
                        })
                        .then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error creating admin'
                    });
                }
            });
    });

    function toggleSuspension(userId, suspend) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('suspend', suspend);

        fetch(`${API}?action=suspend_admin`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Status updated',
                            timer: 1000,
                            showConfirmButton: false
                        })
                        .then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error updating user status'
                    });
                }
            });
    }
    </script>
</body>

</html>