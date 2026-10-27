{{--
    x-welcome-banner's chrome with its words still to come: the mint panel and
    its disc are drawn as they will be, the heading and status sentence as
    bars tinted from the panel's own green. The slot is the action's place.
--}}
<div class="draft-on-seal flex flex-col gap-4 rounded-lg border border-seal-tint bg-seal-wash px-5 py-4 sm:flex-row sm:items-center sm:px-6">
    <div class="flex min-w-0 flex-1 items-center gap-4">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-seal-tint/60 text-seal">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4 2.5 9 12 14l9.5-5L12 4Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 11v4.5c0 1.4 2.7 3 6 3s6-1.6 6-3V11M21.5 9v5" />
            </svg>
        </span>

        <div class="min-w-0 flex-1">
            <p data-draft-heading class="text-lead font-semibold leading-tight text-ink [overflow-wrap:anywhere]"><span class="draft-line draft-line--lead w-72"></span></p>
            <span class="draft-line mt-0.5 w-96"></span>
        </div>
    </div>

    {{ $slot }}
</div>
