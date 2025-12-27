# Indexing SaaS Layer (Internal Credits) — Product Requirements Document (PRD)

## 1. Overview
Build a PHP + MySQL SaaS that lets users submit URLs for indexing/checking via Provider API while all credits/balances are managed internally in our database. Provider is used strictly for task processing (create/check tasks, fetch results). Users top up credits via PayPal; credits are deducted per URL (and extra for VIP when enabled).

- Tech: PHP (MVC or small framework), MySQL 8.x, Bootstrap UI (Vue/React optional), optional Redis/queue for async polling.
- Roles: User, Admin.
- Constraints: Never expose Provider API keys to the frontend. No dependency on Provider balances. All billing/audit lives in our DB.

## 2. Scope
- In-scope: Registration/Login, internal credits ledger, PayPal payments, task submission/tracking (indexer/checker), URL-level results, resubmission, admin management, API logs and error monitoring, alerts.
- Out-of-scope: Using Provider for balances or invoices; exposing Provider keys.

## 3. Roles & Permissions
- User: Manage profile, view internal credits, submit tasks, view/download results, view payment history and API logs (self), resubmit failed links.
- Admin: Manage users/balances, suspend/restore, full visibility of tasks/logs/errors, manual credit adjustments, payment oversight, broadcast announcements, exports.

## 4. Functional Requirements
### 4.1 Authentication & Accounts
- Email/password registration, login, logout, password reset.
- Optional email verification.
- Profile page (change email/password). Show internal credits balance.

### 4.2 Credits & Billing
- Internal credit model: default 1 credit = 1 URL processed. VIP multiplier configurable (e.g., +1 credit per URL).
- On task submission: verify sufficient credits, reserve/deduct credits, then create Provider task.
- Resubmission deducts credits again per URL.
- PayPal top-up: on successful webhook capture, create payment record and credit ledger entry, increment user balance.
- Full audit via credit ledger: every change in balance is recorded with reason and reference.

### 4.3 Task Management (Indexer/Checker)
- Submit single/bulk URLs (CSV/text), Google/Yandex, normal or VIP.
- Store task metadata and each URL as `task_links` with statuses: pending, indexed, unindexed, error.
- Poll Provider for task status/results; persist to DB.
- Task details view: progress, per-URL status, error codes, report download (CSV/JSON).
- Bulk actions: resubmit failed URLs (revalidates credits).

### 4.4 Dashboard (User)
- Widgets: internal credits balance, usage summary (submitted/processed/failed), recent tasks, recent payments.
- API usage log (self): recent calls, status codes, error messages.
- Notifications: low balance, repeated failures, maintenance notices, admin announcements.

### 4.5 Payments (PayPal)
- Create order, redirect/approve flow; finalize via webhook (IPN/Webhook) to avoid client-side trust.
- Store transactions with amount, currency, PayPal IDs, status.
- Convert amount to credits based on configurable price per credit and promotions.
- Idempotent webhook processing; no duplicate credits.

### 4.6 Admin Dashboard
- Users: list/filter, view balances/usage, suspend/restore, manual credits adjustment.
- Tasks: global view, filters by status, type, VIP, user.
- Payments: all transactions, statuses, credits awarded, refunds.
- API health: error rates, failed calls, latency, most common issues.
- Logs export (CSV).
- Announcements broadcast.

### 4.7 API Usage & Error Monitoring
- Log every Provider API call: endpoint, payload, response, status_code, duration_ms, error_message, timestamp, user_id (nullable for system calls).
- Charts: error rate over time, top endpoints, most common errors, affected users.
- Alerts: threshold-based notifications for repeated failures or suspected abuse.

### 4.8 Security & Compliance
- Never expose Provider API keys on frontend; keep in server env/config.
- Passwords hashed (bcrypt/Argon2). CSRF, XSS, SQLi protections. HTTPS enforced.
- GDPR: account deletion and data export.
- Activity logs (login IP, timestamps).

## 5. Data Model (High Level)
- users (internal credits)
- credit_ledger (auditable credits changes)
- tasks (submission metadata)
- task_links (per-URL status/result)
- payments (PayPal transactions + credits awarded)
- api_logs (all external API calls to Provider)
- webhook_events (raw PayPal webhook deliveries with idempotency)
- admin_actions, announcements

## 6. External Integrations
- Provider API: create tasks (indexer/checker), fetch status/results.
- PayPal: Orders + Webhooks (capture/complete/refund). Idempotent processing.
- SMTP/Email: verification, resets, alerts.

## 7. Non-Functional
- Scalability: 1k+ active users; bulk tasks up to 10k URLs/req.
- Reliability: idempotent payments and task submission; retries with backoff.
- Performance: async polling for tasks; paginate lists; indexed queries.
- Observability: structured logging; dashboards for errors/latency.

## 8. Configurables
- Price per credit; VIP credit multiplier.
- Max URLs per submission.
- Polling intervals/backoff; timeouts for API calls.
- Currency, promotions, minimum top-up.

## 9. Endpoints (Illustrative)
- Auth: POST /auth/register, POST /auth/login, POST /auth/forgot, POST /auth/reset
- Profile: GET/PUT /me
- Credits: GET /me/credits, GET /me/ledger
- Tasks: POST /tasks, GET /tasks, GET /tasks/{id}, POST /tasks/{id}/resubmit-failed
- Links: GET /tasks/{id}/links, GET /tasks/{id}/report
- Payments: POST /payments/paypal/create, GET /payments, POST /webhooks/paypal
- Admin: GET/PUT /admin/users, POST /admin/users/{id}/adjust-credits, GET /admin/tasks, GET /admin/payments, GET /admin/api-logs, POST /admin/announcements

## 10. MVP Checklist
- Auth + internal credits (ledger-backed)
- SpeedyIndex tasks (submit/poll/store results)
- PayPal top-up + webhooks (idempotent)
- User dashboard (balance, tasks, payments, logs)
- Admin dashboard (users, credits, tasks, payments, API health)
- API/error logging, alerts

## 11. Acceptance Criteria (Key)
- Credits are deducted only if sufficient, atomically with task creation.
- All balance changes appear in `credit_ledger` with reason and reference.
- PayPal webhook can be replayed without duplicating credits.
- No SpeedyIndex balance endpoints are called.
- Per-URL results are persisted and exportable.
