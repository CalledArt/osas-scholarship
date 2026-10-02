---
name: OSAS Scholarship Allocation System
description: An institutional record system for Cavite State University scholarships — flat, bordered, and unambiguous, where green is authority and gold is unfinished business.
colors:
  seal-green: "#0B6E4F"
  deep-seal: "#074A35"
  signal-green: "#0F9D63"
  citation-gold: "#FFC72C"
  struck-gold: "#E6A800"
  record-white: "#FFFFFF"
  bond-paper: "#F3F4F6"
  ledger-gray: "#F9FAFB"
  rule-line: "#E5E7EB"
  field-stroke: "#D1D5DB"
  ink: "#111827"
  ink-soft: "#1F2937"
  graphite: "#374151"
  slate: "#6B7280"
  mist: "#9CA3AF"
  approved-wash: "#DCFCE7"
  approved-ink: "#15803D"
  rejected-wash: "#FEE2E2"
  rejected-ink: "#B91C1C"
  danger: "#DC2626"
typography:
  display:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2.25rem"
    fontWeight: 700
    lineHeight: 1.25
    letterSpacing: "normal"
  headline:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.5rem"
    fontWeight: 700
    lineHeight: 2rem
    letterSpacing: "normal"
  title:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.25
    letterSpacing: "normal"
  section:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: 1.75rem
    letterSpacing: "normal"
  body:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.25rem
    letterSpacing: "normal"
  label:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 500
    lineHeight: 1rem
    letterSpacing: "0.025em"
  mono:
    fontFamily: "ui-monospace, SFMono-Regular, Menlo, Consolas, monospace"
    fontSize: "0.8125rem"
    fontWeight: 400
    lineHeight: 1.25rem
    letterSpacing: "normal"
rounded:
  sm: "4px"
  md: "6px"
  lg: "8px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "48px"
components:
  button-primary:
    backgroundColor: "{colors.seal-green}"
    textColor: "{colors.record-white}"
    rounded: "{rounded.lg}"
    padding: "10px 16px"
    typography: "{typography.body}"
  button-primary-hover:
    backgroundColor: "{colors.deep-seal}"
  button-secondary:
    backgroundColor: "{colors.record-white}"
    textColor: "{colors.graphite}"
    rounded: "{rounded.md}"
    padding: "8px 16px"
    typography: "{typography.body}"
  button-secondary-hover:
    backgroundColor: "{colors.ledger-gray}"
  button-danger:
    backgroundColor: "{colors.danger}"
    textColor: "{colors.record-white}"
    rounded: "{rounded.md}"
    padding: "8px 16px"
    typography: "{typography.body}"
  input-text:
    backgroundColor: "{colors.record-white}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "8px 12px"
    typography: "{typography.body}"
  card:
    backgroundColor: "{colors.record-white}"
    textColor: "{colors.ink}"
    rounded: "{rounded.lg}"
    padding: "24px"
  badge-pending:
    backgroundColor: "rgba(255, 199, 44, 0.2)"
    textColor: "{colors.deep-seal}"
    rounded: "{rounded.full}"
    padding: "2px 10px"
    typography: "{typography.label}"
  badge-approved:
    backgroundColor: "{colors.approved-wash}"
    textColor: "{colors.approved-ink}"
    rounded: "{rounded.full}"
    padding: "2px 10px"
    typography: "{typography.label}"
  badge-rejected:
    backgroundColor: "{colors.rejected-wash}"
    textColor: "{colors.rejected-ink}"
    rounded: "{rounded.full}"
    padding: "2px 10px"
    typography: "{typography.label}"
  badge-neutral:
    backgroundColor: "{colors.bond-paper}"
    textColor: "{colors.graphite}"
    rounded: "{rounded.full}"
    padding: "2px 10px"
    typography: "{typography.label}"
  table-header:
    backgroundColor: "{colors.ledger-gray}"
    textColor: "{colors.slate}"
    padding: "12px 24px"
    typography: "{typography.label}"
  rail-link-active:
    backgroundColor: "{colors.record-white}"
    textColor: "{colors.ink}"
    rounded: "{rounded.md}"
    padding: "8px 12px"
    typography: "{typography.body}"
  rail-link-idle:
    textColor: "{colors.slate}"
    rounded: "{rounded.md}"
    padding: "8px 12px"
    typography: "{typography.body}"
