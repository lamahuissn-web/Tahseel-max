# Tahseel - Agent Guide

## Stack
- Laravel 10, PHP 8.1+, MySQL 8.0
- Multi-lang: `mcamara/laravel-localization` (prefix: `{locale}/admin/*`)
- Auth: custom admin guard (`auth:admin`), JWT for API
- Permissions: `spatie/laravel-permission` v5, custom `App\Models\Role` (HasTranslations)
- `config/permission.php` line 27 must use `App\Models\Role::class` (not Spatie default)
- Assets: Vite 5 (`npm run dev` / `npm run build`)
- Tables: most business tables use `tbl_` prefix (`tbl_clients`, `tbl_invoices`, etc.)
- EditorConfig: 4 spaces, LF line endings

## Server Environment
- **Server**: LAN at `192.168.0.83`, served from `/var/www/html/tahseel`
- **APP_URL**: `http://192.168.0.83`
- **DB**: `tahseel` / `tahseelusr` / `Tahseel@2024!`
- **Web owner**: `www-data` — run `chown -R www-data:www-data storage bootstrap/cache` after changes
- **Crontab** (www-data): `* * * * * php /var/www/html/tahseel/artisan schedule:run`
- **Supervisor**: `tahseel-telegram-bot` polling process

## Branch Strategy
- `main` — production, stable
- `feature-sas4-integration` — SAS 4 integration (merged)
- `feature-ui-ux-improvements` — UI/UX improvements (current work)
- **Never commit directly to `main`** — work on feature branches, merge after testing
- To switch branches on server: `git checkout <branch> && git pull && php artisan view:clear config:clear route:clear`

## Key Commands
```bash
# After code changes on server (always run this)
php artisan view:clear && php artisan config:clear && php artisan route:clear

# Run a single migration
php artisan migrate --path=database/migrations/<file>.php --force

# Run all migrations
php artisan migrate --force

# Refresh views/assets after Blade or Vite changes
php artisan view:clear
npm run build          # production assets
npm run dev            # dev server (rarely used on this server)

# Telegram bot
supervisorctl restart tahseel-telegram-bot
php artisan telegram:send-backup --force

# SAS 4 auto-match (dry-run first)
php artisan sas4:auto-match --dry-run
php artisan sas4:auto-match

# Clear all caches
php artisan optimize:clear
```

## Setup (fresh clone)
```bash
cp .env.example .env
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate
php artisan db:seed --class=PermissionsSeeder
chown -R www-data:www-data storage bootstrap/cache
composer dump-autoload  # loads Telegram helper
```

## Critical Gotchas

### Blade `@json()` parse error
Never use `@json()` inside `<script>` tags — it causes parse errors. Always use:
```blade
{!! json_encode($variable) !!}
```

### Custom Role model
`app/Models/Role.php` extends `Spatie\Permission\Models\Role` with `HasTranslations`. If missing, all role/permission pages crash. Update these files to import `App\Models\Role`:
- `app/Http/Controllers/Admin/RolesController.php`
- `app/Http/Controllers/Admin/UsersController.php`
- `app/Http/Controllers/Admin/Users/RolesController.php`
- `app/Services/RoleService.php`
- `app/Models/Admin.php`

### AppServiceProvider boot
Previously called `sendOverdueInvoiceNotifications()` on every request (DB hit). Moved to scheduled command `SendOverdueReminders`. If you see DB errors in boot, check this.

### Helper file crash
`get_app_config_data()` in `app/Helpers/main_helper.php:138` — fixed to use `$data?->value ?? null`. If helper errors appear, check this.

### Asset URLs
`ASSET_URL` must equal `APP_URL` (not `${APP_URL}/public`). Laravel appends `/public` itself. Double `/public/` in paths = misconfigured.

### `.env` is gitignored
Server `.env` is not committed. Contains SAS 4 credentials, JWT secret, FCM key, OneSignal keys. Copy from server when setting up locally.

