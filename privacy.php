<?php
require_once __DIR__ . '/config/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Rapid Indexer</title>
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
                        <h1 class="h2 mb-4">Privacy Policy</h1>
                        <p class="text-muted mb-5">Last updated: <?php echo date('F j, Y'); ?></p>
                        
                        <h4>1. Information We Collect</h4>
                        <p>We collect information you provide directly to us, including:</p>
                        <ul>
                            <li>Account information (email address, password)</li>
                            <li>Payment information (transaction history, payment method details processed by our providers)</li>
                            <li>Usage data (URLs submitted for indexing, task history)</li>
                        </ul>
                        
                        <h4>2. How We Use Your Information</h4>
                        <p>We use the information we collect to:</p>
                        <ul>
                            <li>Provide, maintain, and improve our services</li>
                            <li>Process your transactions and send related information</li>
                            <li>Send you technical notices, updates, security alerts, and support messages</li>
                            <li>Detect and prevent fraud, abuse, and illegal activities</li>
                        </ul>
                        
                        <h4>3. Payment Processing</h4>
                        <p>We use third-party payment processors (PayPal and Cryptomus) to handle payments. We do not store your full credit card details or crypto wallet private keys. Payment processors adhere to the standards set by PCI-DSS as managed by the PCI Security Standards Council.</p>
                        
                        <h4>4. Data Sharing</h4>
                        <p>We do not sell or rent your personal information to third parties. We may share your information with third-party service providers (such as search engine APIs like Google or Yandex) only to the extent necessary to perform the requested services (indexing your URLs).</p>
                        
                        <h4>5. Data Security</h4>
                        <p>We take reasonable measures to help protect information about you from loss, theft, misuse and unauthorized access, disclosure, alteration and destruction.</p>
                        
                        <h4>6. Your Rights</h4>
                        <p>You have the right to access, correct, or delete your personal information. You can manage your account settings within the dashboard or contact us to request deletion of your account.</p>
                        
                        <h4>7. Contact Us</h4>
                        <p>If you have any questions about this Privacy Policy, please contact us at privacy@rapid-indexer.com.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

