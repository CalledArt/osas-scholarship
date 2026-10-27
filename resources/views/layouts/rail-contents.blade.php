@php
    $isAdmin = Auth::user()->isAdmin();
@endphp

<div class="flex h-full flex-col">
    <a href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}"
       class="flex items-center gap-2.5 px-5 py-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-gold">
        <x-application-logo class="h-9 w-9 shrink-0" aria-hidden="true" />
        <span class="font-semibold leading-tight text-white">
            {{ __('OSAS Scholarship') }}<br>{{ __('Allocation System') }}
        </span>
    </a>

    <nav class="flex-1 space-y-1 px-3 pt-2" aria-label="{{ __('Main navigation') }}">
        @if ($isAdmin)
            <x-rail-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" data-tour="nav-dashboard">
                {{ __('Dashboard') }}
            </x-rail-link>
            <x-rail-link :href="route('admin.scholarships.index')" :active="request()->routeIs('admin.scholarships.*')" data-tour="nav-scholarships">
                {{ __('Scholarships') }}
            </x-rail-link>
            <x-rail-link :href="route('admin.fund-allocation.index')" :active="request()->routeIs('admin.fund-allocation.*')" data-tour="nav-allocation">
                {{ __('Fund Allocation') }}
            </x-rail-link>
            <x-rail-link :href="route('admin.roster.index')" :active="request()->routeIs('admin.roster.*')" data-tour="nav-roster">
                {{ __('Search Student') }}
            </x-rail-link>
            <x-rail-link :href="route('admin.students.index')" :active="request()->routeIs('admin.students.*')" data-tour="nav-students">
                {{ __('Edit Student Data') }}
            </x-rail-link>
        @else
            <x-rail-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" data-tour="nav-dashboard">
                {{ __('Dashboard') }}
            </x-rail-link>
            <x-rail-link :href="route('scholarships.index')" :active="request()->routeIs('scholarships.*')" data-tour="nav-scholarships">
                {{ __('Scholarships') }}
            </x-rail-link>
            <x-rail-link :href="route('scholarship-applications.index')" :active="request()->routeIs('scholarship-applications.*')" data-tour="nav-applications">
                {{ __('My Applications') }}
            </x-rail-link>
        @endif
    </nav>

    <div class="border-t border-seal-line px-3 py-3">
        {{-- The role sits in Prussian Tint, drawn from the shell's own hue. Grey
             here is what put the previous system's account block at 4.39:1. --}}
        <div class="px-3 pb-2">
            <p class="font-medium uppercase text-white [overflow-wrap:anywhere]">{{ Auth::user()->surname_first_name }}</p>
            <p class="truncate font-annot text-annot uppercase text-seal-tint">{{ Auth::user()->role->label() }}</p>
        </div>

        <x-rail-link :href="route('tutorial')" :active="request()->routeIs('tutorial')" data-tour="nav-tutorial">
            {{ __('Tutorial') }}
        </x-rail-link>

        <x-rail-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')" data-tour="nav-profile">
            {{ __('Profile') }}
        </x-rail-link>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit"
                    class="block w-full rounded-sheet px-3 py-2 text-left font-medium text-seal-tint transition duration-150 ease-in-out hover:bg-seal-deep hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-seal">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</div>
