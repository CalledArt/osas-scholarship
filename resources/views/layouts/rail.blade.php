{{-- Persistent rail from 1024px up. The shell is a Prussian field, not a tint. --}}
{{-- Pinned to the screen (asked 2026-10-25): it stays put while a long page scrolls beside it, with Log Out always in reach. It only scrolls itself if the window is too short to hold it. --}}
<aside class="hidden w-rail shrink-0 border-r border-seal-line bg-seal [view-transition-name:rail] lg:sticky lg:top-0 lg:z-30 lg:flex lg:h-screen lg:flex-col lg:self-start lg:overflow-y-auto">
    @include('layouts.rail-contents')
</aside>

{{--
    Below 1024px the identical contents become a slide-over. It is a real
    dialog: focus moves in on open, the page behind it is inert so there is no
    keyboard path out, and focus returns to the trigger on close. Both
    instances are never present at once, so only one is ever in the
    accessibility tree.
--}}
<div x-show="navOpen" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="{{ __('Main navigation') }}">
    <div x-show="navOpen"
         x-transition:enter="transition ease-sheet duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeNav()"
         class="fixed inset-0 bg-seal-deep/55"></div>

    {{-- With reduced motion the rail appears where it rests instead of sliding across. --}}
    <div x-show="navOpen"
         x-ref="navPanel"
         x-transition:enter="transition ease-sheet duration-200"
         x-transition:enter-start="-translate-x-full motion-reduce:opacity-0"
         x-transition:enter-end="translate-x-0 motion-reduce:opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0 motion-reduce:opacity-100"
         x-transition:leave-end="-translate-x-full motion-reduce:opacity-0"
         class="fixed inset-y-0 left-0 flex w-rail flex-col border-r border-seal-line bg-seal shadow-sheet motion-reduce:!transform-none">
        @include('layouts.rail-contents')
    </div>
</div>
