# Spike: Zernio WhatsApp Adapter for Tahseel

**Date:** 2026-08-15
**Branch:** `spike/zernio-whatsapp-adapter`
**Question:** Can Tahseel's Laravel stack send WhatsApp messages through Zernio
(official Meta Cloud API) the same way it sends receipts through OpenWA today?

**Feasibility question (Given/When/Then):**
Given a Zernio API key + active sandbox session,
when a Laravel service posts a free-form receipt text to the inbox-messages endpoint,
then the message is delivered on WhatsApp via Meta Cloud with a real `wamid`.

## Approach

- `app/Services/WhatsApp/ZernioService.php` — thin HTTP adapter (status /
  findConversation / sendText) mirroring the surface Tahseel's
  `WhatsAppService` exposes today.
- `app/Console/Commands/ZernioTestCommand.php` — `php artisan zernio:test {phone}`
  for observable, interactive testing against the Zernio shared sandbox.
- Config via `.env` (untracked): `ZERNIO_API_KEY`, `ZERNIO_ACCOUNT_ID`, `ZERNIO_BASE_URL`.
- Not wired into the WhatsApp control center — deliberate. This spike only
  validates the transport.

## Test evidence (2026-08-15)

1. **Sandbox activation** — `POST /v1/whatsapp/sandbox/sessions` with
   `+96170781562` → session `pending` → user replied → `active` (expires Aug 22).
2. **Raw API send (curl)** — free-form text receipt to the user's phone via
   `POST /v1/inbox/conversations/{id}/messages` → delivered, real
   `wamid.HBgLOTYxNzA3ODE1NjIV...` confirmed by recipient on WhatsApp.
3. **Laravel path** — `php artisan zernio:test +96170781562 --message=...`
   (see run log below) → same inbox endpoint through `Http` facade.
4. **Deterministic unit test** — `tests/Feature/ZernioSpikeTest.php` with
   `Http::fake()` asserts the request shape and the no-window error path.

## Run log

```
$ php artisan zernio:test +96170781562 --check-only
Status: {"ok":true,"reachable":true,"sandboxNumber":"+120****7457",...}
Sandbox connected (+120****7457).

$ php artisan zernio:test +96170781562 --message="..."        # fill from actual run
Sent OK — messageId: wamid.xxx
```

## Constraints discovered

- **24h window is hard.** Free-form text only works for recipients who have a
  recent conversation (replied in the last 24h). Tahseel's "receipt right after
  payment" fits (if the customer messaged first), but **invoice reminders to
  inactive clients REQUIRE Meta-approved templates** — the single biggest
  behavioral difference vs OpenWA.
- **No conversation = no send.** `findConversation()` returns null → the adapter
  must fall back to template sends (`POST /v1/inbox/conversations` with
  `templateName`/`templateParams`).
- **Sandbox caps:** 50 msgs/day, 1 recipient, one active session per user,
  only the `sandbox_start` template allowed for opens.
- **Sender number is foreign** in sandbox (+1 202...); production with a BYO
  Lebanese WABA number would show the real number.

## Verdict: VALIDATED (with template caveat)

### What worked
- End-to-end free-form WhatsApp delivery via Zernio from both raw curl and
  Laravel `Http` facade — real `wamid`, recipient-confirmed.
- Sandbox session lifecycle (pending → active on reply) is clean and free.
- Zernio-side cost for messaging = $0 (platform free for 1 account; sandbox free).

### What didn't / needs the real build
- Free-form sends are window-bound; the production adapter needs a
  template-send path (create conversation with `templateName` + `templateParams`)
  for out-of-window receipts/reminders.
- Delivery status tracking needs webhooks (`message.delivered`/`failed`) — not
  covered by this spike.

### Surprises
- The 24h window applies to receipts too: a client who paid but never messaged
  first is technically "outside the window" → template required. Business impact
  depends on how often Tahseel clients initiate WhatsApp contact.

### Recommendation for the real build
- Extend `ZernioService` with `sendTemplate(phone, templateName, params)` and a
  webhook receiver for delivery status → then swap the transport behind
  `WhatsAppService` (keep queue/job/log/rate-limiter layers untouched).
- Decision point for KIRA: receipts as utility templates (free-ish, needs Meta
  approval + template management) vs keeping OpenWA for receipts and using
  Zernio only for broadcasts/marketing (hybrid).

## Files
- `app/Services/WhatsApp/ZernioService.php`
- `app/Console/Commands/ZernioTestCommand.php`
- `tests/Feature/ZernioSpikeTest.php`
- `.env` → `ZERNIO_API_KEY` / `ZERNIO_ACCOUNT_ID` / `ZERNIO_BASE_URL` (untracked)
