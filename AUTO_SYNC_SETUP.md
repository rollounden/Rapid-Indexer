# Auto-Sync Setup Guide

## Overview
The system now has two auto-sync scripts for optimal performance:

1. **Checker Tasks**: Sync every 30 seconds (faster completion)
2. **Indexer Tasks**: Sync every 2 minutes (longer processing time)

## Cron Job Setup

### Option 1: Two Separate Cron Jobs (Recommended)

```bash
# Checker tasks - every 30 seconds
* * * * * /usr/bin/php /path/to/your/site/auto_checker_sync.php
* * * * * sleep 30; /usr/bin/php /path/to/your/site/auto_checker_sync.php

# Indexer tasks - every 2 minutes  
*/2 * * * * /usr/bin/php /path/to/your/site/auto_task_sync.php
```

### Option 2: Single Cron Job (Simpler)

```bash
# Run every minute - handles both checker and indexer tasks
* * * * * /usr/bin/php /path/to/your/site/auto_task_sync.php
```

## How It Works

### Checker Tasks
- **Frequency**: Every 30 seconds
- **Logic**: Checker tasks typically complete in 30-60 seconds
- **Script**: `auto_checker_sync.php`
- **Priority**: Higher priority in sync queue

### Indexer Tasks  
- **Frequency**: Every 2 minutes
- **Logic**: Indexer tasks take longer to process
- **Script**: `auto_task_sync.php`
- **VIP Support**: Includes VIP queue handling

## Manual Sync Options

### Tasks Page
- **Individual Sync**: Click "Sync" button on each task
- **Visual Indicators**: 
  - Checker tasks show "(30s)" 
  - Indexer tasks show "(2m)"
- **Tooltips**: Hover over sync button to see auto-sync frequency

### VIP Queue
- **Available**: Only for indexer tasks with ≤100 links
- **Cost**: Extra credits per URL
- **Benefit**: Guaranteed fast completion or auto-refund

## Monitoring

### Logs
Both scripts output detailed logs:
- ✅ Successful syncs
- ⏳ Still processing
- ❌ Errors
- 📊 Summary statistics

### Task Status
- **Pending**: Just created, waiting to start
- **Processing**: Active on SpeedyIndex
- **Completed**: All links processed
- **Failed**: Error occurred

## Benefits

1. **Faster Checker Results**: 30-second intervals for quick completion
2. **Efficient Indexer Processing**: 2-minute intervals for longer tasks
3. **Automatic Updates**: No manual intervention needed
4. **Priority Handling**: Checker tasks get priority in sync queue
5. **Error Handling**: Robust error logging and recovery

## Setup Commands

Replace `/path/to/your/site/` with your actual site path:

```bash
# For Hostinger/cPanel
* * * * * /usr/bin/php /home/u906310247/domains/cyan-peafowl-394593.hostingersite.com/public_html/auto_checker_sync.php
* * * * * sleep 30; /usr/bin/php /home/u906310247/domains/cyan-peafowl-394593.hostingersite.com/public_html/auto_checker_sync.php
*/2 * * * * /usr/bin/php /home/u906310247/domains/cyan-peafowl-394593.hostingersite.com/public_html/auto_task_sync.php
```
