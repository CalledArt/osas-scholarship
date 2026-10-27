---
paths:
  - resources/views/admin/students/index.blade.php
---

# Students

## Edit Student Data list is drawn like Search Student
Decided 2026-10-25 at the user's request: admin.students.index takes the Search Student design (see roster.md): x-welcome-banner ("Edit Student Data", "Find a student to correct their details or mark them on leave of absence.") at max-w-[100rem], the search in one row, and the students in a rounded-lg bordered panel with a linen-sunk sticky header, name over email (no initials discs; removed at the user's request), Student no., Course, Year level, Status (LOA badge) and an outlined Edit button. From lg the page fits the screen and only the list scrolls; keep the wrapper `relative`, and keep the course and year-level cells ending right at </td> (StudentManagementTest asserts it). admin.students.edit follows too (same date): the banner headed by the student's name over the old intro sentence, then one rounded-lg card whose sections (Name, Enrollment, Student status) put their title in a 10rem left column from lg, with Cancel and Save Changes in a linen-sunk bar at the card's foot. Enrollment is a 4-column grid from xl: email (2 columns, so a long CvSU address isn't cut off), student number, year level, then course (2) and program length (2). Every field's name, value and hint is unchanged; the tests assert them.
