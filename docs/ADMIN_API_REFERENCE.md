# Admin API Reference

This document describes the Admin API for Rapid Indexer, designed to allow external systems (such as a WordPress plugin) to manage users, payments, and credits.

## Authentication

All API requests must be authenticated using an API Key.

- **Header**: `X-Admin-Key: YOUR_ADMIN_API_KEY`
- **Alternative**: Query parameter `?api_key=YOUR_ADMIN_API_KEY` (useful if headers are stripped by your server)

### Setup
1. Generate a strong API key (alphanumeric recommended to avoid URL encoding issues).
   ```bash
   # Example:
   9f8e7d6c5b4a31209f8e7d6c5b4a3120
   ```
2. Add it to your `.env` file:
   ```env
   ADMIN_API_KEY=9f8e7d6c5b4a31209f8e7d6c5b4a3120
   ```

## Base URL

`https://rapid-indexer.com/api/admin/v1/index.php`

*(Or `http://localhost/api/admin/v1/index.php` if testing locally)*

## Endpoints

### 1. Create User

Creates a new user account.

- **URL**: `?action=create_user`
- **Method**: `POST`
- **Body (JSON)**:
  ```json
  {
    "email": "user@example.com",
    "password": "securePassword123"
  }
  ```

**Response**:
```json
{
  "success": true,
  "user": {
    "id": 123,
    "email": "user@example.com",
    "created_at": "2023-10-27 10:00:00"
  }
}
```

### 2. Get User

Retrieve user details by email or ID.

- **URL**: `?action=get_user&email=user@example.com` OR `?action=get_user&id=123`
- **Method**: `GET`

**Response**:
```json
{
  "success": true,
  "user": {
    "id": 123,
    "email": "user@example.com",
    "credits_balance": 500,
    "status": "active",
    "role": "user",
    "created_at": "2023-10-27 10:00:00"
  }
}
```

### 3. Get Recent Payments

Retrieve a list of recent payments.

- **URL**: `?action=get_recent_payments&limit=20`
- **Method**: `GET`
- **Parameters**:
  - `limit`: (Optional) Number of records to return (default: 50)

**Response**:
```json
{
  "success": true,
  "payments": [
    {
      "id": 101,
      "user_id": 123,
      "email": "user@example.com",
      "amount": "50.00",
      "currency": "USD",
      "method": "paypal",
      "status": "paid",
      "created_at": "2023-10-28 14:30:00"
    }
  ]
}
```

### 4. Update Credits

Add or remove credits from a user's balance.

- **URL**: `?action=update_credits`
- **Method**: `POST`
- **Body (JSON)**:
  ```json
  {
    "user_id": 123,
    "delta": 100,
    "reason": "Bonus for promotion"
  }
  ```
  - `delta`: Positive integer to add credits, negative to remove.
  - `reason`: (Optional) Description for the ledger.

**Response**:
```json
{
  "success": true,
  "message": "Credits updated",
  "new_balance": 600
}
```

### 5. Health Check

Verify API is reachable and key is valid.

- **URL**: `?action=health`
- **Method**: `GET`

**Response**:
```json
{
  "status": "ok",
  "version": "1.0"
}
```

## Troubleshooting

### "Unauthorized" Error
If you receive `{"error": "Unauthorized..."}`:
1. **Check Key Match**: Ensure the key in your request matches the `ADMIN_API_KEY` in `.env`.
2. **Debug Mode**: The API currently returns `debug_received_key_length` and `debug_configured_key_length`.
   - If `debug_received_key_length` is `0`, your server is stripping the `X-Admin-Key` header.
   - **Fix**: Pass the key as a query parameter: `&api_key=YOUR_KEY` instead.

### "Could not resolve host" Error
Your server cannot find the domain `rapid-indexer.com`.
- **Fix**: Use the server's IP address (e.g., `http://123.45.67.89/...`) or `localhost` if on the same machine.

## WordPress Plugin Integration Guide

To build a WordPress plugin that integrates with this API:

1.  **Settings Page**: Create a settings page in WP Admin to store:
    - `Rapid Indexer URL` (e.g., `https://indexer.example.com`)
    - `Admin API Key`

2.  **User Synchronization**:
    - Hook into `user_register` in WordPress.
    - When a user registers in WP, call `create_user` API endpoint.
    - Store the returned `rapid_indexer_user_id` in WP user meta.

3.  **Single Sign-On (SSO)** (Optional):
    - Use the `create_user` endpoint to ensure the account exists.
    - Implement a "Login to Indexer" button that redirects the user with a temporary token (requires additional implementation on the Indexer side).

4.  **Credit Display**:
    - Create a shortcode `[rapid_indexer_credits]` to display the user's balance.
    - The shortcode function should call `get_user` using the stored email address.

5.  **Credit Purchase**:
    - If using WooCommerce, hook into `woocommerce_payment_complete`.
    - Call `update_credits` to add credits when a specific product is purchased.

### Example PHP (WordPress) Code Snippet

```php
function rapid_indexer_create_user($user_id) {
    $user_info = get_userdata($user_id);
    $email = $user_info->user_email;
    $password = wp_generate_password(12, true); // Or sync password if possible

    $api_url = get_option('rapid_indexer_url') . '/api/admin/v1/index.php?action=create_user';
    $api_key = get_option('rapid_indexer_api_key');

    // Try sending key in both header and query param for maximum compatibility
    $api_url = add_query_arg('api_key', $api_key, $api_url);

    $response = wp_remote_post($api_url, [
        'headers' => [
            'X-Admin-Key' => $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'email' => $email,
            'password' => $password
        ])
    ]);

    // Handle response...
}
add_action('user_register', 'rapid_indexer_create_user');
```
