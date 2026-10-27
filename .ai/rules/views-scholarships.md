---
paths:
  - resources/views/scholarships/republic-act-10931.blade.php
---

# Views Scholarships

## RA 10931 page: banner, coverage beside a scrolling Act
Decided 2026-10-25 at the user's request: the page runs at max-w-[100rem] under x-welcome-banner ("Republic Act No. 10931" with vt-title, the Act's short title as its sentence), keeps its title block full width, then sets "What this means for you" (rounded-lg Grant Wash panel) beside "Full text of the Act" in two columns from lg. From lg the page fits the screen and does not scroll (asked the same day): lg:h-screen flex column, the two-column grid takes the rest with grid-rows-[minmax(0,1fr)], and each panel keeps its heading while its body scrolls inside (the coverage panel's content below its heading and ACTIVE mark; the Act's text below "Full text of the Act"). The Act's scroll box is `relative` and scroll-padded so the coverage panel's #section-N links land with the heading in view. Below lg the page scrolls normally. Only the frame around the Act changed: when restyling it, diff the text between "Full text of the Act" and </footer> before and after, since the wording must match the signed Official Gazette copy.
