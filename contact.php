<?php
require_once __DIR__ . '/config/config.php';
session_start();

$user = null;
$success = '';
$error = '';

if (isset($_SESSION['uid'])) {
    require_once __DIR__ . '/src/Db.php';
    $pdo = Db::conn();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $user = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            require_once __DIR__ . '/src/Db.php';
            $pdo = Db::conn();
            
            $stmt = $pdo->prepare('INSERT INTO contact_messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $user['id'] ?? null,
                $name,
                $email,
                $subject,
                $message
            ]);
            
            $success = 'Your message has been sent successfully. We will get back to you soon!';
        } catch (Exception $e) {
            $error = 'Failed to send message. Please try again later.';
            // Ensure table exists
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                 $error .= ' (System update required: Run migration)';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - Rapid Indexer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/navbar.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <h1 class="h2 fw-bold mb-3">Contact Support</h1>
                            <p class="text-muted">Have questions or need assistance? We're here to help.</p>
                        </div>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Your Name</label>
                                    <input type="text" class="form-control" name="name" required 
                                           value="<?php echo htmlspecialchars($user['email'] ?? $_POST['name'] ?? ''); ?>"
                                           <?php echo $user ? 'readonly' : ''; ?>>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" class="form-control" name="email" required 
                                           value="<?php echo htmlspecialchars($user['email'] ?? $_POST['email'] ?? ''); ?>"
                                           <?php echo $user ? 'readonly' : ''; ?>>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subject</label>
                                <select class="form-select" name="subject" required>
                                    <option value="">Select a topic...</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Technical Support">Technical Support</option>
                                    <option value="Billing & Payments">Billing & Payments</option>
                                    <option value="Feature Request">Feature Request</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Message</label>
                                <textarea class="form-control" name="message" rows="6" required placeholder="How can we help you?"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted small">
                                Alternatively, email us at <a href="mailto:support@rapid-indexer.com">support@rapid-indexer.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
