---
paths:
  - resources/views/scholarship-applications/show.blade.php
---

# Scholarship Applications

## Student application page: banner, title block, panels
Decided 2026-10-25 at the user's request: scholarship-applications.show runs at max-w-[100rem] under x-welcome-banner (scholarship title with vt-title; the application's status mark and the scholarship's type as its sentence; "View the scholarship" as an outlined button in the action slot). The x-title-block (Reference, Applied, Documents sent, Standing) stays full width beneath it. "Where your application stands" (now a visible heading) and "Documents you sent" are rounded-lg bordered panels with seal icons, side by side from lg (24rem, 28rem from xl, for documents). Only the title block carries view-transition-name: cycle; the cycle <ol> used to carry it too, which cancelled the transition, so don't put it back there.