## Key Paths
| Path | Purpose |
|------|---------|
| `routes/admin.php` | All admin routes (`{locale}/admin/*`) |
| `routes/adminauth.php` | Auth routes (login, logout) |
| `app/Helpers/main_helper.php` | Auto-loaded helper functions |
| `app/Helpers/notification_helper.php` | FCM + Telegram helpers |
| `app/Models/Role.php` | Custom Role model (critical) |
| `config/permission.php` | Must reference `App\Models\Role` |
| `public/.htaccess` | Required for Laravel routing |
| `config/sas4.php` | SAS 4 API config |
| `app/Services/Sas4/Sas4ApiService.php` | SAS 4 API client |
| `public/assets/css/custome/extra.css` | Main custom CSS (skeleton loaders, SAS4 styles, RTL utilities) |

## Admin Login
- URL: `/{locale}/admin/login`
- Default: `main_admin@yahoo.com` / `main_admin010`
- Imported admin password: `123456789`
- Locale: `ar` (Arabic) is primary

## Database
- 42 migrations applied. Key tables: `admins` (12), `tbl_clients` (~1470), `tbl_invoices` (~11750), `tbl_subscriptions` (28), `tbl_revenues`, `tbl_financial_transactions`
- Restore: `mysql tahseel < dump.sql` (strip `mysqldump:` warning lines first)

## Git
- Remote: `https://github.com/lamahuissn-web/tahseel-v2`
- Credentials: `~/.git-credentials`
- Workflow: `git add . && git commit -m "msg" && git push`

## UI Conventions

### Quick Panel Design Pattern
- Quick panel modals use `inv-list-item` style: rounded `12px` cards with `1px #e8e8e8` border, soft shadow, label + value layout
- Header uses `inv-header-summary` style: name + badge with `2px solid #ffc107` bottom border
- Action buttons use rounded card style with colored borders (`qp-btn-danger`, `qp-btn-success`, etc.)
- Reference: `resources/views/dashbord/clients/remaining_invoices_modal_content.blade.php`

### Modal Stacking
- When opening a modal from within another modal, **always close the parent first**:
  ```js
  $('#parentModal').modal('hide'); showChildModal(clientId);
  ```
- Example: Quick panel → remaining invoices modal (line ~1812 in `index.blade.php`)

### Client Quick Panel Modal
- Triggered by clicking client name on mobile (`showClientQuickPanel(clientId)`)
- Replaces DataTables inline child row (disabled with `responsive.details: false`)
- Uses `modal-fullscreen-sm-down` for responsive sizing
- Route: `GET /clients/{id}/quick-panel` → `ClientController::quickPanel()`
- Partial: `resources/views/dashbord/clients/quick_panel.blade.php`

### RTL Support
- Use logical CSS properties: `border-inline-start` (not `border-left`), `padding-inline-start`, `margin-inline-end`
- Use Bootstrap RTL utilities: `text-start`, `text-end`, `float-start`, `float-end`
- Sidebar active state uses `border-inline-start` for the indicator line

### Responsive Modals
- Use `modal-fullscreen-sm-down` class: full-screen on mobile (<576px), centered on desktop
- Quick panel modals stay on same page (no new browser tabs)

### Translation Helper (JS)
- Use `t2('key')` for JavaScript translations (defined in Blade, pulls from `lang/ar/clients.php`)
- Add new Arabic keys to `lang/ar/clients.php` under the relevant section

### AJAX Loading Pattern
- Show skeleton loader while AJAX loads, then fade in content
- Use `.sas4-skeleton` class for skeleton animation (defined in `extra.css`)
- Container pattern: `<div id="container"><div class="sas4-skeleton"></div></div>` → replace on success

### Abandoned Folder
- `resources/views/dashbord/clients1/` — old abandoned views, safe to delete
- Active views are in `resources/views/dashbord/clients/`

## Telegram Integration

### 3 features
- **Notifications** — 10 event types via `sendTelegramNotification()` (silent-fail, never throws)
- **Backup** — `php artisan telegram:send-backup` (or `--force`). Scheduled every minute, self-checks frequency
- **Bot** — Client search via `/client <name>` or `/عميل <name>` + inline mode. Runs 24/7 under Supervisor

