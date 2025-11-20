<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Redirect logged-in users to dashboard
if (isset($_SESSION['uid'])) {
    header('Location: /dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Rapid Indexer - Fast Google Indexing Service</title>
    <meta name="description" content="Get your website pages indexed by Google quickly with Rapid Indexer. Professional SEO indexing service for faster search engine visibility.">
    <meta name="theme-color" content="#2563eb">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/">
                <i class="fas fa-rocket text-primary"></i>
                <span>Rapid Indexer</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link" href="/login">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary px-4" href="/register">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section pt-5 pb-5 mt-5 bg-white">
        <div class="container pt-5">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <div class="badge bg-primary-subtle text-primary mb-3 px-3 py-2 rounded-pill">
                        <i class="fas fa-bolt me-2"></i>Now with 70% Faster Indexing
                    </div>
                    <h1 class="display-4 fw-bold mb-4 text-slate-900">
                        Force Google to Index Your Links <span class="text-primary">Instantly</span>
                    </h1>
                    <p class="lead text-muted mb-5">
                        Stop waiting weeks for search engines to crawl your site. Our premium indexing API ensures your backlinks and pages get discovered within hours.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3">
                        <a href="/register" class="btn btn-primary btn-lg px-5">
                            Start Indexing Free
                        </a>
                        <a href="#how-it-works" class="btn btn-outline-secondary btn-lg px-5">
                            How it Works
                        </a>
                    </div>
                    <div class="mt-4 pt-3 border-top d-flex gap-4 text-muted small">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success"></i> No Monthly Fees
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success"></i> 100% Safe
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success"></i> Detailed Reports
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="absolute top-0 start-0 w-100 h-100 bg-primary opacity-10 rounded-4" style="transform: rotate(-3deg);"></div>
                        <img src="https://placehold.co/600x400/f8fafc/cbd5e1?text=Dashboard+Preview" alt="Dashboard Preview" class="img-fluid rounded-4 shadow-lg position-relative bg-white border">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary display-5">1.2M+</h2>
                    <p class="text-muted mb-0">Pages Indexed</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary display-5">98%</h2>
                    <p class="text-muted mb-0">Success Rate</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary display-5">2h</h2>
                    <p class="text-muted mb-0">Avg. Index Time</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-white">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-6 mb-3">Why Choose Rapid Indexer?</h2>
                <p class="lead text-muted mx-auto" style="max-width: 600px;">
                    We use direct API integrations with Google and other search engines to guarantee your content gets seen.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex p-3 mb-4">
                                <i class="fas fa-bolt fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Lightning Speed</h4>
                            <p class="text-muted">
                                Forget waiting weeks. Our system pings search engine bots directly, often resulting in indexing within minutes to hours.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="bg-success-subtle text-success rounded-3 d-inline-flex p-3 mb-4">
                                <i class="fas fa-check-double fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Indexing & Checking</h4>
                            <p class="text-muted">
                                Not just submission. We verify if your links are actually indexed and provide detailed reports on their status.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <div class="bg-warning-subtle text-warning rounded-3 d-inline-flex p-3 mb-4">
                                <i class="fas fa-shield-alt fa-2x"></i>
                            </div>
                            <h4 class="fw-bold mb-3">100% White Hat</h4>
                            <p class="text-muted">
                                We use official indexing APIs provided by search engines. No spammy tactics that could get your site penalized.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5 bg-light">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold display-6 mb-3">Simple, Transparent Pricing</h2>
                <p class="lead text-muted">Pay as you go. Credits never expire.</p>
            </div>
            
            <div class="row justify-content-center g-4">
                <!-- Checking Plan -->
                <div class="col-md-5 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-5">
                            <div class="text-center">
                                <h5 class="fw-bold text-muted mb-4">LINK CHECKING</h5>
                                <div class="display-4 fw-bold mb-2">$0.05</div>
                                <p class="text-muted mb-4">per URL check</p>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Check Google Index Status</li>
                                <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Real-time Verification</li>
                                <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Bulk Processing</li>
                                <li class="mb-3"><i class="fas fa-check text-primary me-2"></i> Export Reports</li>
                            </ul>
                            <div class="d-grid">
                                <a href="/register" class="btn btn-outline-primary btn-lg">Get Started</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Indexing Plan -->
                <div class="col-md-5 col-lg-4">
                    <div class="card h-100 border-primary shadow position-relative">
                        <div class="position-absolute top-0 start-50 translate-middle">
                            <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm">RECOMMENDED</span>
                        </div>
                        <div class="card-body p-5">
                            <div class="text-center">
                                <h5 class="fw-bold text-primary mb-4">PREMIUM INDEXING</h5>
                                <div class="display-4 fw-bold mb-2">$0.15</div>
                                <p class="text-muted mb-4">per URL submission</p>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Force Google Crawl</li>
                                <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Includes Index Check</li>
                                <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Priority Processing</li>
                                <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> 98% Index Rate</li>
                                <li class="mb-3"><i class="fas fa-star text-warning me-2"></i> VIP Queue Available (+$0.05)</li>
                            </ul>
                            <div class="d-grid">
                                <a href="/register" class="btn btn-primary btn-lg">Start Indexing</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <p class="text-muted">
                    <i class="fab fa-cc-paypal fa-lg me-2"></i>
                    <i class="fab fa-bitcoin fa-lg me-2"></i>
                    <i class="fab fa-ethereum fa-lg"></i>
                    <span class="ms-2">Secure payments via PayPal & Crypto</span>
                </p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-5 bg-primary text-white">
        <div class="container py-5 text-center">
            <h2 class="display-5 fw-bold mb-4">Ready to Boost Your Rankings?</h2>
            <p class="lead mb-5 opacity-75" style="max-width: 600px; margin: 0 auto;">
                Join thousands of SEO professionals who trust Rapid Indexer to get their content found faster.
            </p>
            <a href="/register" class="btn btn-light btn-lg px-5 fw-bold">Create Free Account</a>
        </div>
    </section>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