---

# Design System: OSAS Scholarship Allocation System

## Overview

**Creative North Star: "The University Registrar"**

This is the interface of a well-run records office, not a product. The page ground is a quiet Bond Paper Gray desk; every unit of content is a white record card laid on it, defined by a hairline border rather than a drop shadow. Cavite State University's Seal Green carries institutional authority — it appears on the actions that commit something, the links that lead deeper into a record, and the counts that represent a settled award. Citation Gold is the office highlighter: it marks the thing you are focused on and the thing still waiting on a human decision. Nothing else is allowed to be loud.

The density is administrative but never cramped. Cards breathe at 24px internal padding, sections stack at a 24px rhythm, and pages open with 48px of air above the first card. Type stays small and disciplined — 14px body, 12px labels — because these screens are read in scanning mode by staff working a queue and by students checking one specific status. The one place the system raises its voice is the sign-in screen, set like the title page of a record: a full-height Deep Seal panel lit from one corner, the CvSU seal blind-embossed into it, the system's name at full display strength, and the scholarship cycle laid out as a ruled register — establishing who is behind this system before anyone types a password. That contrast is deliberate: the front door is institutional and warm; the working interior is calm and out of the way.

The system rejects the dashboard-as-spectacle idiom. No gradient cards, no glassmorphism, no floating panels, no decorative iconography, no animated counters. Financial aid is high-stakes and often anxiety-inducing for the student reading it; the design's job is to make status legible at a glance and to never let visual flourish compete with a deadline, a slot count, or a decision.

**Key Characteristics:**
- Flat white record cards on a Bond Paper Gray desk, separated by 1px hairlines, not shadows
- One typeface (Figtree) doing every job through weight and size alone
- Green for what is settled, gold for what is unresolved — a strict two-signal color language
- Uniformly soft corners (8px cards and primary actions, fully round status pills)
- Small, dense, uppercase-tracked labels above large plain numerals
- Motion confined to 150ms state transitions and overlay entrances; nothing moves on its own

## Colors

An institutional two-note palette — a deep muted forest green and a warm saturated academic gold — set against a strictly neutral cool-gray document stack that does all the structural work.

