---
paths:
  - resources/views/layouts/rail.blade.php
  - resources/views/layouts/app.blade.php
---

# Layouts

## The rail is pinned to the screen
Decided 2026-10-25 at the user's request ("don't make the navbar scrollable"): from lg the rail aside is lg:sticky lg:top-0 lg:h-screen lg:self-start, so it stays put while a long page scrolls beside it and Log Out is always on screen. Its own lg:overflow-y-auto only engages if a window is too short to hold it (it fits at 560px tall). Don't go back to a rail that stretches with the page. Below lg the slide-over is already fixed.

## Signed-in pages prefetch links on hover; GET must stay read-only
Added 2026-10-27: layouts/app carries a speculationrules script that prefetches same-origin links with "moderate" eagerness (pointer resting ~200ms, or pointerdown on phones). Measured locally in Chrome 157, click to content went from ~1.8s to ~0.47s (deliveryType navigational-prefetch). So every GET route must stay free of side effects. Any new GET that builds a file or streams a download must be added to the rule's "not" list, as /*/report, application documents and required-document samples are; AppLayoutTest checks those against real route URLs. Keep it prefetch-only, never prerender: prerender runs page scripts, and Laravel's StartSession only skips the previous-URL write when Sec-Purpose is exactly "prefetch".
