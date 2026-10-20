{{-- Persistent rail from 1024px up. --}}
<aside class="hidden w-60 shrink-0 border-r border-gray-200 bg-gray-100 [view-transition-name:rail] lg:flex lg:flex-col">
    @include('layouts.rail-contents')
</aside>

{{-- Below 1024px the same rail becomes a slide-over. --}}
<div x-show="navOpen" x-cloak class="relative z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="{{ __('Main navigation') }}">
    <div x-show="navOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="navOpen = false"
         class="fixed inset-0 bg-gray-900/40"></div>

    {{-- With reduced motion the rail fades in where it rests instead of sliding across. --}}
    <div x-show="navOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full motion-reduce:opacity-0"
         x-transition:enter-end="translate-x-0 motion-reduce:opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0 motion-reduce:opacity-100"
         x-transition:leave-end="-translate-x-full motion-reduce:opacity-0"
         class="fixed inset-y-0 left-0 flex w-64 flex-col border-r border-gray-200 bg-gray-100 motion-reduce:!transform-none">
        @include('layouts.rail-contents')
    </div>
</div>
