### SpeedyIndex SaaS Layer Documentation

- **PRD**: `docs/PRD.md`
- **Implementation Plan**: `docs/IMPLEMENTATION_PLAN.md`
- **Windows Setup**: `docs/SETUP_WINDOWS.md`
- **Database Schema (MySQL 8)**: `docs/DB_SCHEMA.sql`
- **SpeedyIndex API Guide**: `docs/SPEEDYINDEX_API.md`
- **PayPal Integration**: `docs/PAYPAL_INTEGRATION.md`
- **Security & Compliance**: `docs/SECURITY.md`
- **Logging & Monitoring**: `docs/LOGGING_MONITORING.md`

Quick start (Windows PowerShell):
```powershell
# Clone and prepare
# git clone <your-repo-url>
# cd Rapid Indexer

# Copy env template
# cp .env.example .env  # Note: use PowerShell Copy-Item if needed
Copy-Item .env.example .env -Force

# Install PHP deps (Composer required)
composer install

# Generate app key (Laravel)
php artisan key:generate

# Run migrations
php artisan migrate

# Start dev server
php artisan serve
```

See each document for details.

1
