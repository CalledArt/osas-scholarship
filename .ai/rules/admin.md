---
paths:
  - 'app/Http/Controllers/Admin/**'
---

# Admin

## The pass list is the only way to decide applications
OSAS decides scholarship applications only by uploading a pass list CSV (DecideScholarshipApplicationsFromCsv via importDecisions): matched pending applicants are approved up to remaining slots, every other pending applicant is rejected. There is deliberately no per-applicant Approve/Reject button, route, or policy ability — do not add one back. Ambiguous name matches stay pending and are resolved by re-uploading the pass list with student numbers.

## The Student Roster is registered students, not an import
The admin Student Roster page lists users with the Student role, read-only, with search. Students join it by completing the registration form after CvSU Google sign-in; OSAS does not upload a roster CSV. The old CSV importer, template download, StudentRosterEntry model and remove-entry action were deliberately removed — do not add them back. The student_roster_entries table may still exist in the database but nothing reads it.
