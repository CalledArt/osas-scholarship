---
paths:
  - resources/views/admin/fund-allocation/show.blade.php
  - resources/views/admin/fund-allocation/index.blade.php
---

# Fund Allocation

## Fund allocation page fits the screen; only the student list scrolls
Decided 2026-10-25 at OSAS's request: from lg up, admin/fund-allocation/show fits one screen (lg:h-screen flex column, min-h-0 down to the table wrapper) and only the "Students who passed" table scrolls (lg:overflow-y-auto). Its thead is sticky; the header line is an inset box-shadow on each th, not a border-b on the row, because collapsed table borders don't move with a sticky header. Below lg the page scrolls normally, since the form leaves too little room for the list on a phone. Since 2026-10-25 (the user's request) the page runs at max-w-[100rem] and its header is x-welcome-banner: the scholarship title as the h1 (with `vt-title`, so the card's title still transitions into it), the status mark and type as its sentence, and View scholarship plus Download Excel sheet in its action slot. Two traps that made the whole page scroll again: the table wrapper must stay `relative`, or the sr-only labels in its cells (absolutely positioned) take the page as their containing block and stretch it to the list's full height; and the totals dl needs lg:shrink-0, since its overflow-hidden lets flexbox squash it to clip its figures.

## Fund Allocation list: banner counts funds still to set
Decided 2026-10-25 at the user's request: admin.fund-allocation.index runs at max-w-[100rem] under x-welcome-banner ("Fund Allocation"). Its sentence counts passed scholarships with no total_funds across every page ("N scholarship(s) still need(s) ... total funds."), else "You're all caught up. Every fund is set.", else "No students have passed a scholarship yet." The count is `unfunded_total`, an uncorrelated subquery on the one paginated query (like AppLayout's old unread_total), so don't add a separate count query. The cards keep their design in an auto-fill grid (min 20rem), 12 to a page.
