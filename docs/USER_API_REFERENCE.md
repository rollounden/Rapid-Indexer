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

## Endpoints

### 1. Get User Profile

Check your account status and credit balance.

- **URL**: `?action=me`
- **Method**: `GET`

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

Check the status of a specific task.

- **URL**: `?action=get_task&task_id={id}`
- **Method**: `GET`

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

Get detailed status for each link in a task.

- **URL**: `?action=get_task_links&task_id={id}`
- **Method**: `GET`

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

## Error Handling

If an error occurs, the API will return a 4xx or 5xx status code and a JSON body with an `error` field.

```json
{
  "success": false,
  "error": "Insufficient credits"
}
```

