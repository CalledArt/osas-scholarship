---
paths:
  - 'app/Http/Controllers/Auth/**'
---

# Auth

## Students authenticate only through Google + roster
Students sign in only via GoogleSignInController: the Google ID token is verified server-side by GoogleIdTokenVerifier (google/auth AccessToken::verify, which checks signature, aud, iss and exp; phpseclib v3 is required for RS256), and the verified email must be on student_roster_entries at every sign-in. Identity is Google's `sub` (users.google_id), never an email posted by the browser. Do not reopen password login, self-registration, or profile email edits for students — each would bypass the roster check. Google sign-in must never log into or create a non-student account. Tests sign tokens with a per-test RSA key and hand AccessToken a local JWKS handler, so the real verification code runs.
