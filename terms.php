<?php
require_once __DIR__ . '/config/config.php';
session_start();

$page_title = 'Terms of Service - Rapid Indexer';
$meta_description = 'Read our Terms of Service regarding the use of Rapid Indexer services.';
$canonical_url = 'https://rapid-indexer.com/terms';

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 lg:px-8 py-12">
    <div class="card rounded-xl p-8 md:p-10">
        <h1 class="text-3xl font-bold text-white mb-4">Terms of Service</h1>
        <p class="text-gray-500 mb-8">Last updated: <?php echo date('F j, Y'); ?></p>
        
        <div class="space-y-8 text-gray-300">
            <section>
                <h4 class="text-xl font-bold text-white mb-3">1. Acceptance of Terms</h4>
                <p>By accessing or using Rapid Indexer ("the Service"), you agree to be bound by these Terms of Service. If you disagree with any part of the terms, you may not access the Service.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">2. Description of Service</h4>
                <p>Rapid Indexer provides tools for submitting URLs to search engines for indexing purposes. We do not guarantee that submitted URLs will be indexed by search engines, as this is ultimately at the discretion of the respective search engine providers (Google, Yandex, etc.).</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">3. User Accounts</h4>
                <p>To use most features of the Service, you must register for an account. You are responsible for maintaining the confidentiality of your account and password. You agree to accept responsibility for all activities that occur under your account.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">4. Payments and Credits</h4>
                <p>The Service operates on a credit-based system. Credits can be purchased via PayPal or Cryptocurrency (via Cryptomus). One credit allows for the processing of one URL (pricing may vary based on task type and VIP status). Credits are non-transferable.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">5. Prohibited Uses</h4>
                <p class="mb-2">You agree not to use the Service to submit:</p>
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li>Illegal content</li>
                    <li>Malware or phishing sites</li>
                    <li>Adult content or gambling sites</li>
                    <li>Content that violates third-party intellectual property rights</li>
                </ul>
                <p class="mt-2">We reserve the right to terminate accounts found violating these rules without refund.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">6. Limitation of Liability</h4>
                <p>In no event shall Rapid Indexer be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the Service.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">7. Modifications to Service</h4>
                <p>We reserve the right to modify or discontinue, temporarily or permanently, the Service with or without notice.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">8. Contact Us</h4>
                <p>If you have any questions about these Terms, please contact us at <a href="mailto:support@rapid-indexer.com" class="text-primary-400 hover:text-primary-300">support@rapid-indexer.com</a>.</p>
            </section>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
