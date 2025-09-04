### PayPal Integration

Mode: PayPal REST (Webhooks)

Flow
- User selects top-up amount → create SpeedyIndex invoice (optional) and PayPal order → approve → capture.
- Webhook `PAYMENT.CAPTURE.COMPLETED` received → validate signature → idempotently credit user balance → mark `payments` row as `paid`.

Endpoints (server)
- `POST /webhooks/paypal` (public): Receives events.
- `POST /payments/create` (auth): Creates a PayPal order for desired credits.
- `GET /payments` (auth): List user payment history.

Security
- Validate PayPal webhook using `PAYPAL-TRANSMISSION-ID`, `PAYPAL-TRANSMISSION-SIG`, `PAYPAL-CERT-URL`, `PAYPAL-TRANSMISSION-TIME`, and `webhook_id`.
- Use sandbox vs live based on `.env`.
- Idempotency: check `paypal_txn_id` uniqueness before crediting.

Crediting logic
- Map currency to credit qty (pricing table). Example: 1 credit = $0.01 (example only; configure).
- On successful capture: increase `users.credits_balance` by computed credits; record in `payments` with `paid` status.
- On refund: decrease balance proportionally, mark `refunded`.

Tables
- `payments`: `user_id`, `amount`, `method='paypal'`, `paypal_txn_id`, `status`.

Errors & Retries
- On transient errors, retry crediting with backoff via queue job.
- Alert admin if repeated failures or signature mismatch.

PowerShell test (sandbox)
```powershell
# Example obtaining access token (Client Credentials)
$cred = [System.Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes("$env:PAYPAL_CLIENT_ID:$env:PAYPAL_CLIENT_SECRET"))
$headers = @{ Authorization = "Basic $cred"; 'Content-Type' = 'application/x-www-form-urlencoded' }
Invoke-RestMethod -Method POST -Headers $headers -Uri "https://api-m.sandbox.paypal.com/v1/oauth2/token" -Body 'grant_type=client_credentials'
```