### Key files
| File | Role |
|------|------|
| `app/Helpers/notification_helper.php` | Telegram send/recv functions |
| `app/Console/Commands/TelegramBackupCommand.php` | Backup logic |
| `app/Console/Commands/TelegramPollCommand.php` | Infinite polling loop (2s sleep) |
| `app/Services/TelegramBotService.php` | Message/inline query handlers |
| `/etc/supervisor/conf.d/tahseel-telegram-bot.conf` | Supervisor process |

### 18 `telegram_*` keys in `app_config` table (set via Settings → App Config)

## SAS 4 Integration

### Architecture
- SAS 4 server: `192.168.0.101` (LAN), login: `admin/admin`
- API requires AES-256-CBC encryption (CryptoJS-compatible) for all POST requests
- JWT tokens cached 55 minutes (`sas4_token` cache key)
- `tbl_clients.sas_username` links clients to SAS 4 users

### AES Encryption
Replicates CryptoJS key derivation: MD5 salted key+IV derivation → AES-256-CBC → `Salted__` prefix → base64. See `Sas4ApiService::aesEncrypt()`.

### Phase 1 (Done): Read-only
- SAS 4 info card in client detail modal (status, plan, speed, balance, expiration, traffic)
- `Sas4AutoMatch` command — dry-run matched 618/1472 clients by name normalization
- Routes: `/clients/{id}/sas4-info`

### Phase 2 Part 1 (Done): Link/Create
- SAS 4 account section in Client Create/Edit forms
- "Link Existing" — AJAX search autocomplete
- "Create New" — username, password, profile dropdown
- `ClientController::handleSas4Operations()` processes on save
- Routes: `/sas4/search-users`, `/sas4/profiles`

### Phase 2 Part 2 (Done): Control Actions
- Enable/Disable/Disconnect/Change Plan with expiration date support
- Bulk AJAX endpoint `POST /sas4/online-status` for table status indicators
- Online status column in clients table (mobile-responsive)
- Routes: `/clients/{id}/sas4-control`, `/sas4/online-status`

### Phase 3 (Done): Traffic & Sessions Tab
- Client detail modal converted to tabbed interface (Details / Traffic & Sessions)
- **Session Info Card**: online status, username, IP, last seen, sessions, plan, expiration, balance
- **Traffic Quota Card**: remaining download/upload/total/uptime (or "Unlimited")
- **Daily Traffic Report**: month/year selector, 31-day table (Day/Download/Upload/Total/Real Traffic), monthly summary footer
- Lazy-loaded on tab click, auto-refreshes every 60s while tab visible
- Routes: `/clients/{id}/sas4-traffic`, `/clients/{id}/sas4-daily-traffic`

### Phase 4 (Done): Quick Panel Modal
- Click SAS 4 status cell in clients table → opens `#sas4QuickPanelModal`
- Shows: info card, traffic quota, daily report (all devices), control actions, "View Full Details" link
- Reuses `GET /clients/{id}/sas4-traffic` endpoint and `sas4ControlAction()` JS function
- Uses `modal-fullscreen-sm-down` for responsive sizing
- JS functions: `showSas4QuickPanel()`, `loadSas4ProfilesForQuickPanel()`, `populateQuickPanelTrafficSelectors()`, `loadQuickPanelDailyTraffic()`

