# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Two roles at a single college's Office of Student Affairs and Services (OSAS):

- **OSAS admin staff** — post and manage scholarships, review applications, approve/reject decisions, import bulk decisions, and maintain the student roster that controls who may sign in.
- **Students** — enrolled students on the OSAS roster who sign in with their CvSU Google account, browse open scholarships, apply with required supporting documents, and track their application status.

## Product Purpose

Digitizes a single college's scholarship administration cycle end to end: OSAS posts a scholarship (with slots, deadline, required documents), students apply and upload documents, and OSAS reviews and decides. Exists to replace a manual/paper-based process with a faster, more transparent cycle for both sides.

## Positioning

Not a generic forms/CMS tool — it models the specific scholarship lifecycle (slots, deadlines, required-document sets per scholarship, approval counts against slot limits) and the two-role workflow (post → apply → review → decide) native to one institution's OSAS office, including bulk decision import for handling a full cohort at once.

## Operating Context

- Admin: creates a scholarship (title, description, slots, deadline, status), attaches required documents, reviews submitted applications per scholarship, updates application status, and can import decisions in bulk. Also maintains the student roster (`admin/roster`: CSV upload with a downloadable template, search, remove).
- Student: views open scholarships, applies to one (subject to deadline/slot/status eligibility via `isOpenForApplications()`), uploads required documents, and views their own applications and status.
- Students sign in only through Sign in with Google (Google Identity Services). The server verifies Google's ID token with Google's own library and admits the student only if the verified email is on the roster, checked at every sign-in; the account is created from the roster row on first sign-in and keyed by Google's `sub`. Students have no password, and the password form refuses them. Google sign-in never grants admin. Admin accounts are provisioned with `users:create`, which issues a generated password and forces a change on first sign-in (`must_change_password`).
- Application statuses and scholarship statuses are enum-driven (`ApplicationStatus`, `ScholarshipStatus`); slots are enforced against the count of approved applications, not just submitted ones.

## Capabilities and Constraints

- Roles: `admin` and `student` (`UserRole` enum), enforced via `role:admin` / `role:student` route middleware.
- A scholarship has: title, description, slot count, deadline, status, a set of required documents, and applications.
- Eligibility to apply is computed, not just status-based: open status AND deadline not passed AND remaining slots > 0.
- Students carry academic profile fields (student number, course, year level) alongside contact info and date of birth.
- Stack: Laravel (PHP 8.4) + Blade + Tailwind CSS + Alpine.js (existing codebase; not a greenfield choice to make).
- No confirmed constraints beyond what the code establishes (no accreditation/compliance rules, fixed document types, or existing branding were specified).

## Product Principles

1. Model the real scholarship lifecycle (slots, deadlines, required documents, approval-gated slot counts), not a generic form builder.
2. Keep the admin and student experiences distinctly scoped to their role's job — admins administer at cohort scale (bulk decision import), students act on their own applications.
3. Favor transparency and speed over the paper process it replaces: clear status at every step, less back-and-forth between OSAS and applicants.
4. Treat eligibility and status as computed truth (deadlines, slots, approval counts), never a value the UI just displays without checking.
