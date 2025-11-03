<?php
use App\Helpers\Auth;
// Determine project base path dynamically from executing script directory
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptDir === '/' || $scriptDir === '\\') {
    $scriptDir = '';
}
$PROJECT_BASE = $scriptDir;
if (preg_match('#/(public|views|developer)$#', $PROJECT_BASE)) {
    $PROJECT_BASE = preg_replace('#/(public|views|developer)$#', '', $PROJECT_BASE);
}
if ($PROJECT_BASE === '') {
    $PROJECT_BASE = '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky top-0 z-50">
    <div class="container">
        <a class="navbar-brand" href="<?= $PROJECT_BASE ?>/public/index.php">POS System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (Auth::isSuperAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $PROJECT_BASE ?>/views/superadmin_home.php">Admin Management</a>
                </li>
                <?php endif; ?>
                <?php if (Auth::isAdmin() || Auth::isSuperAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $PROJECT_BASE ?>/views/admin_home.php">POS System</a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (Auth::check()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="logoutLink">Logout</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<script>
(function() {
    const link = document.getElementById('logoutLink');
    if (!link) return;
    const PROJECT_BASE = '<?= $PROJECT_BASE ?>';
    const API = `${PROJECT_BASE}/public/api.php`;
    link.addEventListener('click', function(e) {
        e.preventDefault();
        fetch(`${API}?action=logout`)
            .then(res => res.json())
            .then(() => {
                const target = `${PROJECT_BASE}/public/index.php`;
                if (window.Swal) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Logged out',
                            timer: 800,
                            showConfirmButton: false
                        })
                        .then(() => {
                            window.location.href = target;
                        });
                } else {
                    window.location.href = target;
                }
            })
            .catch(() => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Logout failed',
                        text: 'Please try again.'
                    });
                }
            });
    });
})();
</script>