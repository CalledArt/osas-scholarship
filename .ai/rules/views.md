---
paths:
  - 'resources/views/**'
  - resources/views/dashboard.blade.php
---

# Views

## Cross-page transitions name one element per navigation
Pages use cross-document view transitions (app.css, script in layouts/app). Mark a list row's title or a detail page's h1 with data-vt-title; the head script gives exactly one element view-transition-name record-title as the navigation happens. Never set record-title statically: two elements with one name on a page cancel the transition. The static names rail, strip and cycle (the step register) must stay unique per page.

## Student pages show no places or slots-left counts; RA 10931 counts years
Decided 2026-10-24 at OSAS's request: student-facing pages don't count places or slots left. The one exception, restored the same day: RA 10931 counts the year of study while in force, as "Free tuition · Year X of Y" in the Dashboard/Scholarships box (x-active-scholarships) and a "Your year of study" row on the Act page (with "This is your last covered school year." in the final year). Paused (leave of absence) shows no count. There are no year squares, and the tutorial's x-free-tertiary-education sample shows no count. There is no "You can apply to N more scholarships" box or sentence (the x-scholarship-places component was deleted). There are no slot drawings or "Slots left" / "X of Y slots left" (the x-parcel component was deleted); a scholarship still shows its total "Slots". When a student can't apply, the empty Available-to-apply box says why: on leave, holding an active scholarship, or 2 applications waiting. The limits still apply in the policy. Admin pages keep their slot and year figures, which OSAS uses to manage.

## Student dashboard stacks its panes when nothing can be applied to
Decided 2026-10-25 at the user's request (no wasted space): DashboardController passes hasOpenScholarships (not on leave, not at the active limit, and open boxes to show). Only then does the dashboard use the 22rem Mine | Open two-pane grid. Otherwise the panes stack: the Where-you-stand boxes run three across in equal-height cells from xl (grid-flow-col, auto-cols-fr; the ID box full width over the other two from sm, since three across at lg cramps its student-number cell), and the empty Available box and My applications span the width beneath. Don't fill the empty right pane with decoration; DESIGN.md's Two Panes Rule allows nothing ornamental. The dashboard runs at max-w-[100rem] (asked 2026-10-25, so a 1920px screen has no empty gutters), the same width as the admin dashboard; the cap keeps boxes from stretching on wider monitors. The student Scholarships page (ScholarshipController::index) follows all of this too: hasOpenScholarships, the stacked panes, max-w-[100rem], and x-welcome-banner headed "Scholarships" with the open count or "Nothing is open for you to apply to right now." My Applications also takes the banner and 100rem, and since 2026-10-25 (asked by the user) shows each application as a card drawn like the admin Fund Allocation cards (auto-fill grid, min 20rem; Reference and Applied cells; "N days waiting" only while pending); its waiting count rides on the paginated query as a `waiting_total` subquery (like AppLayout's unread_total), so don't add a separate count query.

## People's names show in capitals everywhere
Decided 2026-10-25 at the user's request: every displayed person's name is in capitals, overriding DESIGN.md's annotation-only uppercase. A name standing alone takes CSS `uppercase` (stored data and screen readers unchanged): Search Student and Edit Student Data rows, Fund Allocation's students, the scholarship page's applicants and pass-list result names, the student ID box, and the rail account name (it wraps, not truncate). A name inside a sentence or a file is mb_strtoupper'd: the dashboards' "Welcome back, NAME!", the Edit Student banner, the apply form's name details, the applications PDF (surname-first and Prepared by) and FundAllocationSheet (last/first/middle and Prepared by, via its upper() helper). Form inputs (Profile, Edit Student) keep names as typed. The tour samples match. Names also read surname first everywhere (asked the same day): display User::surname_first_name ("DELA CRUZ, JUAN SANTOS", PersonName::surnameFirst), never full_name or the stored name column, including the greetings, rail, ID box, every list, the pass-list results built by DecideScholarshipApplicationsFromCsv (rows typed in the CSV stay as typed), and the PDF and Excel Prepared by. Every form and detail list orders its name fields Last name, First name, Middle name (register, both profile forms, Edit Student, the apply form's details), with autofocus on Last name.

## New pages need a draft entry; downloads say download; refreshed lists carry data-results
Added 2026-10-27 at the user's request (loading animation and skeleton screens). When a GET page hasn't answered within 300ms, x-page-skeleton draws a draft of the page being opened: its real chrome, plus bars where its records go. The draft is picked by path from App\View\Components\PageSkeleton.
- A new page reached by link needs an entry there: path, shape, a heading only if fixed, and the parts to hide. The first match wins, so fixed paths go before `*` wildcards (PageSkeletonTest checks this).
- A heading only some users should see, like the RA 10931 box, comes from the link's data-vt-title, not the map. The map and the templates sit on every page, so assertDontSee on their fixed words ("Where you stand", headings) will see them.
- A link to a file download must carry the `download` attribute (DesignSystemTest checks it). Otherwise the progress bar waits 20s for a page that never comes.
- A list that a search, sort or page link replaces in place carries data-results, so it dims while the page loads.
- Submit buttons get a busy ring automatically. A button that draws its own .animate-spin keeps its own.

## Hardening rules: scroll panels, long words, error pages, field limits
Decided 2026-10-27 (the user's "harden the system, no errors should be detected"):
- A scroll wrapper (overflow-x-auto or overflow-y-auto) is `relative`, or the sr-only labels inside it escape and widen a phone's page. The admin dashboard's postings table did this: 720px on a 390px screen.
- If its contents hold no links or buttons, it takes tabindex="0" plus a name: role="region" with aria-labelledby, or role="group" when the section around it already carries that name (axe landmark-unique). It also takes focus-visible:-outline-offset-2.
- app.css sets `#main { overflow-wrap: anywhere }` and puts tables back to normal wrapping, so an unbroken title or file name breaks instead of scrolling the page sideways. Measured against the real records, it moves nothing until a word overflows.
- errors/500 and 503 use the guest layout and pass their own actions to x-error-sheet, so they never touch auth or the database. ErrorPagesTest asserts no queries.
- Every text input with a server `max:` rule carries the same maxlength.

## Polish rules: back links, status wording, annotation, no kicker
Decided 2026-10-27 (the user's "polish the whole system"):
- Every in-app "Back to …" link is x-back-link (Graphite, underlined, 12px hit area above and below for thumbs). Seal Green is for links that go forward. Auth guest pages keep their own centred links. DesignSystemTest guards it.
- A list shows a scholarship's status with Scholarship::statusLabel(): the stored status, or "Past deadline" for one still set to Open after its deadline. It costs no query, unlike availability(). The admin cards keep the stored mark but write "Past deadline {date}" instead of "Closes", and the admin page writes "Past deadline: {date}".
- Uppercase with tracking is only ever font-annot text-annot (DesignSystemTest guards it). The Fund Allocation keys and table headers were body-size and shouted.
- An empty list never draws an empty table: a table wider than a phone scrolls sideways and cuts off the sentence. Fund Allocation says it beneath its heading.
- x-error-sheet puts "Error 404" at the foot, not above the heading (that would be a kicker).
- app.css balances h1–h3 and sets p/dd to text-wrap: pretty, so names and titles never strand one word.
