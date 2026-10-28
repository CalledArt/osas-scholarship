---
name: OSAS Scholarship Allocation System
description: A survey plan for scholarship capacity in Cavite State University's own green and gold — a scholarship is a parcel, its slots are countable, and a student's application is staked on one of them.
colors:
  linen: "#FBFAF7"
  linen-sunk: "#F4F2ED"
  seal: "#0B6E4F"
  seal-deep: "#064532"
  seal-tint: "#BFE5D6"
  seal-line: "#1C9A6F"
  ink: "#10213A"
  graphite: "#3D4654"
  slate: "#5A6472"
  rule: "#D8D4CB"
  rule-strong: "#8C8678"
  gold: "#FFC72C"
  gold-deep: "#B87F00"
  gold-wash: "#FDF3DA"
  grant: "#15803D"
  grant-deep: "#116430"
  grant-wash: "#DCFCE7"
  oxide: "#A8341F"
  oxide-deep: "#862715"
  oxide-wash: "#F7E4E0"
typography:
  display:
    fontFamily: "Archivo, ui-sans-serif, system-ui, sans-serif"
    fontSize: "3.25rem"
    fontWeight: 700
    lineHeight: 1.03
    letterSpacing: "-0.025em"
  figure:
    fontFamily: "Archivo, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2rem"
    fontWeight: 700
    lineHeight: 1
    letterSpacing: "-0.02em"
  lead:
    fontFamily: "Archivo, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: "1.625rem"
    letterSpacing: "normal"
  body:
    fontFamily: "Archivo, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: "1.375rem"
    letterSpacing: "normal"
  annot:
    fontFamily: "Archivo Narrow, Archivo, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 500
    lineHeight: "1rem"
    letterSpacing: "0.08em"
  mono:
    fontFamily: "Spline Sans Mono, ui-monospace, SFMono-Regular, Menlo, Consolas, monospace"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: "1.375rem"
    letterSpacing: "normal"
rounded:
  sheet: "2px"
  plan: "0px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "40px"
components:
  button-primary:
    backgroundColor: "{colors.seal}"
    textColor: "#FFFFFF"
    rounded: "{rounded.sheet}"
    padding: "10px 16px"
    typography: "{typography.body}"
  button-primary-hover:
    backgroundColor: "{colors.seal-deep}"
  button-secondary:
    backgroundColor: "{colors.linen}"
    textColor: "{colors.graphite}"
    rounded: "{rounded.sheet}"
    padding: "9px 16px"
    typography: "{typography.body}"
  button-secondary-hover:
    backgroundColor: "{colors.linen-sunk}"
  button-danger:
    backgroundColor: "{colors.oxide}"
    textColor: "#FFFFFF"
    rounded: "{rounded.sheet}"
    padding: "9px 16px"
    typography: "{typography.body}"
  input-text:
    backgroundColor: "#FFFFFF"
    textColor: "{colors.ink}"
    rounded: "{rounded.sheet}"
    padding: "9px 12px"
    typography: "{typography.body}"
  sheet:
    backgroundColor: "#FFFFFF"
    textColor: "{colors.graphite}"
    rounded: "{rounded.plan}"
    padding: "24px"
  box:
    backgroundColor: "#FFFFFF"
    textColor: "{colors.ink}"
    rounded: "{rounded.plan}"
    padding: "16px 20px"
    typography: "{typography.body}"
  box-pending:
    backgroundColor: "{colors.gold-wash}"
    textColor: "{colors.ink}"
    rounded: "{rounded.plan}"
    padding: "14px 20px"
    typography: "{typography.body}"
  box-free-tuition:
    backgroundColor: "{colors.grant-wash}"
    textColor: "{colors.grant-deep}"
    rounded: "{rounded.plan}"
    padding: "16px 20px"
    typography: "{typography.body}"
  box-cell-key:
    textColor: "{colors.slate}"
    padding: "10px 20px"
    typography: "{typography.annot}"
  title-block:
    backgroundColor: "{colors.linen-sunk}"
    textColor: "{colors.ink}"
    rounded: "{rounded.plan}"
    padding: "16px 24px"
    typography: "{typography.annot}"
  mark-pending:
    backgroundColor: "{colors.gold-wash}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sheet}"
    padding: "2px 8px"
    typography: "{typography.annot}"
  mark-awarded:
    backgroundColor: "{colors.grant-wash}"
    textColor: "{colors.grant-deep}"
    rounded: "{rounded.sheet}"
    padding: "2px 8px"
    typography: "{typography.annot}"
  mark-rejected:
    backgroundColor: "{colors.oxide-wash}"
    textColor: "{colors.oxide-deep}"
    rounded: "{rounded.sheet}"
    padding: "2px 8px"
    typography: "{typography.annot}"
  mark-neutral:
    backgroundColor: "{colors.linen-sunk}"
    textColor: "{colors.graphite}"
    rounded: "{rounded.sheet}"
    padding: "2px 8px"
    typography: "{typography.annot}"
  mark-paper:
    backgroundColor: "#FFFFFF"
    textColor: "{colors.ink}"
    rounded: "{rounded.sheet}"
    padding: "2px 8px"
    typography: "{typography.annot}"
  table-header:
    backgroundColor: "{colors.linen-sunk}"
    textColor: "{colors.slate}"
    padding: "10px 24px"
    typography: "{typography.annot}"
  rail-link-active:
    backgroundColor: "{colors.seal-deep}"
    textColor: "#FFFFFF"
    rounded: "{rounded.sheet}"
    padding: "9px 12px"
    typography: "{typography.body}"
  rail-link-idle:
    textColor: "{colors.seal-tint}"
    rounded: "{rounded.sheet}"
    padding: "9px 12px"
    typography: "{typography.body}"