### Primary
- **Seal Green** (#0B6E4F): The university's authority color and the system's only action color. It fills primary buttons ("Post Scholarship", "Sign in"), colors every inline navigational link ("View details →", "View"), and underlines the active desktop nav item. Also the input focus border.
- **Deep Seal** (#074A35): The hover partner for every Seal Green surface, and the text color for the wordmark and for anything set on a gold wash (the pending badge, the role chip in the account menu). Never used as a fill for a resting element.
- **Signal Green** (#0F9D63): Reserved almost entirely for the sign-in branding panel, as the light source of a radial glow falling from its top-right corner across a Deep Seal field. It is a lighting effect, not a UI color. Text on that panel sits on the Deep Seal end, where white at 70% opacity still clears 4.5:1.

### Secondary
- **Citation Gold** (#FFC72C): The attention marker. It appears as the focus ring on Seal Green buttons, as a 20%-opacity wash behind pending badges, and as the lettering in the CvSU seal. It is never a background for a large surface, and never a text color — see the Gold Is Not Ink Rule.
- **Struck Gold** (#E6A800): A darker gold held in reserve for gold-on-dark or gold-on-wash situations. It is **not** a text color on white either (≈2.1:1).

### Neutral
- **Record White** (#FFFFFF): Every card, table, panel, modal, dropdown, and the nav bar. The surface that content lives on.
- **Bond Paper Gray** (#F3F4F6): The application desk — the page background behind all authenticated screens — and, at the same value, the hairline border of every card. The system deliberately uses one value for both the ground and the card edge, which is why cards read as cut from the desk rather than floating above it.
- **Ledger Gray** (#F9FAFB): Table header rows, table row hover, and secondary-button hover. The faintest possible tonal shift.
- **Rule Line** (#E5E7EB): Table head/body dividers and the mobile nav section separator.
- **Field Stroke** (#D1D5DB): Input and checkbox borders at rest, and the secondary button's edge.
- **Ink** (#111827): Card headings, table cell primary text, the pending counts in the decision queue — the highest-contrast text.
- **Ink Soft** (#1F2937): Page header titles in the white header bar.
- **Graphite** (#374151): Form field labels and secondary-button text.
- **Slate** (#6B7280): Body copy, supporting descriptions, table cell secondary values, idle nav items.
- **Mist** (#9CA3AF): Decorative marks only — input icons and the em-dash that stands in for an absent value. At ≈2.6:1 on white it is **not** a text color; anything a reader must actually read is Slate or darker.

### Tertiary
Status semantics, used only inside badges and flash messages, plus the one red deadline below. They never appear as page furniture.

- **Approved** (#DCFCE7 wash / #15803D ink): Approved application badges and success flash messages.
- **Rejected** (#FEE2E2 wash / #B91C1C ink): Rejected application badges. On the student dashboard, a deadline inside three days sets in Rejected Ink at 600 weight — the one place red appears outside a badge or a destructive control. The admin decision queue never uses it.
- **Danger** (#DC2626): Destructive buttons only (account deletion). It is the one red that is ever a fill.

### Named Rules

**The Two-Signal Rule.** This system speaks with exactly two signals. Green means *settled* — approved, open, authoritative, committed. Gold means *unresolved or focused* — pending a human decision, or the element the keyboard is currently on. Gold is never decoration and never a general brand accent on an interior screen; if a new element is gold, it must be because someone still has to act on it. Everything that is neither settled nor pending is gray.

**The Seal Rule.** Seal Green is the university's authority, so it only ever marks a committing action or a forward path. It never colors a destructive control, a cancel, a dismissal, or a merely decorative surface. Red is the only color permitted on a destructive action.

**The One Ground Rule.** Bond Paper Gray (#F3F4F6) is simultaneously the page ground and the card border. Do not introduce a third neutral between them to "separate" a card — the identical value is what makes the interface read as flat records on a desk. On sheet surfaces the relationship inverts: the rail takes the Bond Paper ground and the content becomes Record White.

**The Gold Is Not Ink Rule.** Neither gold is ever a text color. Citation Gold reaches about 1.6:1 on white and Struck Gold about 2.1:1 — both fail the 4.5:1 floor at any size the interface actually uses. Gold appears as a focus ring against a green fill, as a 20%-opacity badge wash carrying Deep Seal text, and in the seal. When a number must read as urgent, give it weight and sort position, not gold.

**The Readable Neutral Rule.** Only Ink, Ink Soft, Graphite, and Slate carry text. Mist (≈2.6:1) is for marks a reader never has to decode, and every such mark pairs with an `sr-only` word so assistive technology gets the meaning the color cannot carry.

## Typography

**Display Font:** Figtree (loaded from Bunny Fonts, with `ui-sans-serif, system-ui, sans-serif` fallback)
**Body Font:** Figtree — the same family
**Mono Font:** the platform monospace stack (`ui-monospace, SFMono-Regular, Menlo, Consolas, monospace`) at 13px, used only where a reader must tell one character from another — one-time passwords, and literal CSV header names. Never as a costume for "technical".

**Character:** Figtree is a geometric humanist sans with open apertures and a tall x-height — clear at 12px, friendly rather than corporate at 36px. Using one family for everything is a deliberate institutional choice: hierarchy is built entirely from weight (400/500/600/700) and size, never from a contrasting face. The result reads like a well-set official form rather than a marketing page. Authenticated screens load weights 400/500/600; the sign-in screen additionally loads 700 for its headline.

### Hierarchy
- **Display** (700, 3rem → 3.75rem → 4.5rem across `lg`/`xl`/`2xl`, leading 1.05, tracking -0.02em): The sign-in branding panel headline ("Scholarship Allocation System"), stacked one word per line on desktop and set at 1.875rem in the mobile band. It does not appear anywhere inside the authenticated app.
- **Headline** (700, 1.5rem/24px, 2rem): The auth form heading ("Sign in", "Reset password"). One per screen, on unauthenticated pages only.
- **Title** (600, 1.25rem/20px, leading-tight): The page header inside the white header bar — the persistent "what screen am I on" label. Exactly one per authenticated screen.
- **Section** (600, 1.125rem/18px, 1.75rem): Card headings and the dashboard welcome line.
- **Body** (400, 0.875rem/14px, 1.25rem): All supporting copy, table cells, form inputs, nav items, buttons. The default.
- **Label** (500, 0.75rem/12px, +0.025em tracking): Badges and, uppercased with wider tracking (+0.05em), table column headers.

### Named Rules

**The Single Family Rule.** Figtree does every job. Do not introduce a second family for headings, numerals, or emphasis — if something needs to feel different, change its weight or its size. The one exception is character disambiguation: a value the reader must transcribe exactly, such as a one-time password or a literal CSV header, sets in the mono stack. Wanting to look technical is not disambiguation.

**The Small-Caps Perimeter Rule.** Uppercase with letter-spacing is reserved for the perimeter of data: table column headers. It never touches a sentence, a button, a card heading, or a badge. Every button in the system reads in sentence case at Body size.

**The Tabular Numerals Rule.** Every number a reader compares down a column sets in `font-variant-numeric: tabular-nums` — counts, slot ratios, waits, elapsed days. Proportional digits make a column of figures ragged, and a register whose numbers do not line up is not a register. This is the cheapest detail in the system and the one most easily lost.

**The Numeral-Over-Word Rule.** Where a count matters, the number carries the weight and the words around it drop to Slate. Never let a label out-weigh its value.

## Layout

The application is a two-column shell: a fixed 240px (`w-60`) rail on the left and a content column that takes the rest. There is no horizontal navigation bar at any breakpoint. Content sits on the Bond Paper desk by default; surfaces that *are* the document opt into a Record White sheet instead.

**Containers.** Widths are chosen semantically inside the content column: **1024px (`max-w-5xl`)** for the admin decision queue and the student's scholarship list, where rows are read top-to-bottom; **1280px (`max-w-7xl`)** for wide administrative tables that need every column; and **384px (`max-w-sm`)** for auth forms, centered in their half of the split. Content gutters step 24px → 40px (`px-6 lg:px-10`); older card surfaces keep 16px → 24px → 32px.

**Vertical rhythm.** Content regions open with 48px of top and bottom padding (`py-12`). Sibling cards and sections stack at 24px (`space-y-6`); tighter lists — the student scholarship feed — stack at 16px (`space-y-4`). Card interiors are 24px (`p-6`); list rows inside a card are 24px horizontal by 12px vertical. Form fields stack at 20px (`space-y-5`), with 4px between a label and its input and 8px before an error message.

**Grids.** Paired detail panels run 1 column → 2 at 1024px with a 24px gutter. Only two breakpoints do real work: `sm` (640px) and `lg` (1024px).

**Responsive behavior.** Below 1024px the rail leaves the flow and becomes a slide-over over a 40%-opacity scrim, opened by a single icon button in a minimal 56px strip that carries nothing but that button and the seal. Tables do not restructure — they scroll horizontally inside `overflow-x-auto`, keeping column relationships intact. The sign-in screen's branding panel collapses below 1024px into a compact Deep Seal band above the form, carrying the seal, wordmark, headline, and one-line lede, so phones meet the same front door. The cycle register shows only on desktop screens at least 820px tall; shorter laptops keep the headline and lede with room to breathe.

### Named Rules

**The Two-Container Rule.** 1280px means "this is a working surface with columns to compare." 1024px means "this is a list read top-to-bottom." Pick the container from the reader's task, not from the amount of content.

**The No Horizontal Nav Rule.** Wayfinding lives in the rail and nowhere else. The mobile strip is a disclosure control, not a navigation bar: it may hold the rail trigger and the seal, and it must never grow links, a search field, or an account menu. If a destination matters, it belongs in the rail.

**The Tables Don't Fold Rule.** On narrow screens, data tables scroll sideways; they never collapse into stacked card lists. A scholarship's slots, applicants, deadline, and status only mean something next to each other.

## Elevation & Depth

**This system is flat by doctrine.** Depth comes from tonal layering and hairline borders, not from shadows. A card is Record White on a Bond Paper Gray ground with a 1px Bond Paper Gray border; that value pairing — identical border and ground — is what makes surfaces read as inset records rather than floating panels. Shadows exist in only two roles: an almost-invisible `shadow-sm` that keeps white cards from vibrating against the gray ground, and genuine overlay shadows on elements that actually leave the page plane.

Hover never lifts. Interactive cards respond by warming their border to 40%-opacity Seal Green (`hover:border-cvsu-green/40`) and list rows respond with a Ledger Gray fill — the surface stays exactly where it is.

### Shadow Vocabulary
- **Seat** (`box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05)`): The default for every card, table container, primary button, and input. Barely perceptible; its job is edge definition, not elevation.
- **Header band** (`box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)`): The white page-header strip below the nav, the one horizontal element that casts onto the content beneath it.
- **Overlay** (`box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)`): The account dropdown, paired with a `ring-1 ring-black/5` edge.
- **Dialog** (`box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)`): Modals only, over a 75%-opacity gray-500 scrim.

### Named Rules

**The Flat Desk Rule.** Surfaces are flat at rest and stay flat on hover. A shadow heavier than Seat is permitted only on an element that genuinely floats above the page — a dropdown or a modal. If a new component wants elevation to feel important, give it a border or a heading instead.

## Shapes

Uniformly soft, never sharp, never fully organic. The corner radius encodes what a thing is:

- **8px (`rounded-lg`)** — the system's signature radius. Cards, table containers, primary buttons, text inputs, flash-message banners, and modals. If it holds content or commits an action, it is 8px.
- **6px (`rounded-md`)** — secondary and danger buttons, the account dropdown panel, and icon buttons. A subtly tighter corner that quietly separates supporting controls from primary ones.
- **4px (`rounded`)** — loading-skeleton bars only.
- **Fully round (`rounded-full`)** — status pills, the circular seal, and skeleton avatars. Roundness here means "this is a state, not a control."

Borders are always exactly 1px and always a neutral, with three deliberate exceptions that use thickness to mark selection: the **2px bottom border** on the active desktop nav item, the **4px left border** on the active mobile nav item, and the **4px gold ring** on the CvSU seal. The sign-in panel's blind-embossed seal — concentric rings with "Cavite State University" on the top arc and "Office of Student Affairs and Services" upright on the bottom arc, in white at 8%, cropped off the top-right corner — is the only free-form geometry in the system. It exists to press the institution into the page the way a seal is pressed into an official record, and its center stays empty so it never competes with the headline.

### Named Rules

**The Nothing-Is-Square Rule.** No element in this system has a 0px corner. If a new component needs to feel more rigid, use a border, not a sharp corner.

## Components

Components are **quiet, bordered, and unambiguous**. They state their status plainly and never perform. Every surface is bordered rather than floated, and every interactive element has an obvious, high-contrast state — this is a system students trust with their financial aid, so restraint is the point.

### Buttons
- **Shape:** Softly rounded, 8px on primary, 6px on supporting controls.
- **Primary:** Seal Green fill (#0B6E4F), white text, 600 weight at 14px, 16px × 10px padding, Seat shadow, transparent 1px border. Content is centered with an 8px gap so an inline spinner or icon sits correctly.
- **Hover / Focus:** Background deepens to Deep Seal (#074A35) over 150ms `ease-in-out`. Focus shows a 2px Citation Gold ring with a 2px offset — the gold-on-green pairing is the system's most recognizable interaction detail. Disabled drops to 70% opacity with a not-allowed cursor.
- **Secondary:** White fill, Field Stroke border (#D1D5DB), Graphite text, 6px radius, 16px × 8px padding, Ledger Gray on hover. Focus rings in Seal Green.
- **Danger:** Danger red fill (#DC2626), white text, 6px radius, 16px × 8px padding, lightening to red-500 on hover and darkening to red-700 on press. Focus rings in red-500.
- **Shared type:** Every button — primary, secondary, danger — sets its label at Body size in 600 weight (14px), sentence case. The tiers are separated by fill, border, and corner, never by type size.
- **Disabled:** 70% opacity with a not-allowed cursor, on every variant.
- **Loading:** Submit buttons that navigate bind a `loading` flag, swap the label to a progress phrase ("Signing in..."), disable themselves, and reveal a 16px spinning ring. The button never changes size.

### Named Rules

**The Two-Ring Rule.** A focus ring is always 2px at a 2px offset, and its hue is decided by the surface it sits on, not by brand preference. On a Seal Green fill, the ring is Citation Gold — the system's signature pairing, legible because the gold reads against the green edge. On any white or neutral control, the ring is Seal Green, matching the input focus treatment; gold on white is roughly 1.5:1 and does not survive as an indicator. On a semantically colored control, the ring is that control's own hue (red-500 on the danger button).

### Chips / Badges
- **Style:** Fully round pills, 10px × 2px padding, 12px text at 500 weight, wash background with matching darker ink. Four variants: neutral (Bond Paper on Graphite), approved (#DCFCE7 on #15803D), rejected (#FEE2E2 on #B91C1C), and pending (Citation Gold at 20% on Deep Seal).
- **State:** Badges are read-only status output — they are never filters, never buttons, never dismissible. The pending variant is the only gold surface permitted in a list.

### Cards / Containers
- **Corner Style:** 8px (`rounded-lg`), with `overflow-hidden` so tables and divided lists clip cleanly to the corner.
- **Background:** Record White on the Bond Paper Gray page ground.
- **Shadow Strategy:** Seat only — see Elevation & Depth.
- **Border:** 1px Bond Paper Gray, identical to the page ground.
- **Internal Padding:** 24px for content cards. Cards with a titled head use a divided structure instead: a 24px × 16px header block with a 1px bottom rule, then a `divide-y` list whose rows are 24px × 12px.
- **Interactive variant:** A card that is itself a link warms its border to Seal Green at 40% opacity on hover, with no movement and no shadow change.

### Inputs / Fields
- **Style:** White fill, 1px Field Stroke border, 8px radius, Seat shadow, 14px text. Fields carrying an affordance icon get a 20px icon in Mist inset 12px from the edge, with the input padded to 40px on that side.
- **Focus:** The border and ring both shift to Seal Green — the one place green appears on a non-committing element, because it marks where you are.
- **Error:** Messages render as a Slate-spaced list in red-600 at 14px, 8px below the field. The field itself is not recolored; the message carries the signal.
- **Password fields** pair a Mist eye toggle inside the right inset that swaps the input type without losing focus position.
- **File fields:** the native control, with its `::file-selector-button` styled as a secondary button — white fill, Field Stroke border, 6px radius, Graphite text at 14px/600 — and the filename in Slate beside it. Every file input in the app renders through one component so the three upload surfaces cannot drift apart. Never replace it with a custom drop zone.

### Navigation (the rail)
- **Style:** A 240px column on the Bond Paper ground with a 1px Rule Line right border, holding the circular CvSU seal and two-line Deep Seal wordmark, role-filtered links, and an account block pinned to the bottom behind a Rule Line divider.
- **Default / Active:** Idle items are Slate at 500 weight on no fill; hover takes a 60%-opacity Rule Line fill. The active item is lifted out of the desk onto a Record White pill (6px radius, Seat shadow) with Ink text at 600 weight — the same white as the content sheet, so the current destination reads as continuous with the page it opens. It also carries `aria-current="page"`.
- **No colored edge marker.** The active state is a fill change, never a thick tinted left border; the desk-to-sheet tonal shift already says it.
- **Focus:** Rings offset against the rail's own ground (`ring-offset-gray-100`), not white, so the ring never sits on a mismatched halo.
- **Account block:** Name in Ink, role beneath in Slate as plain text, then Profile and Log Out as ordinary rail items. Log Out is a real submit button styled to match, so the destructive-ish action is never a bare link.
- **Below 1024px:** the identical rail contents render inside a slide-over; the two instances never appear at once, so only one is ever in the accessibility tree.

### The Decision Queue (signature component)
The admin dashboard's working surface: the scholarships that still have applicants waiting, as ruled lists on the Record White sheet with **no card wrapper**, split by whether a pass list can settle them yet. A pass list rejects every pending applicant it does not name, so a scholarship belongs under **Ready for a pass list** only once students can no longer apply — OSAS closed it, its deadline passed, or every slot is filled. Those rows lead, longest wait first. The rest sit under **Still taking applications**, soonest deadline first, beneath one Slate line warning that a pass list rejects everyone not on it. Scholarships with nothing pending are left to the Scholarships index: the dashboard is a queue of work, not a register of the whole cycle.

Each row is a single link. The title sets in Ink at 600 weight and turns Seal Green on hover; beneath it a Slate meta line states availability first — "Closed by OSAS", "Closed 4 days ago", "Full", or "Closes in 9 days" — then "3 of 10 slots filled". Availability comes from the same check that stops applications, so a full or closed scholarship never reads as closing later. On the right, the pending count is an 18px Ink numeral at 600 weight in tabular figures over the word "pending", then "Review →" in Seal Green, which becomes a Slate chevron below 640px. Rows are divided by hairlines, fill Ledger Gray on hover without moving, and bleed 12px past the text column so the fill frames the text rather than clipping it. No row is red: the students' deadline is not the office's, so urgency is carried by group and position.

Above the queue sits **the standing line**: one sentence of ordinary prose with only the numbers emphasised. It counts pass lists due, not applications — "2 scholarships are ready for a pass list. 2 more are still taking applications." — or says plainly that no pass lists are due yet, that the office is caught up, or that nothing has been posted. It replaces a KPI tile grid outright.

### Page Loading Skeleton
A full-screen white overlay that intercepts same-origin link clicks and form submits, showing a nav-bar stub and three pulsing card placeholders in gray-200. It reproduces the real layout's proportions — 64px bar, `max-w-7xl` container, 24px-padded cards — so the transition doesn't jump. It self-clears on `pageshow` when the user returns via the back-forward cache.

## Do's and Don'ts

### Do:
- **Do** use Seal Green (#0B6E4F) for exactly three things: committing actions, forward navigation links, and settled/approved status. Deep Seal (#074A35) is its hover, never its resting state.
- **Do** put a 2px Citation Gold focus ring with a 2px offset on every green button. It is the system's signature interaction detail and it must be consistent. On white or neutral controls the ring is Seal Green instead — see the Two-Ring Rule.
- **Do** set every button label at 14px in 600 weight, sentence case, on every variant. Separate the tiers with fill, border, and corner radius.
- **Do** render every disabled control at 70% opacity with a not-allowed cursor.
- **Do** reserve gold for the unresolved and the focused — pending decisions and keyboard focus. If you are reaching for gold and neither applies, reach for a neutral.
- **Do** border cards with the same value as the page ground (#F3F4F6). Records sit in the desk, they don't hover over it.
- **Do** build hierarchy from Figtree's weights (400/500/600/700) and the six type roles. Never add a second font family.
- **Do** pick containers by task: `max-w-7xl` for administrative comparison surfaces, `max-w-5xl` for lists read top to bottom (the student's scholarship list, the admin decision queue), `max-w-sm` for auth forms.
- **Do** set every compared number in tabular numerals and right-align it in its column.
- **Do** say it in a sentence when a sentence will do. A line of prose naming what is waiting beats a row of tiles restating the same counts.
- **Do** pair every colour-or-glyph-only signal (an em-dash for "none", a coloured deadline) with `sr-only` text carrying the same meaning.
- **Do** write a real empty state for every list and table — a centered sentence at 14px in Mist or Slate, inside the card, saying what would appear here and what to do about it ("Nothing needs review right now.", "No scholarships have been posted yet.").
- **Do** give every progress or status graphic an accessible text equivalent, such as `role="img"` with a spelled-out aria-label.
- **Do** keep state transitions at 150ms `ease-in-out`, overlay entrances at 200–300ms, and overlay exits faster than their entrances (75–200ms).

### Don't:
- **Don't** reintroduce `focus:ring-indigo-500`. Both Laravel Breeze instances — the secondary button and the profile form's resend-verification button — have been removed; indigo belongs to no part of this palette. Pick a ring hue with the Two-Ring Rule.
- **Don't** uppercase a button label. `uppercase tracking-widest` was Breeze residue on the secondary and danger buttons and has been removed; uppercase belongs only to table column headers.
- **Don't** shrink a supporting button's type to signal that it is secondary. All three variants share 14px/600; the hierarchy lives in fill and border.
- **Don't** disable a control below 70% opacity. The Breeze default of 25% left the label unreadable.
- **Don't** add shadows to convey importance. Surfaces are flat at rest and stay flat on hover — deepen a border or add a heading instead. Only dropdowns and modals may exceed the Seat shadow.
- **Don't** lift, scale, or translate a card on hover. The interactive response is a border warming to `cvsu-green/40` or a Ledger Gray row fill.
- **Don't** introduce a third neutral between the page ground and the card border to "separate" a card.
- **Don't** use gold as a large surface fill or a general brand accent on interior screens. Its maximum interior footprint is a 20%-opacity pill.
- **Don't** put Seal Green on a destructive, dismissive, or cancelling control. Destructive is red (#DC2626) and nothing else.
- **Don't** collapse data tables into stacked cards on mobile — scroll them horizontally inside `overflow-x-auto`.
- **Don't** give any element a 0px corner radius.
- **Don't** add gradients, glass effects, decorative iconography, or animated counters to authenticated screens. The green gradient lives on the sign-in branding panel and nowhere else.
- **Don't** build a KPI tile grid. Counts belong in a sentence, or in a column next to the row they describe.
- **Don't** represent a distribution as a stacked bar, donut, progress ring, or sparkline. This system states quantities as aligned figures; a reader should never have to estimate a length to learn a number.
- **Don't** put navigation in a horizontal bar. The rail is the only wayfinding surface.
- **Don't** set gold or Mist as text. See the Gold Is Not Ink and Readable Neutral rules.
- **Don't** wrap a table or the decision queue in a card. A list of records is the document, not something sitting on top of one.
- **Don't** use `diffForHumans()` for a deadline. It phrases futures as "1 day from now" and truncates fractional days, so a date two days out reads as one. Count whole calendar days between day boundaries.
