# User API Reference

The Rapid Indexer User API allows you to integrate indexing capabilities directly into your applications, scripts, or WordPress sites.

## Base URL

```
https://rapid-indexer.com/api/v1/index.php
```

(Replace `https://rapid-indexer.com` with your actual domain if different)

## Authentication

Authentication is handled via the `X-API-Key` header.

1.  Get your API Key from your account settings (contact support if you don't see it).
2.  Include it in every request.

**Header Example:**
```
X-API-Key: 5f4dcc3b5aa765d61d8327deb882cf99
```

**Query Parameter Alternative:**
```
?api_key=5f4dcc3b5aa765d61d8327deb882cf99
```

## Indexing API

The Rapid Indexer User API allows you to integrate indexing capabilities directly into your applications, scripts, or WordPress sites.

### 1. Get User Profile

Check your account status and credit balance.

- **URL**: `?action=me`
- **Method**: `GET`

**cURL Example:**
```bash
curl -X GET "https://rapid-indexer.com/api/v1/index.php?action=me" \
     -H "X-API-Key: YOUR_API_KEY"
```

**Response:**
```json
{
  "success": true,
  "user": {
    "id": 123,
    "email": "user@example.com",
    "credits_balance": 500,
    "created_at": "2023-10-27 10:00:00"
  }
}
```

### 2. Create Task

Submit URLs for indexing or checking.

- **URL**: `?action=create_task`
- **Method**: `POST`
- **Content-Type**: `application/json`

**Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `urls` | array/string | Yes | - | Array of URLs or newline-separated string |
| `type` | string | No | `indexer` | `indexer` or `checker` |
| `engine` | string | No | `google` | `google` or `yandex` |
| `title` | string | No | null | Optional reference title |
| `vip` | bool | No | `false` | Enable VIP Queue (costs extra) |
| `drip_feed` | bool | No | `false` | Enable Drip Feed |
| `drip_duration_days` | int | No | `3` | Duration for drip feed in days |

**Request Body Example:**
```json
{
  "urls": [
    "https://example.com/page1",
    "https://example.com/page2"
  ],
  "type": "indexer",
  "engine": "google",
  "title": "My Blog Posts",
  "vip": true
}
```

**cURL Example:**
```bash
curl -X POST "https://rapid-indexer.com/api/v1/index.php?action=create_task" \
     -H "X-API-Key: YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "urls": ["https://example.com/page1", "https://example.com/page2"],
       "type": "indexer",
       "engine": "google",
       "title": "My Blog Posts"
     }'
```

**Response:**
```json
{
  "success": true,
  "message": "Task created successfully",
  "task_id": 456,
  "provider": "speedyindex",
  "is_drip_feed": false
}
```

### 3. Get Task Details

Check the status of a specific task (indexing, checking, or traffic).

- **URL**: `?action=get_task&task_id={id}`
- **Method**: `GET`

**cURL Example:**
```bash
curl -X GET "https://rapid-indexer.com/api/v1/index.php?action=get_task&task_id=456" \
     -H "X-API-Key: YOUR_API_KEY"
```

**Response:**
```json
{
  "success": true,
  "task": {
    "id": 456,
    "title": "My Blog Posts",
    "type": "indexer",
    "engine": "google",
    "status": "processing",
    "vip": true,
    "progress": {
      "updated": 10,
      "pending": 5
    },
    "created_at": "2023-12-23 14:00:00",
    "completed_at": null
  }
}
```

### 4. Get Task Links

Get detailed status for each link in an indexing/checking task.

- **URL**: `?action=get_task_links&task_id={id}`
- **Method**: `GET`

**cURL Example:**
```bash
curl -X GET "https://rapid-indexer.com/api/v1/index.php?action=get_task_links&task_id=456" \
     -H "X-API-Key: YOUR_API_KEY"
```

**Response:**
```json
{
  "success": true,
  "links": [
    {
      "url": "https://example.com/page1",
      "status": "indexed",
      "error_code": null,
      "checked_at": "2023-12-23 14:05:00"
    },
    {
      "url": "https://example.com/page2",
      "status": "pending",
      "error_code": null,
      "checked_at": null
    }
  ]
}
```

---

## Traffic API

Simulate viral traffic to your URLs.

### 5. Create Traffic Task

Simulate viral traffic to your URLs.

- **URL**: `?action=create_task`
- **Method**: `POST`
- **Content-Type**: `application/json`

**Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `type` | string | **Yes** | - | Must be `traffic` |
| `link` | string | **Yes** | - | Target URL to boost |
| `quantity` | int | **Yes** | - | Total visitors (Min: 100) |
| `mode` | string | No | `single` | `single` (Quick) or `campaign` (Drip-feed) |
| `days` | int | No | `1` | Duration in days (if mode=`campaign`) |
| `country` | string | No | `WW` | 2-letter code (e.g., US, DE, WW) |
| `device` | int | No | `5` | `1`=Desktop, `2`=Android, `3`=iOS, `5`=Mixed |
| `type_of_traffic` | int | No | `2` | `1`=Google Keyword, `2`=Referrer, `3`=Direct |
| `google_keyword` | string | No | - | Required if type_of_traffic=`1` |
| `referring_url` | string | No | - | Required if type_of_traffic=`2` |

#### Device Options

| Value | Description |
|-------|-------------|
| `5` | Mixed (Mobile & Desktop) — **Default** |
| `1` | Desktop |
| `4` | Mixed (Mobile Only) |
| `2` | Mobile (Android) |
| `3` | Mobile (iOS) |

#### Traffic Type Options

| Value | Description | Extra Parameter |
|-------|-------------|-----------------|
| `2` | Social Media / Custom Referrer — **Recommended** | `referring_url` |
| `1` | Google Keyword Search | `google_keyword` |
| `3` | Direct / Blank Referrer | None |

#### Available Countries

Use 2-letter ISO country codes. Default is `WW` (Worldwide).

**Regions:**
| Code | Region |
|------|--------|
| `WW` | Worldwide |
| `NAM` | North America (US, CA, MX) |
| `EUR` | Europe (DE, UK, FR, IT) |
| `ASI` | Asia (CN, IN, ID, JP) |
| `AFR` | Africa (NG, EG, ZA) |
| `SAM` | South America (BR, AR, VE) |
| `MEA` | Middle East (TR, SA, AE) |

**North America:**
| Code | Country |
|------|---------|
| `US` | United States |
| `CA` | Canada |

**Europe:**
| Code | Country |
|------|---------|
| `GB` | United Kingdom |
| `DE` | Germany |
| `FR` | France |
| `ES` | Spain |
| `IT` | Italy |
| `NL` | Netherlands |
| `SE` | Sweden |
| `CH` | Switzerland |
| `PL` | Poland |
| `BE` | Belgium |
| `AT` | Austria |
| `CZ` | Czech Republic |
| `DK` | Denmark |
| `HU` | Hungary |
| `LT` | Lithuania |
| `RO` | Romania |
| `RU` | Russia |
| `RS` | Serbia |
| `UA` | Ukraine |

**Asia & Pacific:**
| Code | Country |
|------|---------|
| `IN` | India |
| `ID` | Indonesia |
| `JP` | Japan |
| `KR` | South Korea |
| `HK` | Hong Kong |
| `SG` | Singapore |
| `TW` | Taiwan |
| `TH` | Thailand |
| `VN` | Vietnam |
| `PK` | Pakistan |
| `AE` | United Arab Emirates |
| `AU` | Australia |

**South America & Africa:**
| Code | Country |
|------|---------|
| `BR` | Brazil |
| `AR` | Argentina |
| `CL` | Chile |
| `ZA` | South Africa |

**Request Body Example:**
```json
{
  "type": "traffic",
  "link": "https://example.com/viral-post",
  "quantity": 5000,
  "mode": "campaign",
  "days": 3,
  "country": "US",
  "type_of_traffic": 2,
  "referring_url": "https://twitter.com/news/status/123"
}
```

**cURL Example:**
```bash
curl -X POST "https://rapid-indexer.com/api/v1/index.php?action=create_task" \
     -H "X-API-Key: YOUR_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{
       "type": "traffic",
       "link": "https://example.com/viral-post",
       "quantity": 5000,
       "mode": "campaign",
       "country": "US"
     }'
```

**Response:**
```json
{
  "success": true,
  "message": "Traffic task created successfully",
  "task_id": 457,
  "total_quantity": 5000,
  "runs": 12
}
```

---

## Error Handling

If an error occurs, the API will return a 4xx or 5xx status code and a JSON body with an `error` field.

```json
{
  "success": false,
  "error": "Insufficient credits"
}
```

