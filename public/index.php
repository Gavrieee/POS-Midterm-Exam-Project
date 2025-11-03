<?php
require_once __DIR__ . '/../src/bootstrap.php';
session_start();

use App\Helpers\Auth;

// Redirect if already logged in
if (Auth::check()) {
    header('Location: ../views/' . (Auth::isSuperAdmin() ? 'superadmin_home.php' : 'admin_home.php'));
    exit;
}

$pageTitle = 'Login - POS System';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    html,
    body {
        height: 100%;
    }
    </style>
</head>

<body class="d-flex align-items-center bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">POS System Login</h3>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div id="loginError" class="alert alert-danger mt-3" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('api.php?action=login', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Logged in',
                        timer: 1000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    const err = document.getElementById('loginError');
                    err.textContent = data.message || 'Invalid credentials. Please try again.';
                    err.style.display = '';
                    Swal.fire({
                        icon: 'error',
                        title: 'Login failed',
                        text: err.textContent
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Network error',
                    text: 'Please try again.'
                });
            });
    });
    </script>
</body>

</html>