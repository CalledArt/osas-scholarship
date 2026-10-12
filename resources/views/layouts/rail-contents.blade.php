@php
    $isAdmin = Auth::user()->isAdmin();
@endphp

<div class="flex h-full flex-col">
    <a href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}"
       class="flex items-center gap-2.5 px-5 py-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
        <x-application-logo class="h-9 w-9 shrink-0" />
        <span class="text-sm font-semibold leading-tight text-cvsu-green-dark">
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

    <div class="border-t border-gray-200 px-3 py-3">
        <div class="px-3 pb-2">
            <p class="truncate text-sm font-medium text-gray-900">{{ Auth::user()->full_name }}</p>
            <p class="truncate text-xs text-gray-500">{{ Auth::user()->role->label() }}</p>
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
                    class="block w-full rounded-md px-3 py-2 text-left text-sm font-medium text-gray-600 transition duration-150 ease-in-out hover:bg-gray-200/60 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2 focus-visible:ring-offset-gray-100">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</div>
