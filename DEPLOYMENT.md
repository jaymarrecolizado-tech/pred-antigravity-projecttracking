# DICT Region 2 Infrastructure & Program Rollout Monitoring Platform
## Enterprise Deployment & Operations Guide (`DEPLOYMENT.md`)

This guide outlines the production deployment, queue workers, scheduled automation, and operational maintenance for the DICT Region 2 Infrastructure & Program Rollout Platform on CloudPanel / Nginx / Ubuntu with PHP 8.2 and MySQL 8.x.

---

### 1. Prerequisites & Required PHP Extensions

The application requires **PHP 8.2+** with the following core extensions enabled in `php.ini`:

```ini
extension=pdo_mysql
extension=mbstring
extension=openssl
extension=curl
extension=gd          ; Used for photo thumbnail generation and image processing
extension=exif        ; Used by FraudDetector to inspect camera GPS coordinates
extension=zip         ; Required by OpenSpout for .xlsx streaming read/write
extension=fileinfo    ; Required for MIME validation
```

Recommended upload limits for field photo evidence:
```ini
upload_max_filesize = 32M
post_max_size = 40M
memory_limit = 256M
```

---

### 2. Initial Setup & Database Provisioning

1. **Clone & Install Dependencies**:
   ```bash
   git clone <repo-url> /home/<user>/htdocs/<domain>
   cd /home/<user>/htdocs/<domain>
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

2. **Environment Configuration (`.env`)**:
   ```env
   APP_NAME="DICT Region 2 Infrastructure Tracking"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.gov.ph

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dict_r2_tracking
   DB_USERNAME=dict_r2_user
   DB_PASSWORD=SecurePasswordHere!

   QUEUE_CONNECTION=database
   SESSION_DRIVER=database
   CACHE_STORE=file
   ```

3. **Database Migration & Regional Seeders**:
   Run the following sequence to provision the schema and load the entire Region 2 geospatial hierarchy:
   ```bash
   php artisan migrate --force
   php artisan psgc:import       # Imports 694 entities (5 provinces, 91 LGUs, 597 barangays)
   php artisan districts:import  # Imports 12 versioned Congressional Districts
   php artisan db:seed --force   # Seeds initial portfolio programs, archetypes, and admin users
   php artisan rollup:rebuild    # Populates initial daily rollup aggregates for analytics
   php artisan storage:link      # Creates public storage symlink for field photos
   ```

---

### 3. Background Queue Worker (Supervisor)

The platform processes asynchronous rollup recalculations (`UpdateRollupJob`) via Laravel Queues. Create a Supervisor configuration at `/etc/supervisor/conf.d/dict-tracking-worker.conf`:

```ini
[program:dict-tracking-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/<user>/htdocs/<domain>/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=<user>
numprocs=2
redirect_stderr=true
stdout_logfile=/home/<user>/htdocs/<domain>/storage/logs/worker.log
stopwaitsecs=3600
```

Apply and start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start dict-tracking-worker:*
```

---

### 4. Automated Scheduled Cron Tasks

The platform includes two automated console routines:
1. **`reports:dispatch`**: Evaluates active user subscriptions (daily, weekly on Mondays, monthly) and dispatches compiled accomplishment digests.
2. **`rollup:rebuild`**: Nightly reconciliation validating daily aggregates against raw field intervention records.

Configure the system crontab (`crontab -e`):
```bash
* * * * * cd /home/<user>/htdocs/<domain> && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs registered in `routes/console.php`:
```php
use Illuminate\Support\Facades\Schedule;

// Nightly aggregate reconciliation at 1:00 AM
Schedule::command('rollup:rebuild')->dailyAt('01:00');

// Scheduled automated executive report dispatches every hour
Schedule::command('reports:dispatch')->hourly();
```

---

### 5. Nginx Web Server Configuration

Sample Nginx server block optimized for Laravel Inertia.js and large file uploads:

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name tracking.region2.dict.gov.ph;
    root /home/<user>/htdocs/<domain>/public;

    index index.php index.html;
    charset utf-8;
    client_max_body_size 40M;

    # SSL configuration here...

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static build chunks
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires max;
        log_not_found off;
        access_log off;
    }
}
```

---

### 6. Append-Only Integrity & Security Auditing

- **Interventions**: The `interventions` table is strictly append-only. Any attempt to execute `update()` or `delete()` on an `Intervention` record triggers a `LogicException`.
- **Audit Logs**: The `audit_logs` table records every administrative change, project creation, grant assignment, and report export. Like interventions, `AuditLog` rows cannot be modified or deleted.
- **Two-Factor Authentication**: Enforced via `EnsureTwoFactorEnforced` middleware. Super Admins and Project Managers must configure TOTP 2FA before accessing administrative features.