---

# Design System: OSAS Scholarship Allocation System

## Overview

**Creative North Star: "Lot and Claim"**

A scholarship is a surveyed parcel. It has a bounded capacity you can count, a closing date in its title block, and lots that are either awarded or open. A student's application is a **claim staked on one lot** — on record, in a known position, against a capacity that is drawn rather than described.

That mapping is exact, not decorative. Slots are lots. The deadline is a closing date. The pass list is the recorded survey that settles every lot at once. "3 of 10 slots filled" stops being a sentence a worried student has to parse and becomes three hatched lots out of ten they can count in a glance, on a phone, in sunlight.

The surface is the drafting table that plan is drawn on. The shell — the navigation rail and the page's header band — is a committed field of **Seal Green**, a deep drafting ink. The working sheets laid on it are **Linen**, a warm off-white, because a plan is printed on stock and because pure white glares on a cheap Android screen outdoors. Structure is carried by **line weight and hatch density**, the way a drawing carries it: hairlines, witness marks, tick rails, and 45° hatching. Depth is not the medium here; measurement is.

Density is administrative and unapologetic — tight uppercase annotation in Archivo Narrow against generous drawing space — but body copy runs at **15px, not 14**, because the reader is often an anxious nineteen-year-old on mobile data who needs to read a decision, not skim a queue.

The system raises its voice exactly twice. **Citation Gold** appears only on a record that belongs to the person reading it. **Oxide** appears only on a refusal or a destructive control. Everything else is Seal Green, Seal Green, Grant green, or a neutral.

**Key characteristics**
- A Seal Green shell framing Linen sheets; no grey screen with one accent
- Capacity drawn as countable lots, never estimated as a bar or a ring
- Four interior type sizes; rank carried by weight, case, reversal and rule
- Two voices — Archivo for reading, Archivo Narrow for annotation — plus a mono reserved to values you must transcribe
- True corners on the plan geometry, 2px on the controls you touch
- Citation Gold means *yours*; Grant green means *settled*; Oxide means *refused*
- The student's Dashboard and Scholarships page are two panes of same-anatomy boxes; every other screen keeps sheets as containers, not scaffold

## Colors

Cavite State University's own two colours doing real work, on a warm paper stack that carries all the structure. There is no third hue: the drawing is ink.

