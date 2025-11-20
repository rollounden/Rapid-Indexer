<?php
require_once __DIR__ . '/config/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-5">
                        <h1 class="h2 mb-4">Terms of Service</h1>
                        <p class="text-muted mb-5">Last updated: <?php echo date('F j, Y'); ?></p>
                        
                        <h4>1. Acceptance of Terms</h4>
                        <p>By accessing or using Rapid Indexer ("the Service"), you agree to be bound by these Terms of Service. If you disagree with any part of the terms, you may not access the Service.</p>
                        
                        <h4>2. Description of Service</h4>
                        <p>Rapid Indexer provides tools for submitting URLs to search engines for indexing purposes. We do not guarantee that submitted URLs will be indexed by search engines, as this is ultimately at the discretion of the respective search engine providers (Google, Yandex, etc.).</p>
                        
                        <h4>3. User Accounts</h4>
                        <p>To use most features of the Service, you must register for an account. You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account.</p>
                        
                        <h4>4. Payments and Credits</h4>
                        <p>The Service operates on a credit-based system. Credits can be purchased via PayPal or Cryptocurrency (via Cryptomus). One credit allows for the processing of one URL (pricing may vary based on task type and VIP status). Credits are non-transferable.</p>
                        
                        <h4>5. Prohibited Uses</h4>
                        <p>You agree not to use the Service to submit:</p>
                        <ul>
                            <li>Illegal content</li>
                            <li>Malware or phishing sites</li>
                            <li>Adult content or gambling sites</li>
                            <li>Content that violates third-party intellectual property rights</li>
                        </ul>
                        <p>We reserve the right to terminate accounts found violating these rules without refund.</p>
                        
                        <h4>6. Limitation of Liability</h4>
                        <p>In no event shall Rapid Indexer be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the Service.</p>
                        
                        <h4>7. Modifications to Service</h4>
                        <p>We reserve the right to modify or discontinue, temporarily or permanently, the Service with or without notice.</p>
                        
                        <h4>8. Contact Us</h4>
                        <p>If you have any questions about these Terms, please contact us at support@rapid-indexer.com.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

