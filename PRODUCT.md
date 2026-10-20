# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Two roles at the Office of Student Affairs and Services (OSAS) of Cavite State University's Main Campus in Indang, serving undergraduates across its colleges (CAFENR, CAS, CCJ, CEMDS, CED, CEIT, CON, and the rest listed in `App\Support\CvsuPrograms`):

- **OSAS admin staff** post and manage scholarships, attach required documents and sample copies, decide each scholarship's applicants by uploading a pass list, look students up on the roster, and correct student details.
- **Students** sign in with their @cvsu.edu.ph Google account, register their academic details once, browse the scholarships open to them, apply with the required supporting documents, and follow their applications to a decision.

## Product Purpose

Digitizes the Main Campus scholarship cycle end to end. OSAS posts a scholarship with its slots, deadline, and required documents. Every student is emailed, students apply and upload, and OSAS settles the whole cohort with one pass list. Approved and rejected applicants are then emailed. It replaces a paper process with one where both sides can see where every application stands.

## Positioning

It is not a generic form builder. It models this office's scholarship rules directly: slots enforced against approved counts, computed availability, a per-student holding limit, RA 10931 coverage computed from the student number and program length, and decision-by-pass-list, which mirrors how OSAS already publishes results. A neighboring tool would have to rebuild those rules to fit this office.

## Operating Context

- **Standing:** deployed and in a pilot with the Main Campus OSAS. It is not yet the office's official process.
- **Student sign-in and registration:** students sign in only with Sign in with Google. The server verifies the ID token and requires an exact `@cvsu.edu.ph` address on the signed `cvsu.edu.ph` hosted domain. A first-time student gets a 15-minute pending registration and fills in name parts, student number, course (from the CvSU program list), year level, program length, date of birth, contact number, and address. Only then is a passwordless account created, keyed by Google's `sub`. There is no access list or roster import: registering is what puts a student on the roster. Students can never use the password form, and Google sign-in never reaches a staff account.
- **Staff accounts:** staff accounts are created with `php artisan users:create`, which sets a temporary password and forces a change on first sign-in. Staff sign in with email and password.
- **Posting:** an admin creates a scholarship with a title, description, slots, deadline, and status (Open, Closed, Ended), plus its required documents. Each document can carry an optional sample (JPG, PNG, or PDF up to 5 MB) that students can preview and download. Posting emails every registered student.
- **Applying:** a student sees Active scholarships first, then Available to apply. The Available list is hidden once they reach their limit, and Closed scholarships they never applied to are not shown. To apply, they upload one file per required document (JPG, PNG, or PDF up to 5 MB).
- **Deciding:** a pass list CSV is the only way to decide applications. Each row is matched by student number first, then by name. Matched pending applicants are approved up to the remaining slots, and every other pending applicant is rejected. Ambiguous name matches stay pending until OSAS uploads a pass list with student numbers. Applicants are emailed their result, and anyone left pending gets no email. There is no per-applicant Approve or Reject control, by design.
- **Roster and corrections:** the Student Roster is a read-only list of registered students, with search, course and year filters, and sort options. Corrections happen in a separate Edit Student Data section. It covers student accounts only and never changes email, Google ID, or role.
- **Practice tour:** each role gets a practice tour on sample screens on its first visit, and either role can replay it later. The tour can never touch real records.
- **Email:** email goes out over Gmail SMTP, inside the request, in Bcc batches of 50. A free Gmail account allows about 500 recipients a day.

## Capabilities and Constraints

- Roles are `admin` and `student` (`UserRole`), enforced by `role:` route middleware.
- **Availability is computed, not stored:** a scholarship is open only when its status is Open, its deadline hasn't passed, and its remaining slots (slots minus approved applications) are above zero. If it is not open, the first failing reason is shown: Closed by OSAS, Past deadline, or Full.
- **Scholarship limit:** a student may hold at most 2 scholarships (`User::SCHOLARSHIP_LIMIT`). Pending and approved applications both count, and rejected ones never do. Setting a scholarship to Ended (grant period over) frees the place. Closed only stops new applications, so approvals under it still count as Active.
- **RA 10931 (free tertiary education):** shown as a fixed row at the top of a student's Active scholarships while their year of study is within program length + 1, per Section 6(a)(3). Year of study is counted from the entry year that starts the student number, with school years opening in August. It is not a scholarship record or an application, and it never counts toward the limit. Only OSAS sets `program_years` after registration.
- **Program list:** courses come from `App\Support\CvsuPrograms`, grouped by college. Program length runs 2 to 6 years, since Doctor of Veterinary Medicine takes 6.
- **Stack:** Laravel (PHP 8.4), Blade, Tailwind CSS, and Alpine.js. It is deployed on Vercel in `hnd1`, with Supabase Postgres and Supabase Storage in `ap-northeast-1`. Each database round trip costs about 240 ms, so page speed comes down to how many queries a page makes. A new screen should reuse queries it already ran, not add one per row.
- **No queue worker:** all mail and work runs synchronously inside the request.
- **Not decided:** no accreditation or compliance requirements, accessibility standard, or rollout to other campuses has been established.

## Brand Commitments

- The system carries Cavite State University's name and seal and the OSAS office name, because it is built for and being trialed with that office.
- Because this is a pilot, no screen, email, or document may describe it as the university's official or endorsed process until OSAS adopts it.

## Evidence on Hand

- The full text of Republic Act No. 10931 (`resources/views/scholarships/republic-act-10931.blade.php`), transcribed from the signed Official Gazette copy. Correct it only against that copy, because common online versions drop words and reverse Section 11.
- The CvSU Main Campus undergraduate program list, by college (`app/Support/CvsuPrograms.php`).
- There are no usage figures, adoption numbers, testimonials, or OSAS statements. Future work must not invent any.

## Product Principles

1. Model this office's real rules (slots, deadlines, the holding limit, RA 10931 coverage, pass-list decisions) instead of offering a generic form builder.
2. Keep each role scoped to its job: OSAS works at cohort scale, by posting to everyone and deciding with one pass list, and students act only on their own applications.
3. Treat eligibility and status as computed truth. The UI shows what the same checks that gate an action would decide, never a stored label that can drift.
4. Make where things stand obvious at every step, so students don't have to chase OSAS for answers that are already in the system.
5. Protect students' personal documents and identity: verified CvSU accounts only, no self-edits of identity fields, and stored files deleted with the scholarship that collected them.