### Key API Endpoints
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/user/traffic` | POST + AES | Daily/monthly traffic report (rx, tx, total, total_real arrays) |
| `/api/index/UserSessions` | POST + AES | Full session history |
| `/api/index/online` | POST + AES | Active sessions (live session time, IP, MAC, NAS) |
| `/api/index/user` | POST + AES | User search with `daily_traffic_details`, `profile_details` |

### Config keys (`.env`)
```
SAS4_URL=http://192.168.0.101
SAS4_USERNAME=admin
SAS4_PASSWORD=admin
SAS4_AES_KEY=abcdefghijuklmno0123456789012345
```

## WhatsApp Integration (feature branch)

### Architecture
- **Library**: `@whiskeysockets/baileys` — no Chrome/browser needed
- **Service**: Node.js Express server on `localhost:3000` (managed by Supervisor)
- **Session**: Stored in `whatsapp-service/session/` — persists across restarts
- **Phone**: Connected to `+96170781562`

### REST API Endpoints (localhost:3000)
| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/status` | GET | `{ connected: true, phone: "+961..." }` |
| `/qr` | GET | `{ qr: "data:image/png;base64,...", connected: false }` |
| `/send` | POST | `{ phone: "+20...", message: "..." }` → sends message |
| `/logs` | GET | Recent sent messages (max 100) |

### Key Files
| File | Purpose |
|------|---------|
| `whatsapp-service/server.js` | Baileys WhatsApp service |
| `whatsapp-service/package.json` | Node.js dependencies |
| `app/Services/WhatsAppService.php` | Laravel Guzzle HTTP client |
| `app/Console/Commands/WhatsAppRemindersCommand.php` | Scheduled reminder command |
| `app/Http/Controllers/Admin/WhatsAppSettingsController.php` | Settings page controller |
| `resources/views/dashbord/settings/whatsapp.blade.php` | Settings UI |
| `/etc/supervisor/conf.d/whatsapp-service.conf` | Supervisor process config |

### Config Keys (stored in `app_config` table)
| Key | Default | Purpose |
|-----|---------|---------|
| `whatsapp_enabled` | `0` | Master toggle |
| `whatsapp_remind_before` | `3` | Days before due date |
| `whatsapp_remind_on_due` | `1` | Send on due date |
| `whatsapp_remind_after` | `1,3,7` | Days after overdue (comma-separated) |
| `whatsapp_message_template` | (Arabic) | Customizable message with `{name}`, `{total_amount}`, `{invoice_details_list}` |

### Command Usage
- **Preview only**: `php artisan whatsapp:reminders`
- **Actually send**: `php artisan whatsapp:reminders --send`
- Groups unpaid/partial invoices by client (one message per client)
- Shows preview table before sending (locale-aware: Arabic/English)
- 10-second delay between each message (rate limiting to avoid WhatsApp blocking)
- Duplicate prevention: skips clients already notified today
- Logs results to `whatsapp_message_logs` table (with `invoice_ids` JSON column)
- Updates invoice `last_notified_at` after successful send
- Cron schedule is commented out (manual execution only for now)

### Message Template Variables
| Variable | Description |
|----------|-------------|
| `{name}` | Client name |
| `{total_amount}` | Sum of all unpaid invoice remaining amounts |
| `{invoice_details_list}` | Multi-line breakdown: `فاتورة شهر أبريل (رقم 10794) بمبلغ 25.00$` |

### Default Template
```
مرحباً {name}،
نود تذكيرك بوجود مبالغ مستحقة غير مدفوعة لحسابك بإجمالي {total_amount}$.

تفاصيل الفواتير المستحقة:
{invoice_details_list}

يرجى التكرم بتسوية الرصيد المستحق في أقرب وقت ممكن. إذا كنت قد سددت هذا المبلغ مؤخراً، يرجى تجاهل هذه الرسالة. شكراً لتفهمك.
```

### Setup / Restart
```bash
cd whatsapp-service && npm install
supervisorctl reread && supervisorctl update
supervisorctl restart whatsapp-service
```
- If disconnected: go to Settings → WhatsApp, scan new QR code
- QR code refreshes every ~30 seconds on server side

### Admin Settings Page
- Route: `/{locale}/admin/settings/whatsapp`
- Sections: Connection Status + QR, Reminder Settings, Message Template, Test Send, Message Logs
- Sidebar link: WhatsApp icon (green) under Settings section

## Testing
- PHPUnit 10: `./vendor/bin/phpunit`
- Test suites: `tests/Unit`, `tests/Feature`
- Testing env: array cache/session, sync queue, no mail/telescope
- No custom test fixtures or integration test prerequisites documented
