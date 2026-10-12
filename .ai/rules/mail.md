---
paths:
  - 'app/Mail/**'
---

# Mail

## Keep mailables synchronous — nothing runs a queue worker
Do not add `implements ShouldQueue` to a mailable here. QUEUE_CONNECTION is `database`, but the app runs on Vercel functions and no worker process exists, so a queued message is written to the `jobs` table and never sent. Anything carrying a credential (a password-reset link, say) has a second reason to stay inline: a queued payload would leave it sitting in `jobs` in plaintext.

Because delivery happens inside the request, wrap the send in try/catch and report the outcome instead of letting it abort the caller, so a refused SMTP connection costs one notification rather than the whole request.

## Mail to many students goes through BccMailer
Posting a scholarship emails every student (ScholarshipPosted). Uploading a pass list emails approved and rejected applicants (ScholarshipApplicationApproved / ScholarshipApplicationRejected). Applicants left pending get no email. These sends go through App\Support\BccMailer, which uses Bcc batches of 50 addressed To the from-address. It stops at the first failed batch and returns how many addresses it reached; the controller flashes an `error` when that count is short. Do not loop Mail::to() once per student: mail is sent inline, so per-student SMTP makes the admin's request scale with the student count. These mailables are shared copies with no per-student greeting. Gmail SMTP counts every Bcc address toward its daily limit (about 500 a day for a free account).
