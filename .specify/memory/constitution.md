# Tahseel Constitution — WhatsApp Payment Notification

## Core Principles

### I. Non-Blocking Notifications (NON-NEGOTIABLE)
Payment collection MUST always succeed independently of WhatsApp delivery.
If WhatsApp is unreachable, misconfigured, or the client has no phone number,
the payment MUST still be recorded. The notification is fire-and-forget:
log the error, do not roll back the payment.

### II. Full Financial Transparency
Every payment notification MUST include:
- Which invoice was just paid (amount + month/year)
- A clear, itemised list of any REMAINING unpaid invoices with their amounts
- The total outstanding balance
This prevents disputes and gives the client a complete picture in one message.

### III. Graceful Degradation
The feature MUST handle three failure modes without interrupting the user:
1. Client has no WhatsApp number — skip silently
2. OpenWA is unreachable — log warning, payment continues
3. Invalid invoice data — log error, payment continues
No user-facing error messages for WhatsApp failures.

### IV. Single Responsibility
The notification logic MUST live in its own dedicated service/class.
The InvoiceController@pay_invoice method MUST NOT contain WhatsApp logic.
It calls a single method: notifier->notifyPaymentReceipt(invoice).

### V. Message Template Consistency
All WhatsApp payment notifications MUST use a single, centrally defined template.
The template MUST be version-controlled and reviewable. No inline string building
scattered across controllers.

### VI. Observable by Default
Every attempted notification MUST be logged with:
- Whether it was sent or skipped (and why)
- The client ID, invoice ID, and amounts involved
Use Laravel's Log facade at info level for sends and warning for skips.

## Technical Constraints

- **Framework**: Laravel 10. Must use existing WhatsAppService for actual sending.
- **Client WhatsApp number**: From tbl_clients.whatsapp or tbl_clients.phone field.
- **Invoice data**: From tbl_invoices table. Outstanding invoices = unpaid invoices
  for the same client excluding the one just paid.
- **Sending**: Via OpenWA API through WhatsAppService->sendMessage().
- **Existing code**: Do NOT modify InvoiceController@pay_invoice signature or return type.
- **Language**: Messages in Arabic (العربية) with appropriate emojis for readability.

## Governance

- This constitution was written after direct discussion with Kira (the stakeholder)
  on 2026-07-04, capturing his requirements for financial transparency and
  non-blocking payment notifications.
- Changes to notification templates require Kira's approval before deployment.
- All WhatsApp notification code MUST be reviewed against Principles I, II, III before merging.

**Version**: 1.0.0 | **Ratified**: 2026-07-04 | **Last Amended**: 2026-07-04
