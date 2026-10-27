---
paths:
  - 'app/Support/**'
  - app/Support/FundAllocationSheet.php
---

# Support

## STEM-only scholarships use CvsuPrograms::STEM, taken from DOST-SEI's list
A posting with stem_courses_only admits (policy), lists (Scholarship::availableTo) and emails only students whose registered course is in CvsuPrograms::STEM. That list is the CvSU programs whose names exactly match the "S&T Undergraduate Scholarship Priority Fields" in the DOST-SEI 2026 brochure (science-scholarships.ph). Near matches (Agricultural Entrepreneurship, Exercise and Sports Sciences, BSEd/BEEd/BTLEd without a recorded major) are deliberately excluded; recheck against the new brochure each year. Only the DOST preset can turn the limit on (decided 2026-10-21): the Post Scholarship form posts `preset`, the store action sets stem_courses_only from ScholarshipPresets::limitsToStemCourses(), and the client cannot send stem_courses_only. There is no STEM checkbox on the post or edit form, and editing never changes the limit. Never check eligibility by scholarship title, since OSAS may reword it. Every posting has a ScholarshipType (Government or Academic).

## The fund allocation report is a hand-written .xlsx, not PhpSpreadsheet
FundAllocationSheet writes Office Open XML into a ZipArchive itself. PhpSpreadsheet needs ext-gd, which local PHP lacks, and no new dependency was approved. OSAS set the column order (2026-10-24): SEQ No., Student Number, Last Name, First Name, Middle Name, Course (abbreviated via CvsuPrograms::ABBREVIATIONS, falling back to the stored name), Amount Received (the student's share), Signature (blank, signed by hand). OSAS had the separate Amount column and the "Total funds · N students passed · share each" summary line removed. The official CvSU logo (resources/images/cvsu-logo.png, from cvsu.edu.ph) is a oneCellAnchor drawing at A1 beside the four masthead rows; it is embedded as the raw PNG and sized with getimagesize(), so no GD is needed. The sheet prints A4 landscape, one page wide, with heading row 6 as print titles. Student numbers are text cells, and amounts are numeric strings from Peso::fromCentavos with a peso number format. Add an abbreviation whenever a program is added to CvsuPrograms (CvsuProgramsTest enforces it). Students' date of birth, contact number and address stay out of the sheet.
