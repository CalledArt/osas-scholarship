---
paths:
  - 'app/Http/Controllers/Admin/**'
---

# Admin

## The pass list is the only way to decide applications
OSAS decides scholarship applications only by uploading a pass list CSV (DecideScholarshipApplicationsFromCsv via importDecisions): matched pending applicants are approved up to remaining slots, every other pending applicant is rejected. There is deliberately no per-applicant Approve/Reject button, route, or policy ability — do not add one back. Ambiguous name matches stay pending and are resolved by re-uploading the pass list with student numbers. The student number is the key (decided 2026-10-24): a row with a student number matches only that number and its name is never used, so a wrong number with a right name is unmatched and the applicant is rejected. That was the bug: names from numbered rows used to approve. Name matching applies only to rows with no number.

## The Student Roster is registered students, not an import
The admin Student Roster page lists users with the Student role, read-only, with search. Students join it by completing the registration form after CvSU Google sign-in; OSAS does not upload a roster CSV. The old CSV importer, template download, StudentRosterEntry model and remove-entry action were deliberately removed — do not add them back. The student_roster_entries table may still exist in the database but nothing reads it.

## Student edits live in Edit Student Data, not the roster
OSAS corrects student details on the separate "Edit Student Data" section (Admin\StudentController index/edit/update + UpdateStudentRequest). The Search Student roster stays read-only — do not add edit links or forms to it. Only student accounts are editable there (staff get 403). Email, google_id and role are never accepted: the email is the student's verified Google identity. Course and year level are separate fields and separate table columns everywhere; do not merge them back into one "Course / year" cell.

## Students' personal details are hidden from OSAS
A student's date of birth, contact number and address belong to the student: they set them at registration and edit them on their own Profile (ProfileUpdateRequest, students only; the fields may be omitted but never cleared). OSAS must neither see nor edit them. Edit Student Data does not show them and UpdateStudentRequest has no rules for them, so posted values are ignored; the scholarship applicant list shows no age, since age is derived from the date of birth. OSAS still edits name, student number, course, year level and program length. Do not add these fields, or anything derived from them, to admin pages, exports or emails to staff.

## Fund Allocation splits the fund equally; payment is signed for on paper
Fund Allocation (FundAllocationController, admin.fund-allocation.*; called Billing until 2026-10-24) lists a scholarship's approved applicants. OSAS enters total_funds only there (ScholarshipFundController, PUT admin.fund-allocation.funds.update), where the form previews total / passed count live (decided 2026-10-21); the Post and Edit Scholarship forms have no fund field and ignore a posted one. UpdateScholarshipFundsRequest refuses a total below one centavo per passed student (the sheet would list ₱0.00). Each is owed Scholarship::total_funds / approved count, rounded down to the centavo (Scholarship::fundShareInCentavos); the leftover centavos stay in the fund. Do all money math in integer centavos via App\Support\Peso, never floats. The "Mark as released" feature (FundReleaseController, BillingSummary, the released-floor and short-of-funds checks) was deliberately removed on 2026-10-24: students sign the downloaded Excel sheet beside the Amount Received instead. Do not add it back. The scholarship_applications.funds_released_amount/_at/_by columns may still exist but nothing reads them.
