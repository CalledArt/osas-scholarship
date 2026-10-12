---
paths:
  - 'app/Http/Controllers/Admin/**'
---

# Admin

## The pass list is the only way to decide applications
OSAS decides scholarship applications only by uploading a pass list CSV (DecideScholarshipApplicationsFromCsv via importDecisions): matched pending applicants are approved up to remaining slots, every other pending applicant is rejected. There is deliberately no per-applicant Approve/Reject button, route, or policy ability — do not add one back. Ambiguous name matches stay pending and are resolved by re-uploading the pass list with student numbers.

## The Student Roster is registered students, not an import
The admin Student Roster page lists users with the Student role, read-only, with search. Students join it by completing the registration form after CvSU Google sign-in; OSAS does not upload a roster CSV. The old CSV importer, template download, StudentRosterEntry model and remove-entry action were deliberately removed — do not add them back. The student_roster_entries table may still exist in the database but nothing reads it.

## Student edits live in Edit Student Data, not the roster
OSAS corrects student details on the separate "Edit Student Data" section (Admin\StudentController index/edit/update + UpdateStudentRequest). The Search Student roster stays read-only — do not add edit links or forms to it. Only student accounts are editable there (staff get 403). Email, google_id and role are never accepted: the email is the student's verified Google identity. Course and year level are separate fields and separate table columns everywhere; do not merge them back into one "Course / year" cell.