### The shell
- **Seal Green** (#0B6E4F): the navigation rail and the narrow-screen strip. The shell is a committed colour field, not a tint — a grey screen with one accent is the category default without the category's polish. It is also the university's own green, which is why the CvSU seal sits *in* its shell rather than on top of a foreign one.
- **Seal Deep** (#064532): the active rail item, every Seal Green hover, and the front door's ground.
- **Seal Tint** (#BFE5D6): **secondary text on the shell.** Tinted from the green itself rather than greyed, at 4.6:1 on the rail and 8.1:1 on Seal Deep.
- **Seal Line** (#1C9A6F): hairlines inside the shell, and the light source of the front door's glow.

Seal Green is also the one action colour on paper: it fills primary buttons, colours every forward link, and draws the season bar's elapsed run. On a Linen sheet it reads 6.0:1.

### Paper and ink
- **Linen** (#FBFAF7): every reading surface. Sheets are white; Linen is the desk they lie on. A plan is printed on stock, and pure white glares on a phone outdoors.
- **Linen Sunk** (#F4F2ED): title blocks, table header rows, row hover.
- **Ink** (#10213A) / **Graphite** (#3D4654) / **Slate** (#5A6472): the only three text colours on paper — headings and figures, body, supporting copy.
- **Rule** (#D8D4CB) separates rows, which is decoration. **Rule Strong** (#8C8678) draws the strokes that carry meaning — input borders, open slot boundaries — and is dark enough to clear 3:1 on Linen, Linen Sunk and white alike.
- **Graphite** also hatches a slot held by an undecided application: plain drawing ink, because green here means granted and nothing about a waiting application is granted.

### Citation Gold — what is yours
- **Gold** (#FFC72C) / **Gold Deep** (#B87F00) / **Gold Wash** (#FDF3DA): the record that belongs to the person reading it, or the one still waiting on a human. The staked slot, the pending mark, the reader's own row. It is also the focus ring on every Seal Green surface — the gold-on-green pairing is this system's most recognisable interaction detail.

### Granted, and refused
- **Grant** (#15803D) / **Grant Deep** (#116430) / **Grant Wash** (#DCFCE7): awarded, settled, in force. A pale wash carrying dark green ink, far enough in value from the shell that the two never read alike.
- **Oxide** (#A8341F) / **Oxide Deep** (#862715) / **Oxide Wash** (#F7E4E0): the rejection stamp, a deadline inside three days, and destructive controls. Nothing else.

### Named rules

**The Committed Shell Rule.** The rail and strip are a Seal Green field. If a new shell element needs separation it takes a Seal Line hairline or a step to Seal Deep — never a grey.

**The Never-Grey-On-Colour Rule.** Secondary text on any coloured surface is tinted from that surface's own hue, never from the neutral ramp. Seal Tint exists precisely because the previous system set Slate on its coloured rail and landed at 4.39:1 — a real, measured failure.

**The Gold Rule.** Gold marks ownership and waiting, and nothing else. It is never a general accent and never a large fill. **On paper it is never text** (1.5:1 on Linen); there it appears only as a focus ring on green, as a wash behind a pending mark carrying Ink, and in the seal. On the Seal Deep front door it *is* legible as text at 7.1:1, and is used there for the university's name — the one place gold sets type.

**The One Hue Rule.** Green and gold are the only hues. Grant and Oxide are semantic states, not palette colours, and they appear only inside a mark, a wash, or a destructive control. If something new wants a colour and it is neither the university's nor a state, it is a neutral.

**The Countable Capacity Rule.** Quantities with a unit a reader cares about — slots, documents, days — are drawn as discrete countable marks or set as aligned figures. Never a progress bar, ring, donut, or sparkline. A reader must never estimate a length to learn a number they could have counted.

**The Product's Words Rule.** The world lends type, palette, density and the parcel. It never lends vocabulary. On screen a scholarship has **slots**, a student sends an **application**, and OSAS settles them with a **pass list** — the office's own words, which PRODUCT.md and the controller rules both use. "Parcel", "lot" and "claim" name the drawing in code, in CSS classes and in this document; they never appear in an interface string. A metaphor that starts renaming the product's nouns has stopped being a design and become a costume.

**The Readable Ink Rule.** On paper, only Ink, Graphite and Slate carry text. On the shell, only white and Seal Tint. Every colour-only or glyph-only signal pairs with an sr-only word carrying the same meaning.

## Typography

**Reading and controls:** Archivo (self-hosted, OFL 1.1, weights 400/500/600/700)
**Plan annotation:** Archivo Narrow (self-hosted, OFL 1.1, weights 500/600)
**Transcription:** Spline Sans Mono (self-hosted, OFL 1.1, weight 400)

Archivo is a grotesque built for small sizes and dense setting, with real tabular figures and a narrow companion that is a true separate design rather than a squeezed instance. That pairing is the whole type system: one face you read, one face that labels the drawing.

### Hierarchy — four interior sizes

- **Annot** (Archivo Narrow 500, 12px, +0.08em, uppercase): column headers, field labels, title-block keys, status marks. It labels the drawing and never forms a sentence.
- **Body** (Archivo 400, 15px/22px): all running copy, table cells, inputs, buttons, rail items. The default.
- **Lead** (Archivo 600, 18px/26px): the page `h1` and every section heading. One weight does both jobs; the page heading is distinguished by position and by the title block beneath it, not by a fifth size.
- **Figure** (Archivo 700, 32px, tabular): **one per region.** The number the region exists to report — days waiting, lots remaining, pesos released.

**Display** (Archivo 700, 52px) exists for the unauthenticated front door only and appears nowhere inside the application.

### Named rules

**The Four Sizes Rule.** The authenticated application has exactly four type sizes, published as tokens. No component may introduce a fifth, and no component may reach for `display`. When something needs to feel more important, it changes weight, case, or reversal, or it gains a rule above it — it does not get bigger. The previous system drifted to a welcome line at 18px sitting above its own section headings at 16px; a fixed scale makes that impossible.

**The Annotation Rule.** Uppercase with tracking belongs to Archivo Narrow at Annot size, and only ever to the labels around data: table column headers, title-block keys, status marks. It never touches a sentence, a heading, a button, or a paragraph of statute.

**The One Figure Rule.** A region reports one number at Figure size. If two numbers compete, one of them is Body — rank them, or the reader ranks them wrongly. This is also what keeps the system off the hero-metric template: a Figure always sits inside a sentence or a title block that says what it counts, never in a tile of its own.

**The Transcription Rule.** Spline Sans Mono is for values a reader must copy exactly: student numbers, application reference numbers, literal CSV headers. Wanting to look technical is not a reason to reach for it.

**The Tabular Default.** `font-variant-numeric: tabular-nums` is set on `html`, so every figure in the product aligns without a component having to remember. The previous system made this a rule and then lost it on exactly the columns it warned about.

## Layout

A two-column shell: a fixed **240px** Seal Green rail and a content column. There is no horizontal navigation bar at any breakpoint.

**Containers**, chosen by the reader's task:
- **`max-w-plan` (1280px)** — wide administrative comparison surfaces, where columns must be read against each other.
- **`max-w-sheet` (1024px)** — lists read top to bottom.
- **`max-w-leaf` (672px)** — a single record, and all prose. This also keeps running text inside a 65–75 character measure.
- **`max-w-stub` (352px)** — auth forms.

**Vertical rhythm.** Content regions open at 40px (`py-10`). Sibling sheets stack at 24px. Sheet interiors are 24px; list rows are 24px by 12px. Form fields stack at 20px. More space sits above a heading than below it, so a heading binds to what it introduces.

**Grids.** Paired detail panels run one column to two at 1024px. Only `sm` (640px) and `lg` (1024px) do real work.

**The student panes.** The student Dashboard and Scholarships page sit in `max-w-[100rem]` (1600px, which fills a 1920px screen beside the rail) as two panes of boxes on the Linen desk, separated by a 24px gap. From 1024px the left pane is a fixed **22rem** column headed "Where you stand" (on the Dashboard: the student's ID box, Republic Act 10931, the active scholarship, places left, then "My applications"); the right pane, headed "Available to apply" with its count in Slate, is a two-up grid of open-scholarship boxes. Boxes inside a pane stack or tile at 16px. From 640px the Where-you-stand boxes run two-up and the Available grid is already two-up; below 640px everything is one column. On a phone the Dashboard's "My applications" follows the open scholarships, so the student's standing and what they can act on come first; on wide screens it closes the left column, which takes an `auto` then `1fr` row so a long right pane never opens a gap beneath it. Both pages keep two panes only while there are open boxes to fill the right one. When nothing can be applied to (an active scholarship held, two applications waiting, on leave, or nothing open), the panes stack: the Where-you-stand boxes run three across in equal-height cells from 1280px (the ID box full width over the other two from 640px), then the empty Available box and My applications (three-up from 1280px) span the width, so one sentence never sits beside a tall column. Above the panes, the student Dashboard's h1 is the admin dashboard's welcome banner (x-welcome-banner, at the user's request): "Welcome back, {name}!" over one status clause (applications waiting for a decision, then scholarships open to them, or "You're all caught up."). The Scholarships page takes the same banner with "Scholarships" as its heading, over "N scholarships are open to you." or "Nothing is open for you to apply to right now." My Applications takes it too, headed "My Applications" over the count still waiting for a decision, "You're all caught up.", or "Your applications appear here once you apply.", and runs at the same 100rem. At the user's request (2026-10-25) its applications are cards drawn like the admin Fund Allocation cards, a third exception to the Two Panes Rule: an auto-fill grid of cards at least 20rem wide, each with the scholarship's title and the application's status mark, its type and status, Reference (in mono) and Applied cells under a rule, "N days waiting" while pending, and a "View application" cue. The admin Scholarships list follows suit (same date, the user's request, chosen over a table): the banner headed "Manage Scholarships" with Post Scholarship as its action, at 100rem, and one card per scholarship (status mark, type, Slots left and Applicants cells, closing date, View and Edit), an admin exception to sheets-not-scaffold. A scholarship's own admin page follows too: its title in the banner with status, type, slots remaining and deadline, Fund Allocation and Edit as the action, then Upload Pass List and Applicants as bordered panels beside a sticky About this scholarship panel whose long description scrolls inside it. A student's own application page takes the banner too (title, status mark and type, View the scholarship), keeps its title block, and sets the Post/Apply/Review/Decide register and Documents you sent as bordered panels side by side from 1024px. The Republic Act 10931 page takes the banner and its title block, then sets What this means for you beside the full text of the Act, which stays in view and scrolls inside its own panel; the Act's wording is untouched. Both Fund Allocation pages take the banner and 100rem too: the list headed "Fund Allocation" over the count of funds still to set, and each scholarship's page headed by its title over its status and type, with View scholarship and Download Excel sheet as the action. Search Student takes the banner ("Find registered students by name, email or student number.") and 100rem, its filters in one row from 1024px, and its roster stays a table, now in a bordered panel like the admin dashboard's postings table. From 1024px the page fits the screen and only that list scrolls, under a sticky header row. Edit Student Data is drawn the same way, its rows closing on an Edit button; its edit form takes the banner (headed by the student's name) and one card whose sections set their title on the left from 1024px, with the save bar at the card's foot. The student Profile copies the staff Profile (the user's request): a plain "Profile" heading at 88rem and one compact card of rows (Profile information, Personal details, and a read-only Enrollment row that says once, in its description, that OSAS keeps it) with one Save, fitting one laptop screen. Like the admin banner, it is a deliberate exception to the True Corner and One Hue rules.


**Responsive.** Below 1024px the rail leaves the flow and becomes a slide-over over a Seal Green scrim, opened from a 56px Seal Green strip that carries the trigger, the seal, and the current page's name. Data tables scroll horizontally inside `overflow-x-auto`; they never collapse into stacked cards, because a parcel's lots, closing date and status only mean anything beside each other.

### Named rules

**The Task-Container Rule.** Pick the container from what the reader is doing, not from how much content there is. 1280px means "compare columns". 1024px means "read down a list". 672px means "read a record or a sentence".

**The Rail-Only Rule.** Wayfinding lives in the rail. The narrow-screen strip is a disclosure control plus a page label; it must never grow links, a search field, or an account menu.

**The Named Page Rule.** Every authenticated screen sets a page title, and the narrow strip prints the current page's name. The previous system shipped a `$header` slot no view ever filled and a single `<title>` for the whole application, which left every page announcing itself identically.

**The Two Panes Rule.** The student Dashboard and Scholarships page are the one approved place where a grid of same-size boxes is the page's structure. It holds only there and only under three limits: every box in a grid sets the same facts in the same cells, so two can be compared at a glance; a box never nests inside a box; and the boxes carry the student's own record and the scholarships open to them, nothing ornamental. Admin surfaces are unchanged and keep sheets as containers, not scaffold.

## Elevation and depth

**This system measures; it does not float.** Separation comes from the Seal Green/Linen field change, from hairlines, and from hatch density. Where a shadow does appear it carries a real offset and a soft blur, tinted from Ink rather than black — a zero-offset coloured halo is decoration, not depth.

- **Seat** (`0 1px 2px 0 rgb(16 33 58 / 0.06)`): sheets, inputs, primary buttons. Edge definition only.
- **Lift** (`0 10px 24px -8px rgb(16 33 58 / 0.22)`): dropdowns.
- **Sheet** (`0 20px 40px -12px rgb(16 33 58 / 0.28)`): modals, over a Seal Green scrim at 55%.

Hover never lifts. An interactive sheet warms its border to Seal Green; a list row fills Linen Sunk. The surface stays exactly where it is.

## Shapes

The plan geometry keeps **true corners**: parcels, lots, title blocks, sheets, tables and the season bar are square, because a survey drawing has no rounded parcels. Controls you touch — buttons, inputs, marks, icon buttons — take **2px**, just enough to read as handles. Status marks are rectangular, not pills: a mark is a stamp on a drawing, not a bubble.

Borders are 1px, with two deliberate exceptions where thickness is information: the **2px Seal Green foot** on every fifth lot, and the **2px rule** under a title block's key row.

### Named rules

**The True Corner Rule.** If it is part of the drawing, it is square. If it is a control, it is 2px. There is no third radius, and nothing in the authenticated application is fully round except the seal.

**The No Costume Rule.** The world lends type, palette, density, and the parcel. It does not lend the navigation model or the controls. Every control is the standard one a visitor already knows — real buttons, tabs, inputs, selects, tables with sortable headers — never a drafting instrument's bezel, a drawn ruler standing in for a slider, or ruled paper standing in for a table.

## Components

### The Parcel (signature component)

A scholarship's capacity, drawn. A flex row of `.lot` squares, exactly one per slot, at 13px (`--lot-edge`) or 18px in the `.parcel--wide` variant used on a scholarship's own page.

- **Open lot:** Linen fill, 1px Rule Strong boundary.
- **Awarded lot:** Grant Wash fill with 45° Grant hatching, Grant Deep boundary.
- **Goldd lot** (the reader's own pending claim): Gold Wash fill, Gold Deep boundary, a 1px Gold inset, and a Gold Deep corner triangle.
- **Goldd and awarded:** the Grant hatch with the Gold inset retained.
- Every **fifth lot** carries a 2px Seal Green foot, so a parcel of fifteen reads as fifteen without counting one at a time.

Lots are laid in **groups of five that wrap as units**, so a parcel that runs out of width breaks 20 + 5, never 21 + 4, and the fifth-lot foot keeps its rhythm on every line. Groups and lots are both spaced 2px.

The parcel always sits beside the same figures in Annot and Body — `8 OF 10 LOTS OPEN` — so the drawing is an accelerant for a number that is also written. It is never the only statement of a quantity, and it always carries an `sr-only` sentence giving the same facts in words. The written caption is **optional only where the containing box already writes the figure in a cell**, as an open-scholarship box does in its Slots left cell; the `sr-only` sentence stays regardless.

### The Title Block

The plan convention, and the home of the cycle register carried over from the previous system — the strongest thing in it. A Linen Sunk block, 16px by 24px, under a 2px rule, holding Annot keys over Body or Figure values: the award's name, its reference, the closing date, the capacity, and the stage the record has reached (Posted, Applied, Under review, Decided) with dates on the stages that have happened and none on the stages ahead.

It keeps `view-transition-name: cycle`, so navigating between a scholarship and a claim holds the block still and changes only the filled row. The register is the document; only the entered line moves.

It is **not** a kicker. It carries real values, it sits beneath the heading it belongs to, and it is never a label floating above a title.

### The Season Bar

A closing date made measurable: a 3px rule whose width is the window from posting to deadline, with `--elapsed` marking today as a 2px Ink witness line. Past the deadline the whole run is struck in Oxide and the witness line is removed. It reports a date that is also written out in full beside it.

### Buttons
- **Primary:** Seal Green fill, white text, Body at 600, 16px by 10px, 2px radius, Seat shadow. Hover deepens to Seal Deep over 150ms.
- **Secondary:** Linen fill, Rule Strong border, Graphite text, same type, Linen Sunk on hover.
- **Danger:** Oxide fill, white text. The only red fill in the system.
- Every variant sets its label in Body at 600, sentence case. Tiers are separated by fill and border, never by type size and never by uppercase.
- **Disabled:** 70% opacity with a not-allowed cursor. **Loading:** the label swaps to a progress phrase, the button disables and keeps its width, and a 16px ring spins. Any submit button without its own phrase gives up its label to the ring the moment it is pressed, keeping its width and refusing a second press until the next page.

**The Two-Ring Rule.** A focus ring is 2px at 2px offset, and its hue comes from the surface it sits on: **Gold** on any Seal Green surface — the primary button, the rail items, the strip trigger — which is the signature pairing and reads at 4.0:1 against the green; **Seal Green** on any Linen or white control; and the control own hue on a semantically coloured control. The ring offset colour is the surface behind it, so a ring never sits on a mismatched halo. A global focus-visible floor in app.css guarantees nothing is ever left wearing the browser default outline — which the previous system left on its mobile wordmark.

### Marks (status)
Rectangular stamps at 2px radius, 8px by 2px, Annot size, uppercase: **pending** (Gold Wash on Ink), **awarded** (Grant Wash on Grant Deep), **rejected** (Oxide Wash on Oxide Deep), **neutral** (Linen Sunk on Graphite). Read-only output — never a filter, never a button, never dismissible.

### Sheets
White, true corners, 1px Rule border, Seat shadow, 24px padding. A titled sheet uses a 24px by 16px header block with a 1px bottom rule, then a `divide-y` list of 24px by 12px rows. **Sheets are not a page scaffold:** a grid of same-size sheets each holding an icon, a heading and a line of text is not how a screen here is organised, and a sheet never nests inside a sheet. The one approved exception is the student Dashboard and Scholarships page, which are built from boxes under the Two Panes Rule (see Layout and Boxes).

### Boxes (student Dashboard and Scholarships)
The sheet's material at a smaller unit: **white, true corners, 1px Rule border, Seat shadow**, 20px by 16px inside. A box that opens something is the whole link: its border warms to **Seal Green** on hover over 150ms and its title goes Seal Green with it; nothing lifts. A box title is Body at 600 in Ink, never a larger size.

- **Key cells.** Facts the reader compares sit in a row of cells under a 1px Rule top border, each cell 20px by 10px and divided from its neighbour by a 1px Rule: an **Annot key in Slate over a Body value in Ink** ("Closes" over the date and its days-left line; "Slots left" over "8 of 12"; "Student number" over the number in Spline Sans Mono).
- **Open-scholarship box.** Title with its type mark top right; the parcel without its caption; the Closes and Slots left cells; then a footer row under a 1px Rule with the document count in Slate and a Seal Green **Apply** plate, which is a cue inside the link rather than a second control. Every box in the grid carries exactly these cells in this order.
- **The student's own waiting application** is a **Gold Wash box with a Gold Deep border**, and its status mark is the **paper** mark (white, Ink), because a Gold Wash mark on a Gold Wash box disappears. Decided or voided applications stay white boxes with their usual marks.
- **Republic Act 10931** is a **Grant Wash box** with a Grant hairline at 30% that warms to full Grant on hover, its text in Grant Deep, and the school years it covers drawn as wide lots, the years reached hatched as awarded. While the student is on leave it falls back to a white box saying so.
- **Places left.** One box states how many more scholarships the student can apply to as a sentence carrying a single Figure, with the places drawn beneath as wide lots, the used ones staked in Gold. While nothing can be applied to, it says why in one short sentence instead, and the Available pane shows one empty box rather than repeating the reason.
- **Empty.** An empty pane is one white box, 20px by 32px, with a centred Slate sentence.

**The One Clause Rule.** On the student panes, copy states the count or the reason in one short clause and stops. It never explains the rules behind it: "You can apply to 1 more scholarship.", not a paragraph on the limit before the limit is shown.

### Inputs
White fill, 1px Rule Strong border, 2px radius, Body size, 12px by 9px. **Focus:** border and ring both go Seal Green. **Error:** the message renders in Oxide at Body size, 8px below the field, and the field takes an Oxide border — the error container renders unconditionally so `aria-describedby` is never a dangling reference. File fields render through one component with `::file-selector-button` styled as a secondary button; never a custom drop zone.

### The rail
A 240px Seal Green column with a Seal Green Line right border, holding the CvSU seal and a two-line white wordmark, role-filtered links, and an account block pinned to the bottom behind a Seal Green Line divider. Idle items are Seal Tint; hover fills Seal Deep at 60%; the active item is a Seal Deep plate with white text at 600 and `aria-current="page"`. The account block's name is white and the role beneath it is Seal Tint — never grey. Log Out is a real submit button styled to match. Below 1024px the identical contents render in a slide-over, which **moves focus in on open, traps it while open, returns it to the trigger on close**, and marks the page behind it `inert`.

### Drafts and loading
A page that has not answered within 300ms is **drafted**: the page in hand gives way to the one asked for, drawn in its real chrome (banner, panels, rules, title blocks and any fixed words), with its heading when it is known and **square bars** where its records will be. The bars are one text line tall and tinted from the surface they sit on: Rule Strong on paper, the panel's own hue on a coloured one, never grey on colour. One light crosses all of them at once, left to right, every 2.2s. A record's title rises from the row that was pressed into the draft's heading and stays there while the page arrives over it. The rail's plate moves to the item pressed at once, and on a phone the slide-over closes and the strip names the page being opened. A search, sort or page change keeps the filters on screen and dims only the list it replaces. Fields are drafted as white frames in Rule, lighter than a live field's Rule Strong. The **progress hairline** runs across the content column with a 2px Ink witness tick at its head, like the season bar's "today". Over the Seal Green strip it is drawn in Gold with a white tick, and the next page runs it out to the end before it fades. Under reduced motion nothing sweeps or travels: the bars breathe in place and the hairline appears at full width.

### Empty states
Every list and table has one: a centred Body sentence in Slate inside the sheet, saying what would appear there and what to do about it. A heading never renders above an empty container — the previous system printed "Documents you sent" over an empty box on the rejection screen, which reads as lost paperwork.

## Do's and Don'ts

### Do
- **Do** make the shell a Seal Green field, and tint any secondary text on it from Seal Green rather than greying it.
- **Do** draw a capacity as countable lots and write the same number beside them.
- **Do** mark the reader's own record in Gold, and nothing else in Gold.
- **Do** keep to four interior type sizes, and change weight, case or rule when something needs rank.
- **Do** put uppercase tracking only on Archivo Narrow annotation around data.
- **Do** give every region exactly one Figure, inside a sentence or a title block that says what it counts.
- **Do** set a page title on every screen and print the page's name in the narrow strip.
- **Do** write a real empty state for every list, and never render a heading above an empty container.
- **Do** state the elapsed wait on a pending record. The office can already see it; the student is the one who needs it.
- **Do** print a record's reference number wherever that record is shown, in Spline Sans Mono.
- **Do** pair every colour-only or glyph-only signal with `sr-only` words.
- **Do** build the student Dashboard and Scholarships page as two panes of same-anatomy boxes, each setting the same facts in the same Annot-over-Body cells.
- **Do** state a count or a reason on the student panes in one short clause.
- **Do** keep state transitions at 150ms, overlay entrances at 200–300ms with an exponential ease-out, and exits faster than entrances.

### Don't
- **Don't** reintroduce a neutral shell with one accent colour. That is the look this system replaced.
- **Don't** set grey text on any coloured surface. Tint it from the surface's hue.
- **Don't** use Gold as a text colour, a brand accent, or a large fill.
- **Don't** put Seal Green on a destructive, dismissive or cancelling control. Destructive is Oxide and nothing else.
- **Don't** represent a quantity as a progress bar, ring, donut, or sparkline.
- **Don't** add a fifth interior type size, and don't use `display` inside the application.
- **Don't** put a kicker or eyebrow above a heading. The heading carries its own weight. A title block goes beneath.
- **Don't** build a grid of same-size sheets as a page's structure, and never nest a sheet in a sheet. The student Dashboard and Scholarships page are the one approved exception, under the Two Panes Rule; the no-nesting half holds there too.
- **Don't** explain the rules on a student surface before or instead of showing the count. One clause, then the drawing.
- **Don't** uppercase a button label or shrink a secondary button's type.
- **Don't** round the plan geometry, and don't introduce a third radius.
- **Don't** dress a standard control as a drafting instrument. The world lends type, palette, density and the parcel — never the controls or the navigation model.
- **Don't** collapse a data table into stacked cards on mobile; scroll it inside `overflow-x-auto`.
- **Don't** use a glyph or emoji in place of an icon. Icons are authored SVG at one consistent stroke.
- **Don't** use `diffForHumans()` for a deadline. It phrases futures as "1 day from now" and truncates fractional days, so a date two days out reads as one. Count whole calendar days between day boundaries.
- **Don't** show a deadline alone on a scholarship that is full, closed or ended. State the binding reason first; a bare deadline reads as open.
