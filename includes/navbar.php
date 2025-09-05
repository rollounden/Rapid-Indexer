<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/dashboard.php">
            <i class="fas fa-rocket me-2"></i>Rapid Indexer
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" 
                       href="/dashboard.php">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'tasks.php' ? 'active' : ''; ?>" 
                       href="/tasks.php">
                        <i class="fas fa-tasks me-1"></i>Tasks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>" 
                       href="/payments.php">
                        <i class="fas fa-credit-card me-1"></i>Payments
                    </a>
                </li>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin.php' ? 'active' : ''; ?>" 
                       href="/admin.php">
                        <i class="fas fa-cog me-1"></i>Admin
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span class="d-none d-md-inline">
                            <?php 
                            // Refresh user data from database if email is not in session
                            if (!isset($_SESSION['email']) && isset($_SESSION['uid'])) {
                                try {
                                    require_once __DIR__ . '/../src/Db.php';
                                    $pdo = Db::conn();
                                    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
                                    $stmt->execute([$_SESSION['uid']]);
                                    $user = $stmt->fetch();
                                    if ($user) {
                                        $_SESSION['email'] = $user['email'];
                                    }
                                } catch (Exception $e) {
                                    // Fallback to default
                                }
                            }
                            echo htmlspecialchars($_SESSION['email'] ?? 'User'); 
                            ?>
                        </span>
                        <span class="d-md-none">Account</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><h6 class="dropdown-header d-md-none"><?php echo htmlspecialchars($_SESSION['email'] ?? 'User'); ?></h6></li>
                        <li><a class="dropdown-item" href="/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
