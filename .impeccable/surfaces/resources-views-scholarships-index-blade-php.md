---
version: 1
slug: "resources-views-scholarships-index-blade-php"
primary_target: "resources/views/scholarships/index.blade.php"
related_targets: ["resources/views/dashboard.blade.php","resources/views/components/active-scholarships.blade.php","resources/views/components/available-scholarships.blade.php"]
---

# Student scholarships: Dashboard and Scholarships page

**Scope:** the student Dashboard (`resources/views/dashboard.blade.php`) and Scholarships page (`resources/views/scholarships/index.blade.php`), through the shared `active-scholarships` and `available-scholarships` components. Mode: Operate.

**Audience and job:** a CvSU undergraduate, often on a phone, checking where they stand (RA 10931, any active scholarship, applications waiting, places left) and which open scholarships they can still apply to.

**Constraints:** the user asked for grids and boxes instead of ruled single-column lists, and less explanation. The visual world (Lot and Claim palette, type, slot squares) stays. The scholarship detail page, application pages and the practice tour are out of scope; the tour's sample screens now mirror these pages (redrawn 2026-10-25).

**Resolved 2026-10-25:** the practice tour's sample dashboard and My Applications were redrawn to match the current pages.

## Direction contract

THESIS: A student's two questions, "what do I hold?" and "what can I still apply to?", answered side by side as boxes. It refuses the single ruled column and the paragraph that explains the limits before showing them.

OWN-WORLD: Lot and Claim unchanged: Seal Green rail, Linen desk, white square-cornered boxes with 1px Rule borders and Seat shadow. Grant Wash box for RA 10931, Gold Wash for the student's own waiting application. Archivo for reading, Archivo Narrow only for cell keys. Everything countable is drawn as squares: slots, places left, RA 10931 years.

STORY: The student sees their RA 10931 year, active scholarship, places left and waiting applications at once, compares open scholarships box to box by closing date and slots left, then opens one to apply.

FIRST VIEWPORT: From lg, a 22rem "Mine" column on the left (ID box on the dashboard, RA 10931, active scholarship, places left, my applications) and an "Open" grid on the right: two-up boxes with title, type, slot squares, Closes and Slots-left cells, documents and an Apply cue. Each open box links to that scholarship's page.

FORM: Mine | Open, position 4 on the ordered list. seed:1de3af805dee3051

FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
