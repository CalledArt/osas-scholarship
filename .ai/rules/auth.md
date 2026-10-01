---
paths:
  - 'app/Http/Controllers/Auth/**'
---

# Auth

## Student registration starts with verified CvSU Google identity
Students sign in only through GoogleSignInController. Verify the Google ID token server-side (signature, aud, iss, expiry, email_verified), require an exact @cvsu.edu.ph email and signed hd=cvsu.edu.ph, and identify accounts by Google sub. New students receive a 15-minute server-side pending registration session; /register GET and POST require it. Create a passwordless student account only after validated student details are submitted, using email, google_id and role from server-controlled values. Existing students sign in directly. There is no roster import or access list: the registration form is what puts a student on the admin Student Roster. Never allow student password login, arbitrary email edits, or Google login into a staff account. Data is stored via the existing Laravel User model in the Supabase PostgreSQL users table, not Supabase Auth. Tests use signed tokens with a local JWKS handler.
