### Implementation Plan (Phased)

Phase 0: Foundation
- Initialize Laravel app, env config, auth scaffolding, roles (user/admin), middleware for status (active/suspended).
- Create MySQL schema migrations: Users, Tasks, TaskLinks, Payments, ApiLogs, AdminActions, Announcements.

Phase 1: SpeedyIndex Integration (MVP)
- Services for: account balance, task create, list, status, full report, single URL, invoice create, VIP queue.
- Background jobs for task status polling; store reports; map provider codes.
- API logging middleware to record all external calls in `ApiLogs`.

Phase 2: User Dashboard & Task UI
- Balance widget; create task (CSV/text bulk up to 10,000); task list with filters; task detail with report download; resubmit failed links; VIP if under 100 links.
- Notifications for errors, low balance, success.

Phase 3: Payments (PayPal)
- Checkout page to top up credits; webhook to finalize and credit; admin manual adjustments; payment history.
- Idempotent webhook handling by transaction id; signature validation.

Phase 4: Admin Dashboard
- User management; payments log; usage stats; API health/errors; manual task trigger; CSV exports; announcements.

Phase 5: Security, Compliance, Observability



- HTTPS, CSRF/XSS/SQLi protection, bcrypt, activity logs, GDPR data export/delete.
- Error dashboards, daily/weekly error reports; abuse detection.

Architecture
- Laravel Controllers → Services (SpeedyIndexService, PaymentsService) → HTTP Client.
- Jobs/Queue: polling, report fetching, notifications.
- Policies & Gates for admin/user access.
- Config in `.env` for API keys and PayPal credentials.

Milestones & Deliverables
- MVP features complete with tests; deployment checklist; rollback plan.
