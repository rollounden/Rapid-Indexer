<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Sandbox Testing Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">PayPal Sandbox Testing Guide</h4>
                    </div>
                    <div class="card-body">
                        <h5>🚀 How to Test PayPal Integration</h5>
                        
                        <div class="alert alert-info">
                            <strong>Important:</strong> You cannot create new accounts in PayPal Sandbox. You must use the pre-created test accounts below.
                        </div>
                        
                        <h6>📋 Test Accounts:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Business Account (Merchant)</h6>
                                        <p><strong>Email:</strong> sb-j2gdy45737228@business.example.com</p>
                                        <p><strong>Password:</strong> i$DK>V]1</p>
                                        <small class="text-muted">This is your merchant account</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Personal Account (Buyer)</h6>
                                        <p><strong>Email:</strong> sb-123456789@personal.example.com</p>
                                        <p><strong>Password:</strong> 12345678</p>
                                        <small class="text-muted">Use this to test payments</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mt-4">🧪 Testing Steps:</h6>
                        <ol>
                            <li>Go to <a href="payments.php" target="_blank">Payments Page</a></li>
                            <li>Enter a test amount (e.g., $1.00)</li>
                            <li>Click "Add Credits"</li>
                            <li>You'll be redirected to PayPal Sandbox</li>
                            <li>Log in with the <strong>Personal Account</strong> credentials above</li>
                            <li>Complete the payment</li>
                            <li>You'll be redirected back to your site</li>
                            <li>Check that credits were awarded</li>
                        </ol>
                        
                        <h6 class="mt-4">🔍 What to Check:</h6>
                        <ul>
                            <li>Payment status changes from "Pending" to "Paid"</li>
                            <li>Credits are added to your balance</li>
                            <li>Payment appears in payment history</li>
                            <li>Webhook logs show successful processing</li>
                        </ul>
                        
                        <h6 class="mt-4">📊 Test Different Scenarios:</h6>
                        <ul>
                            <li><strong>Successful Payment:</strong> Complete the payment normally</li>
                            <li><strong>Cancelled Payment:</strong> Click "Cancel" on PayPal page</li>
                            <li><strong>Failed Payment:</strong> Use invalid card details</li>
                            <li><strong>Refund:</strong> Test refund functionality in PayPal dashboard</li>
                        </ul>
                        
                        <div class="alert alert-warning">
                            <strong>Note:</strong> All transactions in sandbox are fake. No real money is charged.
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="payments.php" class="btn btn-primary">Start Testing</a>
                            <a href="test_paypal.php" class="btn btn-info">Test PayPal API</a>
                            <a href="test_webhook.php" class="btn btn-success">Test Webhook</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
