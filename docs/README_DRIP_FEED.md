# Drip Feed Indexing Implementation

This feature allows users to drip-feed their indexing tasks over a period of time (e.g., 1-30 days) to simulate natural growth and avoid footprints.

## Setup Instructions

### 1. Database Migration
You must run the migration script to create the necessary tables and columns.
Run this command from the project root:

```bash
php db/migrate_drip_feed.php
```

If you cannot run CLI commands, you can execute the SQL found in `docs/DB_UPDATE_v2.sql` (steps 4, 5, and 6) using your database management tool (phpMyAdmin, Adminer, etc.).

### 2. Cron Job
The drip feed worker needs to run in the background to process batches.
Add the following cron job (run `crontab -e`):

```bash
# Run Drip Feed worker every 10 minutes
*/10 * * * * php /path/to/your/project/auto_drip_feed.php >> /path/to/your/project/storage/logs/drip_feed.log 2>&1
```

Make sure to replace `/path/to/your/project` with the actual path (e.g., `/var/www/html` or `/home/apex/Documents/Work/rapid-indexer/Rapid-Indexer-main`).

## How it Works
1. **User Selection**: In the Dashboard, users select "Drip Feed" and a duration (e.g., 3 Days).
2. **Task Creation**: The task is created with `is_drip_feed = 1` and `status = 'pending'`. Links are not submitted immediately.
3. **Worker Process**: 
   - `auto_drip_feed.php` runs periodically.
   - It picks up pending drip tasks.
   - It calculates a batch size based on the duration.
   - It submits a batch of links to the indexing provider (SpeedyIndex).
   - It updates the task's `next_run_at` time (default: every 2 hours).
   - It marks submitted links as `indexed` (submitted).
4. **Completion**: When all links are submitted, the task is marked as `completed`.

## Files Modified/Created
- `db/migrate_drip_feed.php` (New: Migration script)
- `auto_drip_feed.php` (New: Background worker)
- `dashboard.php` (Modified: Added UI for Drip Feed)
- `src/TaskService.php` (Modified: Added backend logic)

