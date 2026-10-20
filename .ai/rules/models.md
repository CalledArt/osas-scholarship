---
paths:
  - 'app/Models/**'
---

# Models

## Pending and approved applications both hold a scholarship slot
A scholarship is Full (stops taking applications) once pending + approved applications reach its slots; a rejection gives the slot back. ScholarshipApplication::holdingSlot() defines this; list pages eager-load it as `held_slots_count` (withCount) so availability() costs no per-row query. The pass list (DecideScholarshipApplicationsFromCsv) is the exception: it measures room as slots minus approvals only, since it is deciding those pending applicants. ScholarshipApplicationController::store locks the scholarship row and re-counts, so concurrent submissions can't overfill the last slot. ScholarshipFactory defaults slots to 10–20 so a default scholarship with a few pending applicants never reads as Full in tests.
