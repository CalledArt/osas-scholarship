---
paths:
  - 'app/Mail/**'
---

# Mail

## Keep mailables synchronous — nothing runs a queue worker
Do not add `implements ShouldQueue` to a mailable here. QUEUE_CONNECTION is `database`, but the app runs on Vercel functions and no worker process exists, so a queued message is written to the `jobs` table and never sent. Anything carrying a credential (StudentAccountCreated ships a one-time password) has a second reason to stay inline: a queued payload would leave that password sitting in `jobs` in plaintext.

Because delivery happens inside the request, wrap the send in try/catch and report the outcome instead of letting it abort the caller — ImportStudentsFromCsv does this and returns `emailed` per row, so a refused SMTP connection costs one notification, not the batch.
