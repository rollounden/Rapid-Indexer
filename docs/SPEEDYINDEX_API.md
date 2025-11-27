### SpeedyIndex API Guide

Base URL: `https://api.speedyindex.com`
Version: v2
Auth header: `Authorization: <API KEY>`




Balance
- GET `/v2/account`
- Response: `{ code, balance: { indexer, checker } }`
```powershell
$headers = @{ Authorization = "<API KEY>" }
Invoke-RestMethod -Headers $headers -Method GET -Uri "https://api.speedyindex.com/v2/account"
```

Create Task
- POST `/v2/task/<SEARCH ENGINE>/<TASK TYPE>/create`
- SEARCH ENGINE: `google|yandex`
- TASK TYPE: `indexer|checker`
- Body: `{ title?: string, urls: string[] }` (max 10,000 URLs)
- Response codes: 0 ok, 1 top up, 2 overloaded
```powershell
$headers = @{ Authorization = "<API KEY>"; 'Content-Type' = 'application/json' }
$body    = @{ title = 'My Task'; urls = @('https://google.com','https://google.ru') } | ConvertTo-Json -Depth 4
Invoke-RestMethod -Headers $headers -Method POST -Uri "https://api.speedyindex.com/v2/task/google/indexer/create" -Body $body
```

List Tasks
- GET `/v2/task/<SEARCH ENGINE>/list/<PAGE>` (PAGE size 1000; 0-based)
```powershell
Invoke-RestMethod -Headers $headers -Method GET -Uri "https://api.speedyindex.com/v2/task/google/checker/list/0"
```

Status of Tasks
- POST `/v2/task/<SEARCH ENGINE>/<TASK TYPE>/status`
- Body: `{ task_ids: string[] }` (≤ 1000 ids)
```powershell
$body = @{ task_ids = @('65f8c7305759855b9171860a') } | ConvertTo-Json
Invoke-RestMethod -Headers $headers -Method POST -Uri "https://api.speedyindex.com/v2/task/google/indexer/status" -Body $body
```

Full Report
- POST `/v2/task/<SEARCH ENGINE>/<TASK TYPE>/fullreport`
- Body: `{ task_id }`
- Returns indexed_links, unindexed_links (with error_code)
```powershell
$body = @{ task_id = '67f542b1e86b8c3b8ffac1a6' } | ConvertTo-Json
Invoke-RestMethod -Headers $headers -Method POST -Uri "https://api.speedyindex.com/v2/task/google/indexer/fullreport" -Body $body
```

Single URL Indexing
- POST `/v2/<SEARCH ENGINE>/url`
- Body: `{ url }`
- Response codes: 0 ok, 1 top up, 2 overloaded
```powershell
$body = @{ url = 'https://google.ru' } | ConvertTo-Json
Invoke-RestMethod -Headers $headers -Method POST -Uri "https://api.speedyindex.com/v2/google/url" -Body $body
```

Create Invoice
- POST `/v2/account/invoice/create`
- Body: `{ qty, type: 'indexer'|'checker'|'mix', method: 'crypto'|'paypal'|'yookassa', email? }`
```powershell
$body = @{ qty = 10000; method = 'crypto'; type = 'indexer' } | ConvertTo-Json
Invoke-RestMethod -Headers $headers -Method POST -Uri "https://api.speedyindex.com/v2/account/invoice/create" -Body $body
```

VIP Queue (google/indexer only)
- POST `/v2/task/google/indexer/vip`
- Body: `{ task_id }`
- Notes: ≤ 100 links; costs 1 extra credit per link; guarantees fast completion or auto-refund.
```powershell
$body = @{ task_id = '680222ce0428e10a6b16bf72' } | ConvertTo-Json
Invoke-RestMethod -Headers $headers -Method POST -Uri "https://api.speedyindex.com/v2/task/google/indexer/vip" -Body $body
```

Retry/backoff guidance
- If `code=2` (overloaded), retry with exponential backoff (e.g., 2s, 5s, 10s).

Logging
- Log every request/response, status code, and any error_message to `ApiLogs`.
