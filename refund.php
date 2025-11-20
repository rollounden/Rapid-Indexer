<?php
require_once __DIR__ . '/config/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Policy - Rapid Indexer</title>
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
                        <h1 class="h2 mb-4">Refund Policy</h1>
                        <p class="text-muted mb-5">Last updated: <?php echo date('F j, Y'); ?></p>
                        
                        <h4>1. General Policy</h4>
                        <p>At Rapid Indexer, we strive to provide the best indexing service possible. However, due to the nature of digital goods and services, we generally do not offer refunds once credits have been purchased and used.</p>
                        
                        <h4>2. Eligibility for Refunds</h4>
                        <p>Refunds may be considered under the following circumstances:</p>
                        <ul>
                            <li><strong>Service Failure:</strong> If our system fails to process your request due to technical errors on our end (e.g., API failure that is not resolved).</li>
                            <li><strong>Duplicate Charge:</strong> If you were accidentally charged multiple times for the same transaction.</li>
                            <li><strong>Unused Credits:</strong> Requests for refunds of unused credits may be considered on a case-by-case basis within 7 days of purchase.</li>
                        </ul>
                        
                        <h4>3. Non-Refundable Items</h4>
                        <p>The following are strictly non-refundable:</p>
                        <ul>
                            <li>Credits that have already been used to submit URLs for indexing.</li>
                            <li>URLs that were submitted but not indexed by the search engine (as we cannot guarantee search engine behavior, only the submission to their API).</li>
                            <li>Funds added via Cryptocurrency (Cryptomus), due to the irreversible nature of blockchain transactions, unless there is a critical system failure on our part.</li>
                        </ul>
                        
                        <h4>4. How to Request a Refund</h4>
                        <p>To request a refund, please contact our support team at support@rapid-indexer.com with your transaction ID and the reason for your request. We will review your case and respond within 2-3 business days.</p>
                        
                        <h4>5. Processing of Refunds</h4>
                        <p>Approved refunds will be processed back to the original payment method. Please allow 5-10 business days for the refund to appear in your account statement.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

