# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Two roles at a single college's Office of Student Affairs and Services (OSAS):

- **OSAS admin staff** — post and manage scholarships, review applications, approve/reject decisions, import students and bulk decisions.
- **Students** — enrolled students who browse open scholarships, apply with required supporting documents, and track their application status.

## Product Purpose

Digitizes a single college's scholarship administration cycle end to end: OSAS posts a scholarship (with slots, deadline, required documents), students apply and upload documents, and OSAS reviews and decides. Exists to replace a manual/paper-based process with a faster, more transparent cycle for both sides.

## Positioning

Not a generic forms/CMS tool — it models the specific scholarship lifecycle (slots, deadlines, required-document sets per scholarship, approval counts against slot limits) and the two-role workflow (post → apply → review → decide) native to one institution's OSAS office, including bulk student import and bulk decision import for handling a full cohort at once.

## Operating Context

- Admin: creates a scholarship (title, description, slots, deadline, status), attaches required documents, reviews submitted applications per scholarship, updates application status, and can import decisions in bulk. Also imports/manages the student roster (`students/import`, with a downloadable template).
- Student: views open scholarships, applies to one (subject to deadline/slot/status eligibility via `isOpenForApplications()`), uploads required documents, and views their own applications and status.
- Auth includes a forced password-change flow (`must_change_password`), suggesting admin-provisioned/imported student accounts rather than open self-registration.
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
2. Keep the admin and student experiences distinctly scoped to their role's job — admins administer at cohort scale (bulk import), students act on their own applications.
3. Favor transparency and speed over the paper process it replaces: clear status at every step, less back-and-forth between OSAS and applicants.
4. Treat eligibility and status as computed truth (deadlines, slots, approval counts), never a value the UI just displays without checking.
