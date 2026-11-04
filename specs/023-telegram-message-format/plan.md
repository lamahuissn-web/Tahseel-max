# Plan 023: Telegram Notification Message Format

Base: `spike/zernio-whatsapp-adapter` @ `a1d76b3` · Branch: `feature/023-telegram-message-format`

## Design

### 1. `app/Services/Telegram/TelegramMessage.php` (new)

Immutable value object that owns the anatomy from the spec.

```php
TelegramMessage::make('invoice_paid')      // sets emoji + title from the map
    ->field('العميل', $client->name)
    ->field('الفاتورة', '#'.$invoice->number, 'الاستحقاق '.$due.' (+'.$days.' يوم)')
    ->amount($paid, $currency)             // renders "<b>25.00 $</b>"
    ->actor($admin->name)
    ->at(now())
        ->toPayload();                         // ['text' => ..., 'reply_markup' => ...]
```

- `EVENT_META` constant: `event key => ['emoji' => '💵', 'title' => 'دفعة مستلمة']`
  for the fifteen keys in the spec (no per-site wording drift).
- `escape()` = `htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`,
  applied to **every** dynamic value; builder markup is added after escaping.
- Empty/null fields are dropped, never rendered as `null` or `-`.
- `at()` defaults to `now()` in `APP_TIMEZONE`; footer renders `H:i · d/m/Y`.
- `toPayload()` throws a `LogicException` if the title or the text exceeds the
  line budget — a malformed message fails in tests, not in silence on the phone.
- `send()`: resolves `TelegramConfig` (master + event gate), calls the API client,
  logs `warning` + retries once on failure, always returns `bool`, never throws.

### 2. `TelegramApiClient` (extend, do not rewrite)

`sendMessage(string $chatId, string $text, string $parseMode = 'HTML', ?array $replyMarkup = null)`
passes `reply_markup` through. Everything else (getMe, getUpdates, sendDocument,
inline queries) untouched — the backup command depends on `sendDocument`.

### 3. `sendTelegramNotification()` (compatibility shim)

Keeps its current signature so no caller breaks while phases land, and gains one
branch: when handed a `TelegramMessage`, it sends the built payload; when handed
a plain string it keeps today's behaviour **but escapes the string** (so a legacy
caller cannot produce an unparseable message). All eighteen sites are migrated,
so the string branch becomes dead code by the end of phase 3 — it is kept for
third-party/plugin callers and documented as such.

### 4. Call-site migration

Each site becomes: build the facts it already has → `TelegramMessage` → `send()`,
positioned exactly where the current call sits (verified to be after
`DB::commit()` / outside the transaction at every site). Numbers keep two
decimals and the currency from `get_app_config_data('currency')`; wording of the
facts is preserved, layout is not.

### 5. No links (owner decision, 2026-11-04)

The first implementation shipped an inline «فتح السجل» button built from
`route()`. The owner rejected it («بدون أن أذهب إلى أي link»), so the button,
`TelegramMessage::link()`, `recordLink()` and the `reply_markup` parameter on
`TelegramApiClient::sendMessage()` were all removed in the same branch. The
message must stay self-explanatory: ids, names, amounts and dates only.

## Phases

| Phase | Scope | Exit criterion |
|---|---|---|
| 0 | RED tests for `TelegramMessage` (anatomy, escaping, line budget, link, retry) and a fake-bound API client | suite fails for the right reason |
| 1 | Builder + `TelegramApiClient` `reply_markup` + shim; migrate events 1–4 (payments) | payment family renders, suite green |
| 2 | Migrate invoice create/delete/redo/balance events (5–9) + client events (10–13) | suite green |
| 3 | Migrate transfer/expense/admin/overdue (14–18) | suite green |
| 4 | Focused phpunit + full suite + `view:cache`; live message per family to `ttt` | owner sees five correct cards |

## Testing

- `tests/Unit/TelegramMessageTest.php` — anatomy order, escaping (`&`, `<`, `>`,
  emoji, quotes), dropped empty fields, line budget, inline button shape, no
  `null` leakage.
- `tests/Feature/TelegramNotificationFormatTest.php` — one test per event key:
  drive the real controller/service inside a rolled-back transaction with the
  API client faked, assert the captured payload contains the expected title,
  the expected fields and the expected link.
- Failure path: fake a `400` response, assert one retry, one log line, `false`
  return, and that the HTTP response of the originating request is unchanged.
- No test may reach `api.telegram.org`; assert the fake was called instead.

## Risks

- **Message length.** Arabic names are long; a wrapped card looks broken. Mitigate
  with the 40-char value trim (ellipsis) and by asserting the budget in tests.
- **Retry inside a request.** One retry adds up to ~2 s when Telegram is slow.
  Bounded by `Http::timeout(8)` and no retry on 4xx (except 429).
- **Legacy callers.** Any code path outside `app/` that calls the helper with a
  raw string keeps working but is escaped — verify with a grep before release.

## Rollback

Text-only change on one branch; revert the branch or `git revert` the phase
commits. No data migration to undo.
