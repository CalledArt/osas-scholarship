---
paths:
  - 'resources/views/**'
---

# Views

## Cross-page transitions name one element per navigation
Pages use cross-document view transitions (app.css, script in layouts/app). Mark a list row's title or a detail page's h1 with data-vt-title; the head script gives exactly one element view-transition-name record-title as the navigation happens. Never set record-title statically: two elements with one name on a page cancel the transition. The static names rail, strip and cycle (the step register) must stay unique per page.
