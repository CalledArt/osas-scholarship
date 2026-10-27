---
paths:
  - resources/views/components/pdf-report.blade.php
  - resources/views/components/notification-bell.blade.php
---

# Components

## PDF reports share x-pdf-report and list students surname-first
PDF reports (today only admin/scholarships/report, the applications report; the fund allocation report is an Excel sheet) render inside x-pdf-report, which holds the masthead, meta, footer, signatures and shared CSS. Report-only CSS goes in its `styles` slot. The dompdf limits apply to all of them: CSS 2.1 tables (no flex/grid), DejaVu Sans for the ₱ glyph, footer shows only counter(page), enable_font_subsetting on, times in config('app.display_timezone'). dompdf cannot break a long unspaced string, so it squeezes every other column to fit one. Give wide tables `table-layout: fixed` with column widths, and middle-truncate uploaded filenames. Printed lists order by ScholarshipApplication::orderedByStudentName(), which sorts by lower(surname) because SQLite sorts case-sensitively ("de la Paz" would land after "Santos"). They show User::surname_first_name ("Dela Cruz, Juan") so the alphabetical order is visible on paper.

## Students' bell: database notifications, one query per page
New-scholarship notices live in the bell, not email (decided 2026-10-24). Admin ScholarshipController::store notifies the same audience the email reached (every student, or STEM courses only for the DOST preset) through App\Support\StudentNotifier. StudentNotifier bulk-inserts Laravel database-notification rows, 500 per insert, instead of one insert per student. Since 2026-10-25 (the user's request) the bell is an icon-only button in the top-right `corner` slot of the student dashboard's x-welcome-banner, not a rail item, and the strip's Gold dot on the menu button is gone with it. User::bellNotifications() loads the ten latest plus an unread_total subquery in one query; only DashboardController calls it, so no other page pays for it. Staff have no bell. The unread count is Gold, never Oxide. Opening a notification and Mark all as read are POST routes in the role:student group; another student's notification returns 404. The panel drops below the bell, right-aligned. Don't mix `@php(...)` with `@php ... @endphp` in the bell (or any one Blade file): the compiler breaks with "unexpected endforeach".

## Read notifications leave the bell after 24 hours
Decided 2026-10-24: opening a notification or Mark all as read sets read_at. The bell keeps a read notification (on white, no Gold dot) for User::READ_NOTIFICATION_HOURS (24), then User::bellNotifications() leaves it out, inside the same single query. Nothing runs on a schedule on Vercel, so StudentNotifier::send deletes rows read 24 hours ago or more each time a scholarship is posted. Until the next posting those rows stay in the table but hidden. Don't delete on read, and don't add a per-page cleanup query.
