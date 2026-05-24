# Tahseel - Agent Guide

## Stack
- Laravel 10, PHP 8.1+, MySQL 8.0
- Multi-lang: `mcamara/laravel-localization` (prefix: `{locale}/admin/*`)
- Auth: custom admin guard (`auth:admin`), JWT for API
- Permissions: `spatie/laravel-permission` v5, custom `App\Models\Role` (has `HasTranslations`, `$translatable = ['title']`)
- `config/permission.php` line 27 must use `App\Models\Role::class` (not the Spatie default)

## Setup (fresh clone)
```bash
cp .env.example .env
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed --class=PermissionsSeeder
# ... other seeders per QUICK_SETUP.md
chown -R www-data:www-data storage bootstrap/cache
```

## Critical Gotchas

### AppServiceProvider boot query
`AppServiceProvider::boot()` **used** to call `sendOverdueInvoiceNotifications()` which hit the DB. This was moved to a scheduled command (`overdue:remind`). No longer blocks setup. If you see `Call to undefined function sendTelegramNotification()`, run `composer dump-autoload`.

### Custom Role model
Missing `app/Models/Role.php` causes `Call to undefined method Role::getTranslation()` errors. If absent, create it:
```php
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Translatable\HasTranslations;
class Role extends SpatieRole { use HasTranslations; public $translatable = ['title']; }
```
Then update these files to import `App\Models\Role` instead of `Spatie\Permission\Models\Role`:
- `app/Http/Controllers/Admin/RolesController.php`
- `app/Http/Controllers/Admin/UsersController.php`
- `app/Http/Controllers/Admin/Users/RolesController.php`
- `app/Services/RoleService.php`
- `app/Models/Admin.php`

### Helper file crash
`get_app_config_data()` in `app/Helpers/main_helper.php:138` returns `$data->value` — crashes if no record found. Fixed version returns `$data?->value ?? null`. If helper errors appear, check this.

### PHP extension `pdo_mysql`
Required but not always installed. Install with: `apt-get install php8.3-mysql`

## Key paths
| Path | Purpose |
|------|---------|
| `app/Helpers/main_helper.php` | Auto-loaded helper functions |
| `app/Helpers/notification_helper.php` | Auto-loaded notification helpers (FCM + Telegram) |
| `app/Models/Role.php` | Custom Role model (critical) |
| `routes/admin.php` | All admin routes (`{locale}/admin/*`) |
| `routes/adminauth.php` | Auth routes (login, etc.) |
| `app/Providers/AppServiceProvider.php` | Boot — no longer calls DB queries |
| `public/.htaccess` | Required for Laravel routing |
| `config/permission.php` | Must reference `App\Models\Role` |

## Admin login
- URL: `/{locale}/admin/login`
- Default credentials: `main_admin@yahoo.com` / `main_admin010`
- Imported admin password: `123456789`
- Locale: `ar` (Arabic) is primary

## DB notes
- Database: `tahseel`, user: `tahseelusr` / `Tahseel@2024!`
- To restore from backup: `mysql tahseel < dump.sql` (strip `mysqldump:` warning lines first)
- Key tables: `admins` (12), `tbl_clients` (~1470), `tbl_invoices` (~11750), `tbl_subscriptions` (28), `tbl_revenues`, `tbl_financial_transactions`
- 41 migrations applied (do not re-run fresh unless resetting)

## Git
- Remote: `https://github.com/lamahuissn-web/tahseel-v2`
- Credentials stored in `~/.git-credentials`
- Workflow: `git add . && git commit -m "msg" && git push`

## Asset URLs
`ASSET_URL` must equal `APP_URL` (not `${APP_URL}/public`) — Laravel appends `/public` itself. Double `/public/` in CSS/JS paths means misconfigured `ASSET_URL`.

## Telegram Integration

### 3 features
- **Notifications** — 10 event types pushed to Telegram group via `sendTelegramNotification()` in `app/Helpers/notification_helper.php`. Silent-fail pattern (never throws).
- **Backup** — `php artisan telegram:send-backup` (or `--force`). Scheduled every minute in Kernel.php, self-checks frequency. `mysqldump → gzip → sendDocument`.
- **Bot** — Client search via `/client <name>` or `/عميل <name>` + inline mode. Runs 24/7 under Supervisor.

### Key files
| File | Role |
|------|------|
| `app/Helpers/notification_helper.php` | `sendTelegramNotification()`, `sendTelegramDocument()`, `getTelegramUpdates()`, `sendTelegramAnswerInlineQuery()` |
| `app/Console/Commands/TelegramBackupCommand.php` | Backup logic + frequency scheduling |
| `app/Console/Commands/TelegramPollCommand.php` | Infinite polling loop (2s sleep) |
| `app/Services/TelegramBotService.php` | Message/inline query handlers |
| `app/Console/Commands/SendOverdueReminders.php` | Daily overdue reminders (replaced boot method) |
| `resources/views/dashbord/config_app/form.blade.php` | Settings form (3 cards: auth, toggles, backup) |
| `/etc/supervisor/conf.d/tahseel-telegram-bot.conf` | Supervisor process definition |

### Setup after fresh clone
```bash
composer dump-autoload  # ensures Telegram helper functions are loaded
php artisan telegram:send-backup --force  # test backup
supervisorctl restart tahseel-telegram-bot  # restart poll bot after code changes
```

### Infrastructure
- Supervisor runs polling bot: `supervisorctl {status|restart|start|stop} tahseel-telegram-bot`
- Crontab (www-data): `* * * * * php /var/www/html/tahseel/artisan schedule:run`
- Telegram bot token + chat ID stored in `app_config` (set via Settings → App Config)
- 18 `telegram_*` keys seeded in `app_config` table
