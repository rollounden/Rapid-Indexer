<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="fw-bold display-5 mb-3">Frequently Asked Questions</h1>
            <p class="lead text-muted">Find answers to common questions about Rapid Indexer.</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <!-- Group 1: General -->
                    <div class="mb-4">
                        <h4 class="mb-3 text-primary">General</h4>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What is Rapid Indexer?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Rapid Indexer is a tool that helps SEO professionals and website owners get their URLs indexed by search engines like Google faster. We use approved methods to notify search engines about your new or updated content.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How long does indexing take?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    While we submit your URLs immediately, Google typically crawls them within 24-48 hours. Actual indexing depends on Google's algorithms and the quality of your content, but our users often see results in as little as a few hours.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Group 2: Pricing & Credits -->
                    <div class="mb-4">
                        <h4 class="mb-3 text-primary">Pricing & Credits</h4>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    How much does it cost?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We use a credit-based system.
                                    <ul>
                                        <li><strong>Indexing:</strong> 3 credits ($0.03) per URL</li>
                                        <li><strong>Checking:</strong> 1 credit ($0.01) per URL</li>
                                    </ul>
                                    Credits can be purchased via PayPal or Cryptocurrency starting at $1.00.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Do credits expire?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    No, your purchased credits never expire. You can use them whenever you need.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Group 3: Technical -->
                    <div class="mb-4">
                        <h4 class="mb-3 text-primary">Technical</h4>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Do you guarantee 100% indexing?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    No service can guarantee 100% indexing as it ultimately depends on Google's quality standards. However, we guarantee that we will successfully submit your URLs to Google's indexing API and force a crawl. If your content is indexable, it will likely get indexed.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    What is the VIP Queue?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    The VIP Queue allows you to prioritize your tasks for faster processing. This is available for indexing tasks with up to 100 URLs and costs an additional 5 credits per link.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5">
                    <p class="lead">Still have questions?</p>
                    <a href="/contact.php" class="btn btn-primary btn-lg">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
