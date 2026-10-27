---
paths:
  - 'resources/views/tutorial/**'
---

# Tutorial

## The practice tour must never reach real records
The tutorial page (/tutorial) acts out the flow on sample screens held in Alpine state. Its stage has no <form>, no name= inputs and no links to real records; the only real request is the tutorial.complete POST. TutorialTest::test_the_practice_tour_can_only_mark_itself_seen enforces this by listing every form action on the page — keep it passing rather than loosening it.

## Toggle tutorial stage elements with :class hidden, not x-show
Alpine's x-show reveals an element on the next requestAnimationFrame, but the tour guide measures its spotlight target synchronously after each stage action. With x-show the spotlight misses newly shown screens and rows (or comes out too short). Use :class="{ 'hidden': ... }" for anything the stage shows or hides while a step plays.

## The tour's sample screens mirror the current pages
Redrawn 2026-10-25 at the user's request: the student tour's dashboard is the real one in miniature (x-welcome-banner with the sample bell in its corner, Where you stand boxes with x-free-tertiary-education as the RA 10931 box, Available to apply boxes, panes stacking once state.canApply is false), My Applications is the card grid, and the admin tour's dashboard is banner + three stat cards + Recent scholarship postings (the old Ready for a pass list / Still taking applications lists are gone, as on the real page). Student steps include the bell (tour-bell), the Scholarships page (tour-scholarships), "Your scholarship is active" (tour-standing) and the Profile card (tour-profile). Since a second pass the same day every admin page has a sample screen too: Scholarships cards (tour-cards), Fund Allocation list and one scholarship's fund (tour-fund-list, tour-fund, with the live per-student split), Search Student (tour-roster, filters as typed), Edit Student Data list and edit form (tour-students, tour-student), and the staff Profile (tour-profile). Sample inputs are readonly and nameless and buttons are spans, so the stage still has no forms or real links. When a real page's layout changes, change its sample here too. On a phone, "Show me" opens the slide-over through appShell.openTourNav(), which leaves the page live: openNav() makes it inert, which made the guide's own buttons untappable.
