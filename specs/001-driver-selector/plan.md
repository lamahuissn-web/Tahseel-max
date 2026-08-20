# Plan: WhatsApp Driver Selector

## Architecture
```
Dashboard (UI) → POST /toggle-driver → WhatsAppSettingsController::toggleDriver()
                                      → Updates .env
                                      → Clears config cache
                                      → Redirects back
```

## Components

### 1. Dashboard Card (`dashboard.blade.php`)
- Shows current driver badge (Zernio/OpenWA)
- Description of active transport
- Toggle button with confirmation dialog
- Form POST to `admin.settings.whatsapp.toggle_driver`

### 2. Controller (`WhatsAppSettingsController.php`)
- `toggleDriver()` method validates driver value
- Updates `.env` file via regex replacement
- Clears config cache
- Redirects with success message

### 3. Config (`config/zernio.php`)
- Reads `WHATSAPP_DRIVER` from `.env`
- Defaults to `openwa` if not set

### 4. Service (`WhatsAppService.php`)
- `isZernio()` checks `config('zernio.driver')`
- Routes to ZernioService or OpenWA based on driver

## Data Flow
```
User clicks toggle
  → POST /admin/settings/whatsapp/toggle-driver
  → Controller validates driver value
  → Controller updates .env file
  → Controller clears config cache
  → Redirect to dashboard
  → Dashboard reads new config
  → Shows updated driver badge
```

## Risk Assessment
| Risk | Impact | Mitigation |
|---|---|---|
| .env permission error | Toggle fails | Verify www-data has write access |
| Invalid driver value | Service disruption | Validate against allowed values |
| Config cache stale | Old driver used | Clear cache after every switch |
| .env corruption | App breaks | Regex replacement preserves file structure |

## Testing Strategy
1. **Unit**: Config value reflects driver
2. **Integration**: Toggle updates .env and config
3. **UI**: View renders correct driver card
4. **Edge**: Invalid values, permissions, concurrent access

## Rollback
- Manual: Edit `.env` and set `WHATSAPP_DRIVER=zernio`
- Or: `git checkout .env` if committed
