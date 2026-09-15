---
paths:
  - 'app/Mail/**'
---

# Mail

## Keep mailables synchronous — nothing runs a queue worker
Do not add `implements ShouldQueue` to a mailable here. QUEUE_CONNECTION is `database`, but the app runs on Vercel functions and no worker process exists, so a queued message is written to the `jobs` table and never sent. Anything carrying a credential (StudentAccountCreated ships a one-time password) has a second reason to stay inline: a queued payload would leave that password sitting in `jobs` in plaintext.

Because delivery happens inside the request, wrap the send in try/catch and report the outcome instead of letting it abort the caller — ImportStudentsFromCsv does this and returns `emailed` per row, so a refused SMTP connection costs one notification, not the batch.

## Keep mailables synchronous — nothing runs a queue worker
Do not add `implements ShouldQueue` to a mailable. QUEUE_CONNECTION is `database`, but the app runs on Vercel functions and no worker process exists, so a queued message is written to the `jobs` table and never sent.

Because delivery then happens inside the request, wrap any send in try/catch and report the outcome instead of letting a refused SMTP connection abort the caller.

As of the move to student self-registration, `app/Mail/` is empty: signup sends nothing (the `@cvsu.edu.ph` rule in RegisterRequest stands in for verification, and accounts are created `email_verified_at => now()`). The only mail the app can still send is Laravel's own password-reset notification, so `MAIL_*` only needs real SMTP credentials when "Forgot your password?" must work; `MAIL_MAILER=log` is fine otherwise.
