---
paths:
  - 'app/Models/**'
---

# Models

## Pending and approved applications both hold a scholarship slot
A scholarship is Full (stops taking applications) once pending + approved applications reach its slots; a rejection gives the slot back. ScholarshipApplication::holdingSlot() defines this; list pages eager-load it as `held_slots_count` (withCount) so availability() costs no per-row query. The pass list (DecideScholarshipApplicationsFromCsv) is the exception: it measures room as slots minus approvals only, since it is deciding those pending applicants. ScholarshipApplicationController::store locks the scholarship row and re-counts, so concurrent submissions can't overfill the last slot. ScholarshipFactory defaults slots to 10–20 so a default scholarship with a few pending applicants never reads as Full in tests.

## Voided applications hold no slot and count toward no limit
ApplicationStatus::Voided means OSAS put the student on leave of absence while the application was still pending, so it gave up its slot. Approved applications are never voided. holdingSlot() (pending and approved only) and holdsScholarshipPlace() both leave voided ones out, and so does the approved() scope behind Fund Allocation. Badges show Voided in neutral gray, never gold, since gold means pending/yours. A voided application still blocks applying to the same scholarship again, because of the unique (scholarship_id, user_id) index.

## Deleting a scholarship archives a completed fund allocation
Decided 2026-10-24 at OSAS's request: Scholarship::deleteWithStoredFiles() first calls ScholarshipArchive::recordFor() in one DB transaction with the delete. Storage files are removed only after it commits. "Completed" means a fund total was entered and at least one student passed (fundShareInCentavos is not null). Anything else is deleted without an archive. scholarship_archives copies the scholarship's details, total_funds, share_amount, recipient_count, posted_by and deleted_by; its created_at is the deletion time. scholarship_archive_recipients copies each approved applicant's line as the fund allocation sheet has it (reference, student number, name parts, course, amount), in the sheet's surname order. Everything is copied, not referenced; user ids are nullOnDelete. The archives are database only; there is no page yet.

## Scholarships carry a stored reference number
Decided 2026-10-24 at OSAS's request: scholarships.reference (unique) is "SCH-", the year posted and the id to five digits, e.g. SCH-2026-00012. The prefix keeps it apart from application references (2026-00012), which are computed and never stored. Scholarship::booted() writes it in a created hook right after the insert (the id only exists then), so posting costs one extra query. It is not fillable and never changes, even if the title or year changes. Migration 2026_10_24_112509 backfilled existing rows from created_at and id. ScholarshipArchive copies it as scholarship_reference, and that migration backfilled archives from posted_at and scholarship_id.
