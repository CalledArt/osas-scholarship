<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased selection:bg-cvsu-gold/40 selection:text-gray-900">
        <div class="flex min-h-screen flex-col lg:flex-row">
            {{-- The front door: the one place the system raises its voice. A title page with the seal pressed into it. --}}
            <header class="relative isolate overflow-hidden bg-cvsu-green-dark px-6 pb-10 pt-8 text-white sm:px-12 lg:flex lg:w-1/2 lg:flex-col lg:justify-between lg:p-12 xl:p-16">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,theme(colors.cvsu.green.light/50%),theme(colors.cvsu.green.DEFAULT/35%)_35%,transparent_70%)]"></div>

                {{-- A blind-embossed seal: two upright arcs of inscription between rings, like the impression on an official record. --}}
                <svg aria-hidden="true" viewBox="0 0 100 100" fill="none" stroke="currentColor" class="pointer-events-none absolute -right-48 -top-48 -z-10 hidden h-[36rem] w-[36rem] text-white/[0.08] lg:block xl:-right-40 xl:-top-40 xl:h-[42rem] xl:w-[42rem]">
                    <defs>
                        <path id="seal-arc-top" d="M 9.5,50 A 40.5,40.5 0 0,1 90.5,50" />
                        <path id="seal-arc-bottom" d="M 6.3,50 A 43.7,43.7 0 0,0 93.7,50" />
                    </defs>
                    <circle cx="50" cy="50" r="48" stroke-width="0.8" />
                    <circle cx="50" cy="50" r="45.6" stroke-width="0.3" />
                    <circle cx="50" cy="50" r="38" stroke-width="0.3" />
                    <circle cx="50" cy="50" r="35.6" stroke-width="0.8" />
                    <g font-family="Figtree, ui-sans-serif, system-ui, sans-serif" font-size="4.4" font-weight="600" letter-spacing="0.9" fill="currentColor" stroke="none" text-anchor="middle">
                        <text><textPath href="#seal-arc-top" startOffset="50%">CAVITE STATE UNIVERSITY</textPath></text>
                        <text><textPath href="#seal-arc-bottom" startOffset="50%">OFFICE OF STUDENT AFFAIRS AND SERVICES</textPath></text>
                    </g>
                </svg>

                <div class="flex items-center gap-3">
                    <x-application-logo class="h-11 w-11 shrink-0 lg:h-14 lg:w-14" />
                    <div class="leading-tight">
                        <p class="text-sm font-semibold text-cvsu-gold">Cavite State University</p>
                        <p class="text-xs text-white/75">Office of Student Affairs and Services</p>
                    </div>
                </div>

                <div class="mt-8 lg:my-10">
                    <h1 class="text-balance text-3xl font-bold leading-[1.05] tracking-[-0.02em] lg:text-5xl xl:text-6xl 2xl:text-7xl">
                        Scholarship <span class="lg:block">Allocation</span> <span class="lg:block">System</span>
                    </h1>
                    <p class="mt-4 max-w-md text-sm text-white/80 lg:mt-6 lg:text-base">
                        A single place for OSAS staff and scholars to manage scholarship applications, awards, and records.
                    </p>

                    <ol aria-label="How a scholarship moves through the system" class="mt-10 hidden max-w-md divide-y divide-white/15 border-y border-white/15 lg:[@media(min-height:820px)]:block">
                        <li class="grid grid-cols-[5.5rem_1fr] gap-4 py-3 text-sm">
                            <span class="font-semibold">Post</span>
                            <span class="text-white/75">OSAS opens a scholarship with its slots, deadline and required documents.</span>
                        </li>
                        <li class="grid grid-cols-[5.5rem_1fr] gap-4 py-3 text-sm">
                            <span class="font-semibold">Apply</span>
                            <span class="text-white/75">Students upload each required document from their CvSU account.</span>
                        </li>
                        <li class="grid grid-cols-[5.5rem_1fr] gap-4 py-3 text-sm">
                            <span class="font-semibold">Review</span>
                            <span class="text-white/75">OSAS reviews every applicant and the documents they sent.</span>
                        </li>
                        <li class="grid grid-cols-[5.5rem_1fr] gap-4 py-3 text-sm">
                            <span class="font-semibold">Decide</span>
                            <span class="text-white/75">One pass list settles the cohort, up to the slots available.</span>
                        </li>
                    </ol>
                </div>

                <p class="hidden text-xs text-white/70 lg:block">&copy; {{ date('Y') }} Cavite State University. All rights reserved.</p>
            </header>

            <main class="flex flex-1 flex-col justify-center bg-white px-6 py-10 sm:px-12 lg:w-1/2 lg:py-12">
                <div class="mx-auto w-full max-w-sm">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
