<?php
require_once __DIR__ . '/../config/config.php';

class PayPalService {
    private $client_id;
    private $client_secret;
    private $base_url;
    private $access_token;
    
    public function __construct() {
        $this->client_id = PAYPAL_CLIENT_ID;
        $this->client_secret = PAYPAL_CLIENT_SECRET;
        $this->base_url = PAYPAL_ENV === 'live' 
            ? 'https://api-m.paypal.com' 
            : 'https://api-m.sandbox.paypal.com';
    }
    
    /**
     * Get PayPal access token
     */
    private function getAccessToken() {
        if ($this->access_token) {
            return $this->access_token;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $this->client_id . ':' . $this->client_secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception('Failed to get PayPal access token: ' . $response);
        }
        
        $data = json_decode($response, true);
        $this->access_token = $data['access_token'];
        
        return $this->access_token;
    }
    
    /**
     * Create a PayPal order
     */
    public function createOrder($amount, $currency = 'USD', $custom_id = null, $description = 'SpeedyIndex Credits') {
        $access_token = $this->getAccessToken();
        
        $order_data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', '')
                    ],
                    'description' => $description,
                    'custom_id' => $custom_id,
                    'soft_descriptor' => 'SpeedyIndex'
                ]
            ],
            'application_context' => [
                'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payment_success.php',
                'cancel_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payments.php?cancelled=1',
                'brand_name' => 'SpeedyIndex',
                'landing_page' => 'LOGIN',
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING'
            ]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
            'PayPal-Partner-Attribution-Id: ' . PAYPAL_BN_CODE
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 201) {
            throw new Exception('Failed to create PayPal order: ' . $response);
        }
        
        $data = json_decode($response, true);
        
        // Log the API call
        $this->logApiCall('create_order', $order_data, $response, $http_code === 201 ? 'success' : 'error');
        
        return $data;
    }
    
    /**
     * Capture a PayPal payment
     */
    public function capturePayment($order_id) {
        $access_token = $this->getAccessToken();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/checkout/orders/' . $order_id . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
            'PayPal-Partner-Attribution-Id: ' . PAYPAL_BN_CODE
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 201) {
            throw new Exception('Failed to capture PayPal payment: ' . $response);
        }
        
        $data = json_decode($response, true);
        
        // Log the API call
        $this->logApiCall('capture_payment', ['order_id' => $order_id], $response, $http_code === 201 ? 'success' : 'error');
        
        return $data;
    }
    
    /**
     * Get order details
     */
    public function getOrder($order_id) {
        $access_token = $this->getAccessToken();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/checkout/orders/' . $order_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
            'PayPal-Partner-Attribution-Id: ' . PAYPAL_BN_CODE
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            throw new Exception('Failed to get PayPal order: ' . $response);
        }
        
        $data = json_decode($response, true);
        
        // Log the API call
        $this->logApiCall('get_order', ['order_id' => $order_id], $response, $http_code === 200 ? 'success' : 'error');
        
        return $data;
    }
    
    /**
     * Refund a payment
     */
    public function refundPayment($capture_id, $amount = null, $reason = 'BUYER_REQUESTED') {
        $access_token = $this->getAccessToken();
        
        $refund_data = [
            'reason' => $reason
        ];
        
        if ($amount) {
            $refund_data['amount'] = [
                'value' => number_format($amount, 2, '.', ''),
                'currency_code' => 'USD'
            ];
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url . '/v2/payments/captures/' . $capture_id . '/refund');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($refund_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
            'PayPal-Partner-Attribution-Id: ' . PAYPAL_BN_CODE
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 201) {
            throw new Exception('Failed to refund PayPal payment: ' . $response);
        }
        
        $data = json_decode($response, true);
        
        // Log the API call
        $this->logApiCall('refund_payment', array_merge(['capture_id' => $capture_id], $refund_data), $response, $http_code === 201 ? 'success' : 'error');
        
        return $data;
    }
    
    /**
     * Log API calls to database
     */
    private function logApiCall($endpoint, $request_data, $response_data, $status) {
        try {
            $pdo = Db::conn();
            $stmt = $pdo->prepare('INSERT INTO api_logs (endpoint, request_data, response_data, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                'paypal_' . $endpoint,
                json_encode($request_data),
                $response_data,
                $status
            ]);
        } catch (Exception $e) {
            error_log('Failed to log PayPal API call: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify webhook signature (for production)
     */
    public function verifyWebhookSignature($payload, $headers) {
        // In production, implement proper webhook signature verification
        // For now, we'll just return true for sandbox testing
        return true;
    }
}
?>
