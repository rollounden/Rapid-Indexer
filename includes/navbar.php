<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/dashboard.php">Rapid Indexer</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" 
                       href="/dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'tasks.php' ? 'active' : ''; ?>" 
                       href="/tasks.php">Tasks</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>" 
                       href="/payments.php">Payments</a>
                </li>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin.php' ? 'active' : ''; ?>" 
                       href="/admin.php">Admin</a>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
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
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
