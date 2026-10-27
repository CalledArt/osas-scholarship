---
paths:
  - resources/views/auth/login.blade.php
---

# Views Auth

## Sign-in form is one card with a mint header
Decided 2026-10-25 at the user's request: the sign-in form sits in one rounded-lg bordered white card (shadow-seat) inside the guest layout's right column. Its header strip uses the welcome banners' seal-wash with a seal-tint border, a lock in a round seal-tint disc, the "Sign in" h2 and the "Students sign in or register…" line (this is the one other place seal-wash appears besides x-welcome-banner). The card body holds the unchanged password form, an "or" rule (aria-hidden), and the Google sign-up button. Nothing sits beneath the card: the local-only Test accounts box was removed 2026-10-27 at the user's request. Only login.blade.php changed; the guest layout and the other auth pages did not.
