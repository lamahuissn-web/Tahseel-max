# Spike: Zernio WhatsApp Adapter for Tahseel

**Date:** 2026-08-15 (initial) → 2026-08-17 (Phase 1: real WABA upgrade)
**Branch:** `spike/zernio-whatsapp-adapter`
**Question:** Can Tahseel's Laravel stack send WhatsApp messages through Zernio
(official Meta Cloud API) the same way it sends receipts through OpenWA today?

**Feasibility question (Given/When/Then):**
Given a Zernio API key + active sandbox session,
when a Laravel service posts a free-form receipt text to the inbox-messages endpoint,
then the message is delivered on WhatsApp via Meta Cloud with a real `wamid`.

## Architecture

```
WhatsAppService (driver switch)
├── WHATSAPP_DRIVER=openwa  → OpenWA (CT111, QR-based, current production)
└── WHATSAPP_DRIVER=zernio  → ZernioService
    ├── sendText()     — free-form within 24h customer-service window
    ├── sendTemplate() — Meta-approved templates (business-initiated)
    ├── sendSmart()    — auto-detect: try text → fallback to template
    └── status()       — sandbox or real WABA connection check
```

## Files

| File | Purpose |
|------|---------|
| `app/Services/WhatsApp/ZernioService.php` | Zernio API adapter (sandbox + real WABA) |
| `app/Services/WhatsAppService.php` | Driver switch (`WHATSAPP_DRIVER` env) |
| `config/zernio.php` | API config (key, account, WABA, sandbox flag) |
| `app/Console/Commands/ZernioTestCommand.php` | `php artisan zernio:test {phone}` |
| `tests/Feature/ZernioSpikeTest.php` | 11 unit tests (all passing) |
| `.env` → `ZERNIO_*` | API credentials (untracked) |

## Phase 1: Real WABA Upgrade (2026-08-17)

Upgraded from sandbox-only to production-ready with template support.

### What changed

1. **`config/zernio.php`** — Added `waba_id` and `sandbox` flag
   - `ZERNIO_WABA_ID` env var for real WABA
   - `ZERNIO_SANDBOX=true` (default) keeps sandbox mode safe

2. **`ZernioService.php`** — Full rewrite supporting both modes
   - `status()`: sandbox (`/whatsapp/sandbox/sessions`) vs real WABA (`/whatsapp/accounts`)
   - `sendText()`: free-form via inbox API (24h window)
   - `sendTemplate()`: Meta-approved templates via `/whatsapp/{wabaId}/messages`
   - `sendSmart()`: auto-detect text → template fallback
   - `findConversation()`: inbox conversation lookup (unchanged)

3. **`WhatsAppService.php`** — Extended `zernioSend()` with template options
   - `template_name`, `template_language`, `template_variables` options
   - Smart send when template is provided (auto-fallback)
   - `method` field in response (`text` or `template`)

4. **`ZernioSpikeTest.php`** — 11 tests (was 2)
   - sendText: inbox endpoint, no-window error
   - sendTemplate: sandbox rejection, real WABA endpoint, missing WABA ID, API error
   - sendSmart: text path, template fallback, no-conversation-no-template failure
   - status: sandbox sessions, real WABA accounts

### Config needed for real WABA

```env
WHATSAPP_DRIVER=zernio          # flip from 'openwa'
ZERNIO_API_KEY=sk_...           # your Zernio API key
ZERNIO_ACCOUNT_ID=...           # connected WhatsApp account ID
ZERNIO_WABA_ID=...              # WhatsApp Business Account ID
ZERNIO_SANDBOX=false            # switch to real WABA (default: true)
```

## Test evidence (2026-08-15)

1. **Sandbox activation** — `POST /v1/whatsapp/sandbox/sessions` with
   `+961****1562` → session `pending` → user replied → `active` (expires Aug 22).
2. **Raw API send (curl)** — free-form text receipt to the user's phone via
   `POST /v1/inbox/conversations/{id}/messages` → delivered, real
   `wamid.HBgLOTYxNzA3ODE1NjIV...` confirmed by recipient on WhatsApp.
3. **Laravel path** — `php artisan zernio:test +961****1562 --message=...`
   (see run log below) → same inbox endpoint through `Http` facade.
4. **Deterministic unit test** — `tests/Feature/ZernioSpikeTest.php` with
   `Http::fake()` asserts the request shape and the no-window error path.

## End-to-end receipt test (2026-08-15, PASSED)

Full flow through the real Tahseel payment path, with `WHATSAPP_DRIVER=zernio`:

1. Fixture: client "Zernio Test" (#1576, phone +961****1562) + unpaid invoice #17259 ($10).
2. KIRA paid invoice #17259 in Tahseel Hub → invoice `paid`, revenue + payment ref
   `PAY-01M02F4W8BG8DY8ND2JRS389NK`.
3. `PaymentReceiptNotifier` created `whatsapp_message_logs` row #178 (`pending`,
   `template_type=receipt`, full Arabic MegaNet receipt body) and dispatched
   `SendWhatsAppMessage` to the `whatsapp_database` queue.
4. Job → `WhatsAppService::send()` → **Zernio branch** (`zernioSend`) →
   `ZernioService::sendText()` → inbox conversation found (24h window open) →
   Meta Cloud delivered.
5. Log #178 → **`sent`**, no error. Recipient confirmed delivery on WhatsApp
   from the sandbox number.

## Constraints discovered

- **24h window is hard.** Free-form text only works for recipients who have a
  recent conversation (replied in the last 24h). Tahseel's "receipt right after
  payment" fits (if the customer messaged first), but **invoice reminders to
  inactive clients REQUIRE Meta-approved templates** — the single biggest
  behavioral difference vs OpenWA.
- **No conversation = no send.** `findConversation()` returns null → the adapter
  must fall back to template sends.
- **Sandbox caps:** 50 msgs/day, 1 recipient, one active session per user,
  only the `sandbox_start` template allowed for opens.
- **Sender number is foreign** in sandbox (+1 202...); production with a BYO
  Lebanese WABA number would show the real number.

## Phases

- [x] Phase 1: Real WABA upgrade (sendTemplate, sendSmart, config)
- [x] Phase 2a: `payment_receipt_v4` (9 params) — live, approved by Meta
- [x] Phase 2b: Service payment fix (description instead of date)
- [x] Phase 2c: `monthly_reminder_v1` wiring (Spec 018) — code ready-to-fire; template PENDING Meta approval
      - `MonthlyReminderNotifier` builds 5 template vars; `SendWhatsAppMessage` maps
        `template_type='monthly_reminder'` → `config('zernio.reminder_template')`
      - Trigger paths wired (manual-only, no scheduler change):
        * WhatsApp Control Center → Calendar send (`calendarSend`)
        * Settings → WhatsApp → Reminders Preview (`ReminderService::enqueueReminders`)
        * Settings → WhatsApp → Monthly Reminders card (`sendMonthly`)  ← Button 2 wired
      - `.env`: `ZERNIO_REMINDER_TEMPLATE=monthly_reminder_v1` (live switch; inert until Meta approves)
- [ ] Phase 2: Webhook receiver for delivery status (superseded by `ZernioWebhookController`)
- [ ] Phase 3: Dashboard integration (Monitor, templates, driver toggle)
- [ ] Phase 4: End-to-end test (pay → receipt → delivery confirmed)
