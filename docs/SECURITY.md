### Security & Compliance

Authentication & Authorization
- Bcrypt for passwords (Laravel default `bcrypt`).
- Roles: `user`, `admin`. Account status: `active`, `suspended`.
- CSRF protection on all POST/PUT/PATCH/DELETE.
- Session hardening: secure cookies, same-site=strict, HTTPS-only.

Secrets Management
- Store `SPEEDYINDEX_API_KEY`, `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET` in `.env`.
- Never expose SpeedyIndex key to frontend. All calls go through server-side service.
- Rotate credentials periodically; restrict access by role.

Input Validation & Sanitization
- Validate URLs before submission (max 10,000 per request, proper schemes).
- Rate limit per user to avoid abuse.

Transport & Storage
- Enforce HTTPS end-to-end.
- Use utf8mb4 and prepared statements.
- Log external API interactions without sensitive data.

GDPR / Data Rights
- Account deletion: anonymize user and purge PII where legally required.
- Data export: provide CSV/JSON of tasks, payments, logs on request.
- Retention: configurable purge for old logs/reports.

Monitoring & Alerts
- Alert on high error rates, failed webhooks, suspicious login locations.

Abuse Detection
- Flag users with unusually high error rates or invalid URLs.

Backup & Recovery
- Regular DB backups; test restore.

Incident Response
- Playbook for credential leak, data breach, or provider outage.
