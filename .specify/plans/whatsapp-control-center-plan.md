# Implementation Plan: WhatsApp Control Center

**Branch**: `feature/whatsapp-control-center` | **Date**: 2026-07-11 | **Spec**: `.specify/specs/whatsapp-control-center.md`

## Summary

Transform the current WhatsApp settings page into a full **WhatsApp Control Center** with 6 sections: Dashboard, Templates, Send, Automation, Message Log, and Queue. P1 covers Dashboard + Templates + Send + Message Log. P2 covers Automation + Queue.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 10

**Primary Dependencies**: OpenWA API, WhatsAppMessageBuilder (existing), WhatsAppService (existing), PaymentReceiptNotifier (existing)

**Storage**: 
- `app_config` table (existing) — for template bodies, automation rules
- `whatsapp_message_log` table **NEW** — for message history

**Testing**: Manual — send test messages + verify UI renders correctly

**Constraints**:
- Do NOT break existing WhatsApp sending (cron reminders, auto-receipts, emergency kill switch)
- Keep backward compatibility — existing routes must still work during transition
- Sidebar link: TBD — under Settings or standalone (to be decided with Kira)

## Constitution Check

✅ **Single Source of Truth** — Message templates stored in DB, not hardcoded
✅ **User Configurable** — Non-technical staff can edit templates from UI
✅ **Traceability** — Every sent message logged with status
✅ **Targeted Operations** — Send to specific clients or filtered groups
✅ **Non-Blocking** — Existing cron and auto-receipts continue working

## Project Structure

### New files
```text
app/Models/WhatsAppMessageLog.php                           # New model
database/migrations/xxxx_create_whatsapp_message_logs_table.php  # New migration
resources/views/dashbord/whatsapp/                          # NEW folder
├── dashboard.blade.php                                      # Dashboard tab
├── templates.blade.php                                      # Templates tab  
├── send.blade.php                                           # Send tab
├── automation.blade.php                                     # Automation tab (P2)
├── log.blade.php                                            # Message Log tab
└── queue.blade.php                                          # Queue tab (P2)
app/Services/WhatsApp/                                       # Maybe new service
└── BroadcastService.php                                     # NEW (Send logic)
```

### Modified files
```text
app/Http/Controllers/Admin/WhatsAppSettingsController.php    # ★ Major refactor
routes/admin.php                                              # New routes + tab URLs
resources/views/dashbord/settings/whatsapp.blade.php          # Replace with redirect or merge
lang/ar/clients.php                                           # New translations
app/Services/WhatsApp/WhatsAppMessageBuilder.php              # Template loading from DB
app/Services/WhatsApp/PaymentReceiptNotifier.php              # Log to whatsapp_message_log
app/Console/Commands/WhatsAppRemindersCommand.php             # Log to whatsapp_message_log
config/app_config.php (or similar)                            # Default template keys
```

### Unchanged files
```text
app/Http/Controllers/Admin/WhatsAppSettingsController.php     # Existing methods preserved
app/Services/WhatsAppService.php                              # ✅ No change needed
```

## Implementation Phases

### Phase 1A: Database + Model + Migration

**Objective**: Create the foundation — message log table and model.

| Task | Detail |
|------|--------|
| Create migration | `create_whatsapp_message_logs_table` with all columns |
| Create model | `WhatsAppMessageLog` with fillable, casts, relationships |
| Seed default templates | Add default template bodies to `app_config` if not exist |
| Run migration | On CT 131 |

### Phase 1B: Route Restructure

**Objective**: Set up `/admin/whatsapp/*` route group with tabs.

