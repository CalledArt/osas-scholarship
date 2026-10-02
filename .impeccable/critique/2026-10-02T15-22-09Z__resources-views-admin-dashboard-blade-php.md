---
target: the admin dashboard
total_score: 25
max_score: 40
na_heuristics: 
p0_count: 0
p1_count: 3
target_identity: "file:/home/michael/OSAS-Scholarships/resources/views/admin/dashboard.blade.php"
target_fingerprint: "sha256:e283d2b5146492d0f55a9ed2b1bf6442d7129e9b484b89dd7ffa1156642d8d03"
target_path: /home/michael/OSAS-Scholarships/resources/views/admin/dashboard.blade.php
timestamp: 2026-10-02T15-22-09Z
slug: resources-views-admin-dashboard-blade-php
closed: true
---
Method: dual-agent (A: design review · B: detector + browser evidence)

Viewed via real HTTP-kernel renders against an in-memory SQLite DB with synthetic data (6 scholarships incl. one full, one closed, one no-deadline, one long title; plus empty / all-caught-up / first-run states), screenshotted in Chrome at 1440px and 390px.

## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 2 | Settled scholarships vanish; no oldest wait |
| 2 | Match System / Real World | 3 | Counts applications; the real unit of work is one pass list per scholarship |
| 3 | User Control and Freedom | 3 | Read-only navigation; tour offers Skip on step 1 only |
| 4 | Consistency and Standards | 3 | "3 of 10 slots filled" here vs "7 / 10 slots remaining" on the scholarship page |
| 5 | Error Prevention | 2 | Red "Closes in 2 days" next to Review invites a pass list while applications are still open |
| 6 | Recognition Rather Than Recall | 2 | Admin subtracts filled from slots per row; exact date only on hover |
| 7 | Flexibility and Efficiency | 2 | Fixed order, no sort, no pass-list action from the row |
| 8 | Aesthetic and Minimalist Design | 3 | Calm, but count sits ~650px from its title at desktop |
| 9 | Error Recovery | 2 | "5 of 5 slots filled, 2 pending" stated but not explained |
| 10 | Help and Documentation | 3 | Replayable tour; pass-list step spotlights a rail link |
| **Total** | | **25/40** | **Acceptable** |

## Design Specificity Verdict

LLM: a calm, well-built inbox that any queue product could ship. Missing product-specific ideas: open vs ready-to-decide, real competition vs formality, wait time, what's already awarded. DESIGN.md's Register was the product-specific answer; commit 3e6c593 replaced it with this list and DESIGN.md:354-359 still describes it.

Detector: dashboard.blade.php 0 findings. Rendered page: low-contrast rail "Administrator" label 4.4:1 (layouts/rail-contents.blade.php:41; DESIGN.md prescribes that pairing), layout-transition width on page progress bar (components/page-progress.blade.php:8). False positives: flat-type-hierarchy (CSS unread), cramped-padding on the list (-mx-3 hover bleed) and layout shell. Overlays ran headless only; no user-visible tab.

## Overall Impression

Honest standing line, accessible rows, holds up at 390px. But it answers "how many applications are pending?" when the coordinator needs "which scholarship can I decide now, and is there a real choice?"

## What's Working

1. Standing line with three honest states (lines 9-16), trans_choice, no tiles.
2. Rows: real links, inset Seal Green focus ring, no-lift hover, long titles wrap, mobile chevron aria-hidden.
3. One query feeds rows, total and count; whole-day deadline math; tour is a proper dialog (focus trap, Esc, focus return, reduced motion).

## Priority Issues

- [P1] Closing copy isn't computed truth. x-deadline reads only `deadline` (deadline.blade.php:8); Municipal reads "Closes in 21 days · 5 of 5 slots filled" though isOpenForApplications() is false; closed-by-OSAS rows also say "Closes in N days". Fix: availability state per row (Full / Closed by OSAS / Closed N days ago / Closes in N days). Command: harden.
- [P1] Urgency tracks the student's deadline, not readiness to decide. Pass list rejects every other pending applicant (ScholarshipApplicationController.php:13-18), yet red "Closes in 2 days" sits on DOST while still open. Fix: split "Ready for a pass list" vs "Still taking applications"; standing line counts pass lists owed. Command: shape, then clarify.
- [P1] Settled work, wait time and slot competition are invisible. Settled scholarships vanish (2 of 6 hidden); SM's 20-day-old applicants sort last via `nulls last` (DashboardController.php:26); nothing compares pending to slots left. Fix: restore Register from 3e6c593^ with a Slots left column, or add these facts to rows and rewrite DESIGN.md. Command: layout, then document.
- [P2] Hierarchy inverted. Post scholarship is the only filled element (lines 20-24); headline "31" same size as its sentence; h2 16px vs Section 18px; counts flush right at 1440px. Fix: standing line as headline, numbers at Metric, Post scholarship secondary except in empty state, counts in columns. Command: layout.
- [P2] Thin empty and first-run states. Empty state one sentence, no next step (lines 12-13); tour Skip on step 1 only (tutorial.blade.php:119); pass-list step spotlights a rail link (tutorial.blade.php:28). Command: onboard.

## Persona Red Flags

Alex: open row → scroll → upload → confirm() per pass list; no sort by wait/pending; "View all" uses a different order; mental slot arithmetic.
Sam: whole row read as one link name incl. "right arrow"; red+bold urgency with no sr-only text; exact date only in title attribute; static <title>.
OSAS coordinator at cycle peak: can't tell ready from collecting; Municipal shown as work though only outcome is rejecting 2; "31 applications" overstates 4 uploads (2 competitive).

## Minor Observations

- divide-gray-100 row dividers nearly invisible; DESIGN.md says Rule Line (gray-200).
- py-10 vs DESIGN.md py-12.
- DESIGN.md self-contradictions: KPI row and stat-tile token vs "Don't build a KPI tile grid"; max-w-5xl vs max-w-7xl; loading-skeleton section describes a replaced overlay.
- "Search Student" rail label undersells the roster.
- True 390px mobile is clean.

## Questions to Consider

- What is OSAS's own deadline (sponsor's endorsement date)? Should red mean that?
- If the only decision is one pass list per scholarship, why count applications?
- With 18 pending for 21 open slots, is there a decision at all?
- Why was the Register replaced: staff complaint, mobile width, or a side effect of the tutorial commit?
