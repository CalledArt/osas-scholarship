@props(['heading', 'vtTitle' => false])

{{--
    The page's h1 as a banner: the dashboards' welcome, and the student
    Scholarships page's title. It follows the mock OSAS supplied for the admin
    dashboard (decided 2026-10-24); the student dashboard and Scholarships page
    took it on 2026-10-25: a rounded mint panel and a round icon disc, which
    DESIGN.md otherwise reserves against. Keep it as drawn. The slot is the
    status sentence beneath the heading; `action` sits on the right, below it
    on a phone; `corner` (the student's bell) stays top right at every width.
    A record's own page passes `vt-title` so its heading takes part in the
    cross-page title transition.
--}}
<div class="flex flex-col gap-4 rounded-lg border border-seal-tint bg-seal-wash px-5 py-4 sm:flex-row sm:items-center sm:px-6">
    {{-- On a phone the heading wraps, so the disc sits at its first line rather than floating beside the middle of the block. --}}
    <div class="flex min-w-0 flex-1 items-start gap-4 sm:items-center">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-seal-tint/60 text-seal" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4 2.5 9 12 14l9.5-5L12 4Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 11v4.5c0 1.4 2.7 3 6 3s6-1.6 6-3V11M21.5 9v5" />
            </svg>
        </span>

        <div class="min-w-0 flex-1">
            <h1 @if ($vtTitle) data-vt-title @endif class="text-lead font-semibold leading-tight text-ink [overflow-wrap:anywhere]">{{ $heading }}</h1>

            <p class="mt-0.5 text-base text-graphite">{{ $slot }}</p>
        </div>

        @isset($corner)
            <div class="shrink-0 self-start">{{ $corner }}</div>
        @endisset
    </div>

    {{ $action ?? '' }}
</div>
