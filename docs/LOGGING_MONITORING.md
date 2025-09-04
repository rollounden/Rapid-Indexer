### Logging & Monitoring

Application Logging
- Log all outbound SpeedyIndex API calls in `api_logs` with `endpoint`, `request_payload`, `response_payload`, `status_code`, `error_message`, `user_id` (if applicable).
- Mask secrets and PII.

Metrics & Dashboards
- Widgets: error rates, failed requests, most common error codes, average latency, last success time.
- Usage stats: total submissions, index/check counts, top users.

Alerts
- Daily/weekly error reports to admin.
- Immediate alerts for webhook failures, repeated provider overload (`code=2`), or API downtime.

Retention
- Keep raw logs for N days; aggregate summaries for longer.

Exports
- Admin can export CSV of logs and stats per date range.

Abuse Detection
- Detect excessive error rates and invalid URL submissions; flag accounts for review.
