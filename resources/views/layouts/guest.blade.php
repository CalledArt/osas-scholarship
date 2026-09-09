<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen">
            <!-- Branding panel -->
            <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-cvsu-green-dark via-cvsu-green to-cvsu-green-light p-12 text-white lg:flex">
                <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-cvsu-gold/10"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-white/5"></div>

                <div class="relative flex items-center gap-3">
                    <x-application-logo class="h-14 w-14 shrink-0" />
                    <div class="leading-tight">
                        <p class="text-sm font-semibold text-cvsu-gold">Cavite State University</p>
                        <p class="text-xs text-white/70">Office of Student Affairs and Services</p>
                    </div>
                </div>

                <div class="relative">
                    <h1 class="text-4xl font-bold leading-tight">
                        Scholarship<br>Allocation System
                    </h1>
                    <p class="mt-4 max-w-sm text-white/80">
                        A single place for OSAS staff and scholars to manage scholarship applications, awards, and records.
                    </p>
                </div>

                <p class="relative text-xs text-white/60">&copy; {{ date('Y') }} Cavite State University. All rights reserved.</p>
            </div>

            <!-- Form panel -->
            <div class="flex w-full flex-col justify-center bg-gray-50 px-6 py-12 sm:px-12 lg:w-1/2 lg:bg-white">
                <div class="mx-auto w-full max-w-sm">
                    <div class="mb-8 flex flex-col items-center gap-3 lg:hidden">
                        <x-application-logo class="h-14 w-14" />
                        <div class="text-center">
                            <p class="font-semibold text-cvsu-green">Cavite State University</p>
                            <p class="text-xs text-gray-500">OSAS Scholarship Allocation System</p>
                        </div>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
