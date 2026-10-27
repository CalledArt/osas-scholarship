---
paths:
  - 'app/Mail/**'
---

# Mail

## Keep mailables synchronous — nothing runs a queue worker
Do not add `implements ShouldQueue` to a mailable here. QUEUE_CONNECTION is `database`, but the app runs on Vercel functions and no worker process exists, so a queued message is written to the `jobs` table and never sent. Anything carrying a credential (a password-reset link, say) has a second reason to stay inline: a queued payload would leave it sitting in `jobs` in plaintext.

Because delivery happens inside the request, wrap the send in try/catch and report the outcome instead of letting it abort the caller, so a refused SMTP connection costs one notification rather than the whole request.

## Mail to many students goes through StudentMailer, one message per student
Posting a scholarship sends no email (decided 2026-10-24): students are told in the in-app bell instead (App\Notifications\ScholarshipPosted via StudentNotifier). Do not add the posting email back. Uploading a pass list emails approved and rejected applicants (ScholarshipApplicationApproved / ScholarshipApplicationRejected). Applicants left pending get no email. These sends go through App\Support\StudentMailer, which sends each student their own message with their address in To. Do not go back to Bcc batches addressed To the from-address: decided 2026-10-21 after OSAS saw every notification "sent to" the sender's Gmail and not to the applicant. It stops at the first failed message and returns how many students it reached; the controller flashes an `error` when that count is short. Mail is sent inline, so the admin's request grows with the student count. StudentMailer restarts PHP's time limit (30s in production) before each message, leaving Vercel's 300s function timeout as the real cap. The mailables share their content, with no per-student greeting. Gmail SMTP counts every message toward its daily limit (about 500 a day for a free account).
