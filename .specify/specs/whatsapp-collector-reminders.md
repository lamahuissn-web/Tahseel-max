# WhatsApp Collector Reminder MVP

## Purpose
Help Tahseel admins remind field collectors which customers they should visit today to collect due or overdue bills.

## Safety Constraints
- No new database tables.
- No migrations.
- Do not modify invoice, payment, or customer records.
- Collector assignments are configuration-only for this MVP.
- Messages must go through the existing WhatsApp Queue lifecycle whenever possible.
- Admin must be able to preview collector reminders before sending.

## Collector Assignment Model
Collector assignment is based on markers inside the customer name.

Example:
- `W.K` => `قاسم محمد عامر`
- `W.Y` => `ياسر الزللي`

Each collector rule supports multiple markers because real customer names may use different marker formats.

Example config item:
```json
{
  "name": "قاسم محمد عامر",
  "phone": "+961...",
  "markers": ["W.K", "WK"],
  "active": true
}
```

## Storage
Use the existing `app_config` table only.

Suggested keys:
- `whatsapp_collector_rules` JSON array
- `whatsapp_collector_reminders_enabled` boolean string
- `whatsapp_collector_reminders_include_overdue` boolean string
- `whatsapp_collector_reminders_skip_empty` boolean string

## Matching Rules
- Match active collector markers against customer names.
- English marker matching is case-insensitive.
- Marker input accepts comma, Arabic comma, semicolon, spaces between marker-like codes, or new lines.
- The UI shows marker suggestions detected from existing customer names so admins can click markers instead of retyping them.
- A customer with no matching marker is ignored for sending but shown in preview summary if it has due bills.
- A customer matching multiple collectors is a conflict and must not be auto-sent until fixed.

## Invoice Eligibility
A customer appears in a collector reminder when they have unpaid/partial invoices where:
- `due_date <= today` for due/overdue mode
- invoice is not soft deleted
- customer is not soft deleted

## Performance
- The collectors page must not run per-customer invoice eligibility queries during initial render.
- Preview must use grouped invoice/customer queries so `/admin/whatsapp/collectors` remains fast as customer count grows.

## WhatsApp Message
Each collector receives one grouped message.

Message contains:
- greeting with collector name
- count of customers
- total amount expected
- customer list with name, phone, due date, amount, and overdue hint

## UI
Add a new WhatsApp Control Center tab: `Collectors`.

Sections:
1. Collector Rules
   - collector name
   - WhatsApp phone
   - comma-separated markers
   - active yes/no
2. Preview Today
   - collector grouped counts/totals
   - unmatched/conflict counts
3. Send Now
   - sends reminders through queue

## Queue/Log Source Label
Use source label:
- `Collector Reminder`

Suggested `sent_by` prefix:
- `system:collector_reminder|batch:{uuid}`

## Phase 1 Scope
- Config-only rules UI
- Preview today
- Send now via queue
- Source label support

## Out of Scope for Phase 1
- No new collector table
- No customer collector_id
- No automatic daily scheduler yet
- No collector performance report yet
