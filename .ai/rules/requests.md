---
paths:
  - 'app/Http/Requests/**'
---

# Requests

## Courses come from App\Support\CvsuPrograms
Registration only accepts a course from CvsuPrograms::all() (grouped by college for the dropdown's optgroups). Edit Student Data accepts the list plus the student's current saved course, so pre-list free-text values (e.g. "Bachelor of Science In Information Technology") survive unrelated edits and show as "(not in the program list)" until OSAS recodes them. Add or rename programs in CvsuPrograms, not in the views. program_years allows 2–6 because Doctor of Veterinary Medicine is a 6-year program.
