### Product Requirements Document (PRD): SpeedyIndex SaaS Layer

This PRD reflects the product owner's specification.

1. Overview
Build a SaaS web application that allows users to:
- Submit URLs for Google indexing/checking via SpeedyIndex API
- Manage their tasks, monitor progress/results
- Track their usage and balances
- Recharge balances via PayPal
- Access logs, history, reports
- Admins can manage users, payments, usage, and API health

2. Core Features
2.1. User Registration & Authentication
- User registration with email & password
- Email verification (optional)
- Login/logout
- Password reset
- Profile management (email, password, API key view, balance, usage history)

2.2. Dashboard (User)
- Current balance (fetched via SpeedyIndex API)
- Usage overview (number of links submitted/indexed, checker usage, etc.)
- List of submitted tasks with status (sortable/filterable)
- Submit new tasks (indexer/checker, Google/Yandex, single/multiple URLs)
- Task detail view (results, download report)
- Invoice/payment top-up flow (PayPal integration)
- API usage logs (user-level: their own usage/errors)
- Notification center (for errors, successful submissions, low balance alerts)

2.3. Task Management
- Create tasks (indexer/checker) with bulk URL upload (CSV/text box, max 10,000/req)
- Option to submit for VIP queue (if under 100 links)
- See task status/progress
- Download reports (indexed/unindexed)
- Resubmit failed links (bulk action)
- Task history & status refresh
- Rate limiting/validation on requests

2.4. Payment Integration
- Top up balance via PayPal (auto-invoicing, webhook for status update)
- Usage-based billing: each user has their own SpeedyIndex credit balance
- Show pricing & consumption transparently
- Store all payment history in DB (with status, transaction ID, etc.)
- Admin ability to add/subtract credits manually

2.5. Admin Dashboard
- User management (list, suspend, delete, edit balances, view logs)
- Usage stats (total submissions, index/check counts, top users)
- Payment logs (all transactions, filterable)
- API health/errors monitor (failed calls, last success, average latency)
- Manual task creation/trigger for users
- Download/export all logs & stats (CSV)
- Broadcast announcements (shown on all dashboards)
- Custom reports (daily/weekly/monthly usage, errors, revenue, etc.)

2.6. API Usage & Error Monitoring
- Log all API requests & responses in MySQL (per user, with error codes, timestamps, payload)
- Daily/weekly error reporting to admin (optional email alerts)
- Dashboard widgets for error rates, failed requests, most common issues
- Detect & flag users with high error rates or abuse patterns

2.7. Security & Compliance
- Secure token storage (never expose SpeedyIndex API keys in frontend)
- CSRF, XSS, SQLi protection (PHP best practices)
- Encrypted passwords (bcrypt)
- HTTPS only
- GDPR/data retention support (account deletion, data download)
- Activity logging (last login, IP address, suspicious activity)

3. Database Schema Outline (MySQL)
- Users: id, email, password_hash, status, created_at, updated_at, last_login_at, role (user/admin), credits_balance, api_key (per-user, optional), etc.
- Tasks: id, user_id, type (indexer/checker), search_engine, title, status, created_at, completed_at, speedyindex_task_id, vip (bool), etc.
- TaskLinks: id, task_id, url, status (pending, indexed, unindexed, error), result_data, checked_at, error_code, etc.
- Payments: id, user_id, amount, method, speedyindex_invoice_id, paypal_txn_id, status (pending, paid, failed, refunded), created_at
- ApiLogs: id, user_id, endpoint, request_payload, response_payload, status_code, error_message, created_at
- AdminActions: id, admin_id, action, target_id, details, created_at
- Announcements: id, title, message, show_from, show_to, is_active

4. User Stories / Flows
User
- Register > verify email > login
- Land on dashboard, see current SpeedyIndex balance
- Upload URLs to index/check > create task
- View task progress/results > download report
- Get notified if errors/failed links > resubmit if needed
- Top up credits via PayPal > instant balance update
- View payment history, usage stats, and logs

Admin
- Login to admin dashboard
- View all users, filter/suspend/reset balances
- See usage stats, payments, API health/errors
- Adjust credits or trigger manual task for a user
- Download full logs, broadcast announcements

5. Integrations & APIs
- SpeedyIndex API: All link submissions, status checks, balances, report downloads, invoice/payment creation.
- PayPal API: Payment handling (webhooks), instant crediting.
- SMTP/Email: For verification, alerts, notifications.

6. Technical Requirements
- Frontend: PHP-based (Laravel, or Vanilla PHP w/ Bootstrap/Vue.js for admin UI)
- Backend: PHP (object-oriented, RESTful where needed)
- Database: MySQL 8.x
- Queue: Optional for heavy tasks (Redis/Beanstalkd for async job handling)
- File Storage: Local for reports, option for S3-style backup
- Logging: All API interactions logged for audit/debugging

7. Non-Functional Requirements
- Scalability: Support 1000+ active users, large volume of URL tasks
- Reliability: 99% uptime, API fallback/retry logic
- Performance: Sub-second dashboard load times, async API polling for heavy tasks
- Support: Help/contact form, admin can respond in-app or by email

8. Nice-to-Have Features
- User-level API (users can integrate the service into their own tools)
- Multi-language UI (EN + other major languages)
- Affiliate system (track referrals, reward credits)
- Two-factor authentication
- Webhook/Slack/email alerts for completed tasks or errors
- Rate limiting & abuse prevention (API keys per user, quotas)

9. MVP Feature Checklist
Feature	MVP	Post-MVP
- User auth/registration	✔	
- Email notifications	✔	
- SpeedyIndex API integration	✔	
- Task submission/history	✔	
- Report download	✔	
- Payments via PayPal	✔	
- Admin dashboard/stats	✔	
- API/error logging	✔	
- Manual credit top-up (admin)	✔	
- Announcements		✔
- Affiliate system		✔
- 2FA		✔
