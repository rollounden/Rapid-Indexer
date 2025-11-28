<?php
require_once __DIR__ . '/config/config.php';
session_start();

$page_title = 'Privacy Policy - Rapid Indexer';
$meta_description = 'Learn how Rapid Indexer collects, uses, and protects your personal information.';
$canonical_url = 'https://rapid-indexer.com/privacy';

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-4xl mx-auto px-6 lg:px-8 py-12">
    <div class="card rounded-xl p-8 md:p-10">
        <h1 class="text-3xl font-bold text-white mb-4">Privacy Policy</h1>
        <p class="text-gray-500 mb-8">Last updated: <?php echo date('F j, Y'); ?></p>
        
        <div class="space-y-8 text-gray-300">
            <section>
                <h4 class="text-xl font-bold text-white mb-3">1. Information We Collect</h4>
                <p class="mb-2">We collect information you provide directly to us, including:</p>
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li>Account information (email address, password)</li>
                    <li>Payment information (transaction history, payment method details processed by our providers)</li>
                    <li>Usage data (URLs submitted for indexing, task history)</li>
                </ul>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">2. How We Use Your Information</h4>
                <p class="mb-2">We use the information we collect to:</p>
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li>Provide, maintain, and improve our services</li>
                    <li>Process your transactions and send related information</li>
                    <li>Send you technical notices, updates, security alerts, and support messages</li>
                    <li>Detect and prevent fraud, abuse, and illegal activities</li>
                </ul>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">3. Payment Processing</h4>
                <p>We use third-party payment processors (PayPal and Cryptomus) to handle payments. We do not store your full credit card details or crypto wallet private keys. Payment processors adhere to the standards set by PCI-DSS as managed by the PCI Security Standards Council.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">4. Data Sharing</h4>
                <p>We do not sell or rent your personal information to third parties. We may share your information with third-party service providers (such as search engine APIs like Google or Yandex) only to the extent necessary to perform the requested services (indexing your URLs).</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">5. Data Security</h4>
                <p>We take reasonable measures to help protect information about you from loss, theft, misuse and unauthorized access, disclosure, alteration and destruction.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">6. Your Rights</h4>
                <p>You have the right to access, correct, or delete your personal information. You can manage your account settings within the dashboard or contact us to request deletion of your account.</p>
            </section>
            
            <section>
                <h4 class="text-xl font-bold text-white mb-3">7. Contact Us</h4>
                <p>If you have any questions about this Privacy Policy, please contact us at <a href="mailto:privacy@rapid-indexer.com" class="text-primary-400 hover:text-primary-300">privacy@rapid-indexer.com</a>.</p>
            </section>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
