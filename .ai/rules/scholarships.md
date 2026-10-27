---
paths:
  - resources/views/admin/scholarships/index.blade.php
  - resources/views/admin/scholarships/show.blade.php
---

# Scholarships

## Admin Scholarships list is cards under the banner
Decided 2026-10-25 at the user's request (chosen over a dashboard-style table): admin.scholarships.index runs at max-w-[100rem] under x-welcome-banner ("Manage Scholarships", "N scholarships posted." from the paginator's total, so no extra query, and "+ Post Scholarship" in the action slot; keep that capitalisation, the tutorial trail and the form use it). Each scholarship is a card drawn like the Fund Allocation cards: stored status mark, type (· STEM only), Slots left "X of Y" and Applicants cells, "Closes {date}", a "View scholarship →" cue and an Edit link. The title link stretches over the card (after:absolute inset-0) and Edit is relative z-10 above it; don't nest links instead. The controller paginates 12 so the auto-fill grid closes square at 2, 3 or 4 across.

## Scholarship review page: banner and panels
Decided 2026-10-25 at the user's request: admin.scholarships.show runs at max-w-[100rem] under x-welcome-banner (title with vt-title; status mark, type, STEM only, "X / Y slots remaining" and "Deadline: …" as its sentence; Fund Allocation (when anyone passed) and Edit as its action). Upload Pass List, Applicants (count, Print report (PDF) in its header, rows padded inside) and About this scholarship are rounded-lg bordered panels headed with a seal icon, like the dashboard's postings table. From lg the page fits the screen (lg:h-screen flex column; the grid takes the rest with grid-rows-[minmax(0,1fr)]): the Applicants list scrolls inside its panel under the panel header, and About (20rem, 26rem from xl) scrolls its justified description and documents inside its panel; below lg the page scrolls but the description is capped at max-h-96 and scrolls on its own. Pass List Results is capped at 40vh so it can't push the applicants out. Keep the scrolling list and description `relative` (sr-only labels) and don't add hyphens-auto (it split words like de-gree). Pass List Results stays a linen-sunk panel above the upload panel. The tour's sample scholarship screen mirrors this.
