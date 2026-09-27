---
paths:
  - 'app/Mail/**'
---

# Mail

## Keep mailables synchronous — nothing runs a queue worker
Do not add `implements ShouldQueue` to a mailable here. QUEUE_CONNECTION is `database`, but the app runs on Vercel functions and no worker process exists, so a queued message is written to the `jobs` table and never sent. Anything carrying a credential (a password-reset link, say) has a second reason to stay inline: a queued payload would leave it sitting in `jobs` in plaintext.

Because delivery happens inside the request, wrap the send in try/catch and report the outcome instead of letting it abort the caller, so a refused SMTP connection costs one notification rather than the whole request.
