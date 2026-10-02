<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <x-page-progress />

        <div x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false" @tutorial-nav.window="navOpen = $event.detail" class="min-h-screen bg-gray-100 lg:flex">
            @include('layouts.rail')

            <div class="flex min-h-screen min-w-0 flex-1 flex-col {{ $sheet ? 'bg-white' : 'bg-gray-100' }}">
                <!-- Rail trigger, below the rail's own breakpoint -->
                <div class="flex items-center gap-2 border-b border-gray-200 bg-white px-4 py-3 lg:hidden">
                    <button type="button"
                            @click="navOpen = true"
                            class="-ml-2 inline-flex items-center justify-center rounded-md p-2 text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green"
                            aria-label="{{ __('Open navigation') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>

                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="h-7 w-7 shrink-0" />
                        <span class="text-sm font-semibold text-cvsu-green-dark">{{ __('OSAS Scholarships') }}</span>
                    </a>
                </div>

                @isset($header)
                    <div class="border-b border-gray-200 bg-white px-6 py-5 lg:px-10">
                        {{ $header }}
                    </div>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