| Task | Detail |
|------|--------|
| Add route group | Prefix `admin/whatsapp` with auth + admin middleware |
| Dashboard route | `GET /admin/whatsapp/dashboard` |
| Templates routes | `GET /admin/whatsapp/templates`, `POST /admin/whatsapp/templates/save`, `POST /admin/whatsapp/templates/test` |
| Send routes | `GET /admin/whatsapp/send`, `POST /admin/whatsapp/send/broadcast`, `GET /admin/whatsapp/send/search-clients` |
| Log route | `GET /admin/whatsapp/log` |
| Log API routes | `GET /admin/whatsapp/log/data` (DataTables), `POST /admin/whatsapp/log/{id}/resend` |
| Automation routes (P2) | `GET /admin/whatsapp/automation`, `POST /admin/whatsapp/automation/{id}/toggle`, `POST /admin/whatsapp/automation/{id}/run` |
| Queue routes (P2) | `GET /admin/whatsapp/queue`, `POST /admin/whatsapp/queue/resend-failed`, `POST /admin/whatsapp/queue/pause` |
| Update sidebar | Replace old settings link with Control Center link |

### Phase 1C: Dashboard Tab

**Objective**: Show pulse metrics at a glance.

| Task | Detail |
|------|--------|
| Dashboard controller method | Query: connection status, today's count, this month's count, failures, client reachability |
| Dashboard view | 6-8 stat cards in a grid, emergency state banner |
| Quick action buttons | Emergency Stop + Restart Service (reuse existing) |
| Last activity row | Recent messages, last sent timestamp |

### Phase 1D: Templates Tab

**Objective**: Editable message bodies with live preview.

| Task | Detail |
|------|--------|
| List templates | Fetch from `app_config` — receipt, reminder, disconnection, etc. |
| Edit form | Textarea per template with current body |
| Variable buttons | Buttons for `{name}`, `{amount}`, `{date}`, `{phone}` that insert at cursor |
| Live preview | Show rendered template with sample data |
| Save action | `POST /admin/whatsapp/templates/save` — updates `app_config` |
| Test send | `POST /admin/whatsapp/templates/test` — send to provided number |
| Fallback | If `app_config` key missing, use hardcoded default |

### Phase 1E: Message Log Tab

**Objective**: Full searchable, filterable history.

| Task | Detail |
|------|--------|
| Log view | DataTable with columns: Client Name, Phone, Template, Status, Sent At |
| Search | By client name or phone (server-side DataTable search) |
| Filters | Date range picker, status dropdown, template type dropdown |
| Row expansion | Click to show full message body in a modal |
| Resend button | On failed rows — `POST /admin/whatsapp/log/{id}/resend` |
| Default scope | Last 30 days, paginated |

### Phase 1F: Send Tab (Broadcast)

**Objective**: Send to specific clients or filtered groups.

| Task | Detail |
|------|--------|
| Client search | Typeahead/Select2 — search by name, phone, ID |
| Selected list | Removable chips showing selected clients |
| Filter panel | Unpaid bills (>= N), area, subscription type, last payment date |
| Combined logic | Filter results + manual add/remove = final list |
| Template picker | Dropdown to pick which template to send |
| Preview count | "42 customers will receive this message" |
| Send button | `POST /admin/whatsapp/send/broadcast` — loop + log each |
| Result summary | X sent, Y failed with list of failures |
| Rate limiting | Pause 1 second between sends to avoid OpenWA overload |

### Phase 2A: Automation Tab (P2)

**Objective**: View and control scheduled WhatsApp commands.

| Task | Detail |
|------|--------|
| List automation rules | Fetch from `app_config` or parse `Kernel.php` schedule |
| Toggle active/inactive | Update `app_config` — `whatsapp_auto_enabled` per rule |
| Run Now button | Execute the Artisan command immediately via `Artisan::call()` |
| Status badges | Active 🟢 / Inactive ⚪ / Error 🔴 |
| Last run info | Show last execution time + result |

### Phase 2B: Queue Tab (P2)

**Objective**: Simple queue visibility.

| Task | Detail |
|------|--------|
| Queue status | Pending / Sending / Failed counts |
| Recent items | Last 50 messages in reverse chronological order |
| Resend failed | Resend all failed items in one click |
| Pause/Resume | Toggle `whatsapp_auto_enabled` temporarily |

## Execution Order

