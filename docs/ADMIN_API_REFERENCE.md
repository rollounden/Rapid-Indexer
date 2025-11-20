# Admin API Reference

This document describes the Admin API for Rapid Indexer, designed to allow external systems (such as a WordPress plugin) to manage users, payments, and credits.

## Authentication

All API requests must be authenticated using an API Key.

- **Header**: `X-Admin-Key: YOUR_ADMIN_API_KEY`

Ensure `ADMIN_API_KEY` is set in your `.env` file.

## Base URL

`https://rapid-indexer.com/api/admin/v1/index.php`

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

