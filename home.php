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
    <title>RapidIndexer - Fast Google Indexing Service</title>
    <meta name="description" content="Get your website pages indexed by Google quickly with RapidIndexer. Professional SEO indexing service for faster search engine visibility.">
    <meta name="theme-color" content="#667eea">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .display-4 {
                font-size: 2.5rem !important;
            }
            .display-5 {
                font-size: 2rem !important;
            }
            .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
            .py-5 {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }
            .hero-section h1 {
                font-size: 2rem !important;
                line-height: 1.2;
            }
            .hero-section .lead {
                font-size: 1.1rem;
            }
            .feature-icon {
                font-size: 2.5rem !important;
            }
            .pricing-card {
                margin-bottom: 1rem;
            }
            .navbar-brand {
                font-size: 1.1rem;
            }
            .btn-group-mobile {
                flex-direction: column;
                gap: 0.5rem;
            }
            .btn-group-mobile .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .display-4 {
                font-size: 2rem !important;
            }
            .hero-section h1 {
                font-size: 1.75rem !important;
            }
            .btn-lg {
                padding: 0.6rem 1.2rem;
                font-size: 0.95rem;
            }
        }
        
        /* Touch-friendly buttons */
        .btn {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Prevent zoom on input focus */
        input, textarea, select {
            font-size: 16px;
        }
        
        /* Mobile Navigation Enhancements */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: #fff;
                border-top: 1px solid #dee2e6;
                margin-top: 0.5rem;
                padding-top: 1rem;
            }
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                border-radius: 0.375rem;
                margin: 0.25rem 0;
            }
            .navbar-nav .nav-link:hover {
                background-color: #f8f9fa;
            }
            .navbar-nav .btn {
                margin-left: 0 !important;
                margin-top: 0.5rem;
            }
        }
        
        /* Touch-friendly navbar toggler */
        .navbar-toggler {
            padding: 0.5rem;
            border: none;
            background: transparent;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        /* Brand icon animation */
        .navbar-brand i {
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover i {
            transform: rotate(15deg);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="/">
                <i class="fas fa-rocket me-2"></i>RapidIndexer
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="/login">Login</a>
                    <a class="btn btn-primary ms-2" href="/register">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Get Your Pages Indexed by Google Fast</h1>
                    <p class="lead mb-4">Stop waiting weeks for Google to discover your new content. RapidIndexer gets your pages indexed in hours, not days.</p>
                    <div class="d-flex gap-3 btn-group-mobile">
                        <a href="/register" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>Start Indexing Now
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-search-plus" style="font-size: 200px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Why Choose RapidIndexer?</h2>
                    <p class="lead text-muted">Professional SEO indexing service designed for speed and reliability</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 p-4 text-center">
                        <i class="fas fa-bolt text-primary mb-3 feature-icon" style="font-size: 3rem;"></i>
                        <h4>Lightning Fast</h4>
                        <p class="text-muted">Get your pages indexed in hours instead of waiting weeks for Google to discover them naturally.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4 text-center">
                        <i class="fas fa-shield-alt text-primary mb-3 feature-icon" style="font-size: 3rem;"></i>
                        <h4>Safe & Reliable</h4>
                        <p class="text-muted">Our indexing methods are Google-compliant and won't harm your website's SEO performance.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 p-4 text-center">
                        <i class="fas fa-chart-line text-primary mb-3 feature-icon" style="font-size: 3rem;"></i>
                        <h4>Track Progress</h4>
                        <p class="text-muted">Monitor your indexing progress with detailed reports and real-time status updates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5 fw-bold mb-3">Simple Pricing</h2>
                    <p class="lead text-muted">Pay only for what you use. No monthly fees, no hidden costs.</p>
                </div>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 p-4 pricing-card">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Standard</h3>
                            <div class="display-4 fw-bold text-primary">$0.05</div>
                            <p class="text-muted">per URL indexed</p>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Fast indexing</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Progress tracking</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Detailed reports</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Email support</li>
                        </ul>
                        <a href="/register" class="btn btn-outline-primary w-100">Get Started</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 p-4 pricing-card border-primary">
                        <div class="text-center mb-4">
                            <div class="badge bg-primary mb-2">Most Popular</div>
                            <h3 class="fw-bold">VIP Queue</h3>
                            <div class="display-4 fw-bold text-primary">$0.10</div>
                            <p class="text-muted">per URL indexed</p>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Priority processing</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Faster indexing</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>All standard features</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Priority support</li>
                        </ul>
                        <a href="/register" class="btn btn-primary w-100">Get Started</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="display-5 fw-bold mb-3">Ready to Get Indexed?</h2>
            <p class="lead mb-4">Join thousands of websites that trust RapidIndexer for fast Google indexing.</p>
            <a href="/register" class="btn btn-light btn-lg">
                <i class="fas fa-rocket me-2"></i>Start Indexing Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-rocket me-2"></i>RapidIndexer</h5>
                    <p class="text-muted">Professional Google indexing service for faster SEO results.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2025 RapidIndexer. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