```
Phase 1A (DB + Model)
    │
    ▼
Phase 1B (Routes) ──→ Phase 1C (Dashboard) ──→ Phase 1D (Templates)
                            │                            │
                            ▼                            ▼
                     Phase 1E (Message Log) ←── Phase 1F (Send)
                                                    │
                          ┌─────────────────────────┘
                          ▼
                    Phase 2A (Automation)
                          │
                          ▼
                    Phase 2B (Queue)
```

## Verification

| Check | How |
|-------|-----|
| All new routes respond 200 | `php artisan route:list` + curl each |
| Dashboard shows real data | Open in browser — numbers should match reality |
| Template save + preview | Edit text → save → preview updates |
| Send to single client | Search → select → send → check log |
| Send to filtered group | Filter → preview count → send → check log |
| Message log search | Search by name → results filter correctly |
| Resend failed | Fail a send → resend → status updates |
| Existing reminders still work | Run `php artisan whatsapp:reminders` — no crash |
| Existing receipts still work | Trigger a payment — receipt arrives |
| Emergency stop still works | Hit emergency button — WhatsApp stops |

## Design Constraint — Keen Template Compliance

**CRITICAL:** All new views MUST use Keen's built-in components — NOT custom CSS.

### Required Patterns

```blade
@extends('dashbord.layouts.master')

@section('toolbar')
<div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
    @php
    $title = trans('...');
    $breadcrumbs = [
        ['label' => trans('Toolbar.home'), 'link' => route('admin.dashboard')],
        ['label' => trans('...'), 'link' => ''],
    ];
    PageTitle($title, $breadcrumbs);
    @endphp
</div>
@endsection

@section('content')
<div id="kt_app_content_container" class="app-container container-xxxl">
    {{-- Keen card components ONLY --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ trans('...') }}</h3>
            <div class="card-toolbar">
                {{-- action buttons --}}
            </div>
        </div>
        <div class="card-body">
            {{-- content --}}
        </div>
    </div>
</div>
@endsection
```

### Allowed Components

| Keen Component | Usage |
|----------------|-------|
| `.card` / `.card-header` / `.card-body` / `.card-title` / `.card-toolbar` | All containers |
| `.row` / `.col-*` | Bootstrap grid |
| `.form-label` / `.form-control` / `.form-select` / `.input-group` / `.input-group-text` | Forms |
| `{!! form_icon('...') !!}` | Input icons |
| `.btn` / `.btn-primary` / `.btn-light` / `.btn-success` / `.btn-danger` / `.btn-sm` | Buttons |
| `.badge` / `.badge-success` / `.badge-danger` / `.badge-warning` | Status badges |
| `.table` / `.table-row-bordered` / `.table-align-middle` | Tables |
| `.select2` / `.selectpicker` | Searchable selects |
| `.modal` / `.modal-dialog` / `.modal-content` | Modals |
| `data-kt-*` attributes | Keen JS behaviors |
| `bi-*` Bootstrap Icons | Icons |
| `.text-muted` / `.fw-bold` / `.fs-*` / `.text-*` | Typography utilities |

### Forbidden

- ❌ Custom CSS card classes (`wa-card`, `wa-card-header`, `wa-card-body`, etc.)
- ❌ Inline `<style>` blocks for layout (OK only for very specific overrides)
- ❌ Hardcoded colors — use Keen CSS variables or Bootstrap utility classes
- ❌ Div-based tables — use Keen `.table` or DataTables

### RTL Handling

Since Tahseel is Arabic-first:
- All `.me-*` / `.ms-*` / `.pe-*` / `.ps-*` spacing classes auto-flip via RTL bundle
- Use `.gap-*` for flex spacing instead of hard margins when possible
- Use `margin-inline-end` / `margin-inline-start` for directional margins
- Test every view in both RTL (Arabic) and LTR (English) layout

### Current WhatsApp Page Must Be Replaced

The existing `resources/views/dashbord/settings/whatsapp.blade.php` has ~450 lines of custom CSS + HTML. It will be **completely replaced** by the 6 Keen-compliant tab views under `resources/views/dashbord/whatsapp/`.
