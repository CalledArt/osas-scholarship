---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Every database round trip costs ~240ms — count queries, not milliseconds
The database is hosted on Supabase in ap-northeast-1 and reached through the pooler. A warm `select 1` measures ~240ms; opening a connection costs ~950ms. Page time is therefore (number of round trips) x 240ms, and almost nothing else — a DB-free page renders in 15ms.

So the only optimisation that moves the needle is removing round trips. Prefer one joined query over two eager loads, and reuse a collection you already loaded instead of re-querying it (the admin dashboard resolves recent applications' scholarships from the register it already holds).

Measured: admin dashboard went 1.93s -> 0.75s by dropping 2 queries, enabling DB_PERSISTENT, and unsetting the pgsql `charset`/`search_path` config keys (each of those made Laravel's connector run an extra SET statement per connection — one full round trip).

Caching is not the lever here: at 240ms per query the file cache already removes ~99.6% of the cost, so Redis would buy under 1ms over it and is not worth a dependency.

## Students hold at most 2 scholarships; Ended frees a place
User::SCHOLARSHIP_LIMIT (2) caps what a student holds. Pending and approved applications both count (so approving everything waiting can never exceed it); rejected ones never count, and nothing counts once OSAS sets the scholarship to ScholarshipStatus::Ended (grant period over). Closed only stops new applications, so approvals on a Closed scholarship are still "Active". ScholarshipApplicationPolicy::create enforces the limit. The student Dashboard and Scholarships page show Active scholarships first, then Available to apply (hidden at the limit). Closed scholarships the student never applied to are deliberately not listed for students. Republic Act 10931 (free tertiary education) heads Active while User::hasFreeTertiaryEducation(): year of study (school years since the 20YY entry year that student numbers start with; SY opens in August, User::SCHOOL_YEAR_START_MONTH; falls back to year_level) must not exceed program_years + 1, per Section 6(a)(3). program_years is set at registration and by OSAS in Edit Student Data, never by students. It is a fixed row (x-free-tertiary-education) linking to the Act's full text at scholarships.republic-act-10931, not a Scholarship record or application, and never counts toward the limit. That text is transcribed from the signed Official Gazette copy; common online copies (e.g. Lawphil) drop words and reverse Section 11, so never re-paste from them; it uses the green approved wash, not gold, which DESIGN.md reserves for pending. Pages compute both from User::scholarshipApplicationsWithScholarship() (one joined query) and remainingScholarshipPlaces(). Don't add a per-row query.
