<?php
// Developer utility page: create superadmin and admin accounts for testing
// NOTE: Keep this page out of production environments.

require_once __DIR__ . '/../src/bootstrap.php';
session_start();

use App\Models\User;

$pageTitle = 'Developer - Account Creation';
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $createdBy = (int) ($_POST['created_by'] ?? 0);

    try {
        $userModel = new User();
        if ($role === 'superadmin') {
            $id = $userModel->createSuperAdmin($username, $password, $fullName);
            $flash = ['type' => 'success', 'message' => "Superadmin created (ID: {$id})"];
        } elseif ($role === 'admin') {
            // For showcase, allow creating admin without being logged-in superadmin
            $id = $userModel->createAdminBySuperadmin($username, $password, $fullName, $createdBy ?: 0);
            $flash = ['type' => 'success', 'message' => "Admin created (ID: {$id})"];
        } else {
            $flash = ['type' => 'error', 'message' => 'Invalid role'];
        }
    } catch (\PDOException $e) {
        $msg = strpos($e->getMessage(), 'Duplicate') !== false ? 'Username already exists' : 'Database error';
        $flash = ['type' => 'error', 'message' => $msg];
    } catch (\Throwable $e) {
        $flash = ['type' => 'error', 'message' => 'Unexpected error'];
    }
}
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

<body class="bg-light">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12 d-flex align-items-center justify-content-between">
                <h1 class="h3 mb-0">Developer: Account Creation</h1>
                <a href="../public/index.php" class="btn btn-outline-secondary btn-sm">Back to Login</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <strong>Create Superadmin</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" autocomplete="off">
                            <input type="hidden" name="role" value="superadmin">
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
                            <button type="submit" class="btn btn-primary">Create Superadmin</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <strong>Create Admin</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" autocomplete="off">
                            <input type="hidden" name="role" value="admin">
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
                            <div class="mb-3">
                                <label class="form-label">Created By (User ID, optional)</label>
                                <input type="number" class="form-control" name="created_by" placeholder="0">
                            </div>
                            <button type="submit" class="btn btn-primary">Create Admin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($flash): ?>
    <script>
    window.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $flash['type'] === 'success' ? 'success' : 'error' ?>',
            title: '<?= $flash['type'] === 'success' ? 'Success' : 'Error' ?>',
            text: '<?= htmlspecialchars($flash['message'], ENT_QUOTES) ?>',
            timer: <?= $flash['type'] === 'success' ? 1400 : 'null' ?>,
            showConfirmButton: <?= $flash['type'] === 'success' ? 'false' : 'true' ?>
        });
    });
    </script>
    <?php endif; ?>
</body>

</html>