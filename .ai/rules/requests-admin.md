---
paths:
  - 'app/Http/Requests/Admin/**'
---

# Requests Admin

## No two open postings share a title
Decided 2026-10-24 at OSAS's request: StoreScholarshipRequest refuses a title while another posting with that title is still status Open (Scholarship::hasOpenPostingTitled). Titles compare ignoring case and extra spaces (Scholarship::normalizedTitle), in PHP, since collapsing spaces has no portable SQL. Once the earlier posting is Closed or Ended, the title can be posted again. UpdateScholarshipRequest applies the same check only when a posting is reopened or renamed, so legacy duplicates that are already open stay editable. There is no lock for titles (the post form disables its button once sent) and no unique index (legacy duplicates exist). A refused preset skips the title check, so the error isn't shown twice.
