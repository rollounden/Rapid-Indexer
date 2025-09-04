### Windows Development Setup (PowerShell)

Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.x
- Node.js (optional for frontend tooling)
- Git

Install (PowerShell)
```powershell
# PHP (via winget) - adjust versions as needed
winget install --id=PHP.PHP --source winget --silent

# Composer
winget install --id=Composer.Composer --source winget --silent

# MySQL 8 (Community)
winget install --id=Oracle.MySQL --source winget --silent

# Node.js LTS (optional)
winget install --id=OpenJS.NodeJS.LTS --source winget --silent

# Git
winget install --id=Git.Git --source winget --silent
```

Project setup
```powershell
# Navigate to project root
Set-Location "C:\Users\rollo\Documents\websites\Development\RapidIndexer"

# Copy env
If (Test-Path .env -PathType Leaf) { Write-Host ".env exists" } Else { Copy-Item .env.example .env }

# Install dependencies
composer install

# Generate key
php artisan key:generate

# Configure DB in .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# Run migrations
php artisan migrate

# Seed (optional)
php artisan db:seed

# Serve
php artisan serve
```

Queue worker
```powershell
php artisan queue:work --tries=3 --backoff=5
```

Scheduler (Windows Task Scheduler)
- Create a task to run: `php artisan schedule:run` every minute.

Environment variables
- Set `SPEEDYINDEX_API_KEY`, `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET` in `.env`.
