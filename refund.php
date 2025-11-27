<?php
require_once __DIR__ . '/config/config.php';
session_start();

$page_title = 'Refund Policy - Rapid Indexer';
$meta_description = 'Understand our refund policy for credit purchases and subscription services.';
$canonical_url = 'https://rapid-indexer.com/refund.php';

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 lg:px-8 py-12">
    <div class="card rounded-xl p-8 md:p-10">
        <h1 class="text-3xl font-bold text-white mb-4">Refund Policy</h1>
        <p class="text-gray-500 mb-8">Last updated: <?php echo date('F j, Y'); ?></p>
        
        <div class="space-y-8 text-gray-300">
            <section>
                <h4 class="text-xl font-bold text-white mb-3">1. General Policy</h4>
                <p>At Rapid Indexer, we strive to provide the best indexing service possible. However, due to the nature of digital goods and services, we generally do not offer refunds once credits have been purchased and used.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">2. Eligibility for Refunds</h4>
                <p class="mb-2">Refunds may be considered under the following circumstances:</p>
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong class="text-white">Service Failure:</strong> If our system fails to process your request due to technical errors on our end (e.g., API failure that is not resolved).</li>
                    <li><strong class="text-white">Duplicate Charge:</strong> If you were accidentally charged multiple times for the same transaction.</li>
                    <li><strong class="text-white">Unused Credits:</strong> Requests for refunds of unused credits may be considered on a case-by-case basis within 7 days of purchase.</li>
                </ul>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">3. Non-Refundable Items</h4>
                <p class="mb-2">The following are strictly non-refundable:</p>
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li>Credits that have already been used to submit URLs for indexing.</li>
                    <li>URLs that were submitted but not indexed by the search engine (as we cannot guarantee search engine behavior, only the submission to their API).</li>
                    <li>Funds added via Cryptocurrency (Cryptomus), due to the irreversible nature of blockchain transactions, unless there is a critical system failure on our part.</li>
                </ul>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">4. How to Request a Refund</h4>
                <p>To request a refund, please contact our support team at <a href="mailto:support@rapid-indexer.com" class="text-primary-400 hover:text-primary-300">support@rapid-indexer.com</a> with your transaction ID and the reason for your request. We will review your case and respond within 2-3 business days.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">5. Processing of Refunds</h4>
                <p>Approved refunds will be processed back to the original payment method. Please allow 5-10 business days for the refund to appear in your account statement.</p>
            </section>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
