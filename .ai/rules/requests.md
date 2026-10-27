---
paths:
  - 'app/Http/Requests/**'
---

# Requests

## Courses come from App\Support\CvsuPrograms
Registration only accepts a course from CvsuPrograms::all() (grouped by college for the dropdown's optgroups). Edit Student Data accepts the list plus the student's current saved course, so pre-list free-text values (e.g. "Bachelor of Science In Information Technology") survive unrelated edits and show as "(not in the program list)" until OSAS recodes them. Add or rename programs in CvsuPrograms, not in the views. program_years allows 2–6 because Doctor of Veterinary Medicine is a 6-year program.

## Application documents arrive as staged-upload tokens
StoreScholarshipApplicationRequest takes each document as uploads.{id} (token of a file already PUT to scholarship-applications/staging/{user}/, the normal path) or documents.{id} (plain file, the no-JavaScript fallback). A presigned PUT binds neither size nor type, so StagedDocumentUpload re-checks owner, document, expiry, size and the real type from the file's first bytes; never trust the object's metadata. The controller moves accepted files out of staging; uploads:prune-staged deletes abandoned ones, and nothing on Vercel schedules it.

## Student numbers are exactly 9 digits
StudentRegistrationRequest and UpdateStudentRequest validate student_number with digits:9 (decided 2026-10-24): numbers only, no dashes or spaces, e.g. 202212345. The inputs on register and Edit Student Data use the same limit (inputmode numeric, pattern [0-9]{9}) and strip anything but digits as it is typed or pasted (an Alpine x-on:input filter on the input itself, since the register page has no x-data). User::yearOfStudy() reads the entry year from the first four digits. The pass list CSV matcher still accepts any format, because it only compares against stored numbers. Contact numbers follow the same pattern with digits:11 (e.g. 09171234567): StudentRegistrationRequest and ProfileUpdateRequest (students only) validate them, and the register and Profile inputs use the same digit filter. Numbers saved earlier in +63 format stay as stored until the student next saves their Profile.
