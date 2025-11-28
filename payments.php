<?php
require_once __DIR__ . '/config/config.php';
// Ensure logs work
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Create log dir if missing
if (!is_dir(__DIR__ . '/storage/logs')) {
    mkdir(__DIR__ . '/storage/logs', 0777, true);
}
ini_set('error_log', __DIR__ . '/storage/logs/php_errors.log');

session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/src/Db.php';
require_once __DIR__ . '/src/SettingsService.php';
$pdo = Db::conn();

$error = '';
$success = '';

$enable_paypal = SettingsService::get('enable_paypal', '1');
$enable_cryptomus = SettingsService::get('enable_cryptomus', '1');
$price_per_credit = (string)DEFAULT_PRICE_PER_CREDIT_USD;

try {
    $price_per_credit = SettingsService::get('price_per_credit', (string)DEFAULT_PRICE_PER_CREDIT_USD);
} catch (Exception $e) {
    // Fallback to default
}

// Handle discount validation (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validate_discount') {
    header('Content-Type: application/json');
    try {
        require_once __DIR__ . '/src/DiscountService.php';
        $code = $_POST['code'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        
        $discount = DiscountService::findActive($code);
        if (!$discount) {
            echo json_encode(['valid' => false, 'message' => 'Invalid or expired code']);
            exit;
        }
        
        $discountAmount = DiscountService::calculate($discount, $amount);
        
        echo json_encode([
            'valid' => true,
            'discount_amount' => $discountAmount,
            'final_amount' => max(0, $amount - $discountAmount),
            'code' => $discount['code'],
            'id' => $discount['id']
        ]);
    } catch (Exception $e) {
        echo json_encode(['valid' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle payment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_payment' || $_POST['action'] === 'create_crypto_payment') {
        $is_crypto = $_POST['action'] === 'create_crypto_payment';
        
        if (($is_crypto && $enable_cryptomus !== '1') || (!$is_crypto && $enable_paypal !== '1')) {
            $error = 'Selected payment method is currently disabled.';
        } else {
            $amount = floatval($_POST['amount']); // This is the amount TO PAY
            
            // Capture discount fields
            $original_amount = !empty($_POST['original_amount']) ? floatval($_POST['original_amount']) : null;
            $discount_code_id = !empty($_POST['discount_code_id']) ? intval($_POST['discount_code_id']) : null;
            $discount_amount = !empty($_POST['discount_amount']) ? floatval($_POST['discount_amount']) : 0;
            
            $credits_base = $original_amount ? $original_amount : $amount;
            $credits = intval($credits_base / (float)$price_per_credit);
        
            // Check minimum payment (based on PAID amount)
            // If highly discounted, maybe allow lower? 
            // PayPal has strict limits (usually > 0.01). Cryptomus too.
            // Let's enforce $1.00 min for safety if discounted, or keep $10 if not?
            // Original code had $10 min.
            // If I have a $100 pack and 90% off -> $10.
            // If I have a $10 pack and 50% off -> $5.
            // Let's just enforce min $1.00 for now to allow discounts to work effectively.
            
            if ($amount < 1.00) {
                $error = 'Minimum payment amount is $1.00';
            } else {
                try {
                    require_once __DIR__ . '/src/PaymentService.php';
                    
                    if ($is_crypto) {
                        require_once __DIR__ . '/src/CryptomusService.php';
                        $crypto = new CryptomusService();
                        $result = $crypto->createPayment($_SESSION['uid'], $amount, $discount_code_id, $original_amount, $discount_amount);
                        
                        if (isset($result['payment_url'])) {
                            header('Location: ' . $result['payment_url']);
                            exit;
                        } else {
                            throw new Exception('Failed to get payment URL');
                        }
                    } else {
                        // PayPal
                        require_once __DIR__ . '/src/PayPalService.php';
                        
                        // Create PayPal order
                        $paypal = new PayPalService();
                        // We charge the $amount (discounted)
                        $order = $paypal->createOrder($amount, 'USD', $_SESSION['uid'], "Rapid Indexer Credits - $credits credits");
                        
                        // Record pending payment with discount info
                        $payment_id = PaymentService::recordPending(
                            $_SESSION['uid'], 
                            $amount, 
                            'USD', 
                            null, 
                            $discount_code_id, 
                            $original_amount, 
                            $discount_amount
                        );
                        
                        // Update payment with PayPal order ID
                        $stmt = $pdo->prepare('UPDATE payments SET paypal_order_id = ? WHERE id = ?');
                        $stmt->execute([$order['id'], $payment_id]);
                        
                        // Redirect to PayPal
                        $approval_url = null;
                        foreach ($order['links'] as $link) {
                            if ($link['rel'] === 'approve') {
                                $approval_url = $link['href'];
                                break;
                            }
                        }
                        
                        if ($approval_url) {
                            header('Location: ' . $approval_url);
                            exit;
                        } else {
                            throw new Exception('PayPal approval URL not found');
                        }
                    }
                    
                } catch (Exception $e) {
                    $error = 'Failed to create payment: ' . $e->getMessage();
                }
            }
        }
    }
}

// Handle cancelled payments (when user returns without completing)
if (isset($_GET['cancelled'])) {
    $success = 'Payment was cancelled. No charges were made to your account.';
    // Clean up logic...
    try {
        $stmt = $pdo->prepare('UPDATE payments SET status = ? WHERE user_id = ? AND status = ? AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
        $stmt->execute(['failed', $_SESSION['uid'], 'pending']);
    } catch (Exception $e) {}
}

// Get user's current credits
$stmt = $pdo->prepare('SELECT credits_balance FROM users WHERE id = ?');
$stmt->execute([$_SESSION['uid']]);
$user_credits = $stmt->fetchColumn();

// Get payment history
$stmt = $pdo->prepare('
    SELECT p.*, 
           CASE 
               WHEN p.status = "paid" THEN p.credits_awarded 
               ELSE 0 
           END as credits_received,
           d.code as discount_code
    FROM payments p 
    LEFT JOIN discount_codes d ON p.discount_code_id = d.id
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT 20
');
$stmt->execute([$_SESSION['uid']]);
$payments = $stmt->fetchAll();

// Get credit ledger (recent transactions)
$stmt = $pdo->prepare('
         SELECT cl.*, 
            CASE 
                WHEN cl.reason = "payment" THEN "Payment"
                WHEN cl.reason = "task_deduction" THEN "Task Creation"
                WHEN cl.reason = "task_refund" THEN "Task Refund"
                WHEN cl.reason = "admin_adjustment" THEN "Admin Adjustment"
                ELSE cl.reason
            END as transaction_label
    FROM credit_ledger cl 
    WHERE cl.user_id = ? 
    ORDER BY cl.created_at DESC 
    LIMIT 10
');
$stmt->execute([$_SESSION['uid']]);
$ledger_entries = $stmt->fetchAll();

include __DIR__ . '/includes/header_new.php';
?>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Payments & Credits</h1>
        <p class="text-gray-400 mt-2">Manage your credit balance and view transaction history.</p>
    </div>

    <?php if ($error): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <!-- Current Balance -->
        <div class="card rounded-xl p-8 flex flex-col justify-center">
            <h2 class="text-lg font-bold text-gray-400 uppercase tracking-wide mb-4">Current Balance</h2>
            <div class="flex items-center gap-3">
                <div class="text-5xl font-extrabold text-white"><?php echo number_format($user_credits); ?></div>
                <span class="text-xl text-primary-500 font-bold">credits</span>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                1 credit = $<?php echo htmlspecialchars($price_per_credit); ?> USD
            </div>
        </div>
        
        <!-- Add Credits -->
        <div class="card rounded-xl p-8" x-data="paymentForm()">
            <h2 class="text-lg font-bold text-white mb-6">Add Credits</h2>
            
            <div class="flex space-x-2 mb-6 bg-black/20 p-1 rounded-lg border border-white/5">
                <?php if ($enable_paypal === '1'): ?>
                <button @click="activeTab = 'paypal'" 
                        :class="{ 'bg-primary-600 text-white': activeTab === 'paypal', 'text-gray-400 hover:text-white hover:bg-white/5': activeTab !== 'paypal' }"
                        class="flex-1 py-2 px-4 rounded-md text-sm font-bold transition-all">
                    <i class="fab fa-paypal mr-2"></i> PayPal
                </button>
                <?php endif; ?>
                <?php if ($enable_cryptomus === '1'): ?>
                <button @click="activeTab = 'crypto'" 
                        :class="{ 'bg-primary-600 text-white': activeTab === 'crypto', 'text-gray-400 hover:text-white hover:bg-white/5': activeTab !== 'crypto' }"
                        class="flex-1 py-2 px-4 rounded-md text-sm font-bold transition-all">
                    <i class="fas fa-coins mr-2"></i> Crypto
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Common Inputs -->
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Amount (USD)</label>
                <div class="relative rounded-md shadow-sm mb-2">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-gray-400 sm:text-sm">$</span>
                    </div>
                    <input type="number" x-model="inputAmount" @input="onAmountChange()" min="1" step="0.01" required
                           class="block w-full rounded-lg border-gray-700 bg-[#111] border border-[#333] py-3 pl-8 pr-12 text-white placeholder:text-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 sm:text-sm" 
                           placeholder="0.00">
                </div>
                
                <!-- Discount Section -->
                <div class="mt-4 p-4 bg-black/20 rounded-lg border border-white/5">
                     <div class="flex gap-2">
                         <div class="flex-grow">
                            <input type="text" x-model="discountCode" placeholder="Discount Code" class="block w-full rounded-lg border-gray-700 bg-[#111] border border-[#333] py-2 px-3 text-sm text-white">
                         </div>
                         <button @click="applyDiscount()" type="button" class="bg-white/10 hover:bg-white/20 text-white text-xs font-bold py-2 px-4 rounded transition-colors">
                             Apply
                         </button>
                     </div>
                     <p x-show="discountMsg" x-text="discountMsg" :class="discountValid ? 'text-green-400' : 'text-red-400'" class="text-xs mt-2"></p>
                     
                     <div x-show="discountValid" class="mt-3 pt-3 border-t border-white/5 text-sm">
                         <div class="flex justify-between text-gray-400 mb-1">
                             <span>Subtotal:</span>
                             <span>$<span x-text="inputAmount"></span></span>
                         </div>
                         <div class="flex justify-between text-green-400 mb-1">
                             <span>Discount:</span>
                             <span>-$<span x-text="discountAmount"></span></span>
                         </div>
                         <div class="flex justify-between text-white font-bold text-lg mt-2">
                             <span>Total to Pay:</span>
                             <span>$<span x-text="finalAmount"></span></span>
                         </div>
                     </div>
                </div>
            </div>
            
            <!-- Payment Forms -->
            <?php if ($enable_paypal === '1'): ?>
            <div x-show="activeTab === 'paypal'">
                <form method="POST">
                    <input type="hidden" name="action" value="create_payment">
                    <input type="hidden" name="amount" :value="discountValid ? finalAmount : inputAmount">
                    <input type="hidden" name="original_amount" :value="discountValid ? inputAmount : ''">
                    <input type="hidden" name="discount_code_id" :value="discountId">
                    <input type="hidden" name="discount_amount" :value="discountAmount">
                    
                    <button type="submit" :disabled="!inputAmount || inputAmount < 1" 
                            class="w-full bg-[#0070ba] hover:bg-[#005ea6] text-white font-bold py-3 px-6 rounded-lg transition-all shadow-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fab fa-paypal"></i> Pay with PayPal
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
            <?php if ($enable_cryptomus === '1'): ?>
            <div x-show="activeTab === 'crypto'" x-cloak>
                <form method="POST">
                    <input type="hidden" name="action" value="create_crypto_payment">
                    <input type="hidden" name="amount" :value="discountValid ? finalAmount : inputAmount">
                    <input type="hidden" name="original_amount" :value="discountValid ? inputAmount : ''">
                    <input type="hidden" name="discount_code_id" :value="discountId">
                    <input type="hidden" name="discount_amount" :value="discountAmount">
                    
                    <button type="submit" :disabled="!inputAmount || inputAmount < 1"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-all shadow-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-lock"></i> Pay with Crypto
                    </button>
                    <p class="text-center text-xs text-gray-500 mt-3">Powered by Cryptomus</p>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Payment History Table (truncated for brevity in thought process, but including in file write) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="card rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5 bg-white/5">
                    <h3 class="text-lg font-bold text-white">Payment History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-white/5">
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Credits</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Ref ID</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No payments found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr class="hover:bg-white/5 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-300">
                                            <?php echo date('M j, Y', strtotime($payment['created_at'])); ?>
                                            <span class="block text-xs text-gray-500"><?php echo date('g:i A', strtotime($payment['created_at'])); ?></span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold text-white">
                                            <?php if ($payment['original_amount']): ?>
                                                <span class="line-through text-gray-500 text-xs">$<?php echo number_format($payment['original_amount'], 2); ?></span>
                                                <br>
                                            <?php endif; ?>
                                            $<?php echo number_format($payment['amount'], 2); ?>
                                            <?php if (isset($payment['discount_code']) && $payment['discount_code']): ?>
                                                <span class="ml-1 text-xs text-green-400 bg-green-400/10 px-1 rounded"><?php echo htmlspecialchars($payment['discount_code']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-300">
                                            <?php echo number_format($payment['credits_awarded']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $statusClasses = [
                                                'paid' => 'text-green-400 bg-green-400/10',
                                                'completed' => 'text-green-400 bg-green-400/10',
                                                'pending' => 'text-yellow-400 bg-yellow-400/10',
                                                'failed' => 'text-red-400 bg-red-400/10',
                                                'refunded' => 'text-purple-400 bg-purple-400/10',
                                                'cancelled' => 'text-gray-400 bg-gray-400/10',
                                            ];
                                            $statusClass = $statusClasses[$payment['status']] ?? 'text-gray-400 bg-gray-400/10';
                                            ?>
                                            <span class="px-2 py-1 text-xs font-bold rounded <?php echo $statusClass; ?> uppercase">
                                                <?php echo htmlspecialchars($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-mono text-gray-500">
                                            <?php echo htmlspecialchars($payment['paypal_capture_id'] ?: '-'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="lg:col-span-1">
            <div class="card rounded-xl overflow-hidden h-full">
                <div class="px-6 py-4 border-b border-white/5 bg-white/5">
                    <h3 class="text-lg font-bold text-white">Recent Activity</h3>
                </div>
                <div class="divide-y divide-white/5">
                    <?php if (empty($ledger_entries)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <p>No recent activity</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ledger_entries as $entry): ?>
                            <div class="p-4 flex justify-between items-center hover:bg-white/5 transition-colors">
                                <div>
                                    <p class="text-sm font-bold text-white"><?php echo htmlspecialchars($entry['transaction_label']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('M j, g:i A', strtotime($entry['created_at'])); ?></p>
                                </div>
                                <span class="text-sm font-bold <?php echo $entry['delta'] >= 0 ? 'text-green-400' : 'text-red-400'; ?>">
                                    <?php echo ($entry['delta'] >= 0 ? '+' : '') . number_format($entry['delta']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function paymentForm() {
    return {
        activeTab: '<?php echo $enable_paypal === '1' ? 'paypal' : ($enable_cryptomus === '1' ? 'crypto' : ''); ?>',
        inputAmount: '',
        discountCode: '',
        discountMsg: '',
        discountValid: false,
        discountAmount: 0,
        finalAmount: 0,
        discountId: '',
        
        applyDiscount() {
            if (!this.discountCode || !this.inputAmount) {
                this.discountMsg = 'Please enter an amount and code';
                return;
            }
            
            this.discountMsg = 'Checking...';
            
            const formData = new FormData();
            formData.append('action', 'validate_discount');
            formData.append('code', this.discountCode);
            formData.append('amount', this.inputAmount);
            
            fetch('payments.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.valid) {
                    this.discountValid = true;
                    this.discountAmount = data.discount_amount;
                    this.finalAmount = data.final_amount;
                    this.discountId = data.id;
                    this.discountMsg = `Code applied! You save $${data.discount_amount}`;
                } else {
                    this.discountValid = false;
                    this.discountMsg = data.message;
                    this.finalAmount = 0;
                    this.discountId = '';
                }
            })
            .catch(e => {
                this.discountMsg = 'Error validating code';
            });
        },
        
        onAmountChange() {
            if (this.discountValid) {
                this.discountValid = false;
                this.discountMsg = 'Amount changed, please re-apply code.';
                this.discountId = '';
                this.finalAmount = 0;
                this.discountAmount = 0;
            }
        }
    }
}
</script>

<?php include __DIR__ . '/includes/footer_new.php'; ?>
