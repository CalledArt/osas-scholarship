<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0B6E4F">

        <title>{{ $documentTitle() }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{--
            Cross-page transitions (styled in app.css). The record being opened
            grows from its row into the next page's heading, and back again on
            the way out. Exactly one element on each side takes the shared name,
            chosen as the navigation happens, because a page that names two
            skips the transition. Inline and in the head, because pagereveal
            fires before the first render, ahead of the deferred app bundle;
            the expect link holds that render until <main> has been parsed.
        --}}
        <link rel="expect" href="#page-end" blocking="render">
        <script>
            (function () {
                if (! ('onpagereveal' in window)) {
                    return;
                }

                var pathOf = function (url) {
                    try {
                        return new URL(url, window.location.href).pathname;
                    } catch (error) {
                        return null;
                    }
                };

                // The row linking to the other page, or failing that this page's own heading.
                var titleFor = function (path) {
                    var links = document.querySelectorAll('a[href]');

                    for (var i = 0; i < links.length; i++) {
                        if (pathOf(links[i].href) === path) {
                            var title = links[i].hasAttribute('data-vt-title') ? links[i] : links[i].querySelector('[data-vt-title]');

                            if (title) {
                                return title;
                            }
                        }
                    }

                    return document.querySelector('h1[data-vt-title]');
                };

                var name = function (element, transition) {
                    if (! element) {
                        return;
                    }

                    element.style.viewTransitionName = 'record-title';
                    transition.finished.finally(function () {
                        element.style.viewTransitionName = '';
                    });
                };

                window.addEventListener('pageswap', function (event) {
                    if (event.viewTransition && event.activation && event.activation.entry) {
                        // While a draft stands in for the next page (x-page-skeleton), its heading is the one that carries over.
                        name(document.querySelector('[data-draft] [data-vt-title]') || titleFor(pathOf(event.activation.entry.url)), event.viewTransition);
                    }
                });

                window.addEventListener('pagereveal', function (event) {
                    var from = window.navigation && window.navigation.activation && window.navigation.activation.from;

                    if (event.viewTransition && from) {
                        name(titleFor(pathOf(from.url)), event.viewTransition);
                    }
                });
            })();
        </script>

        {{--
            Fetch a page while the pointer rests on its link (or is pressed on a
            phone), so the server's answer is on its way before the click lands.
            Prefetch only, never prerender: a prefetch runs no script, and Laravel
            leaves the session's previous URL alone for it, which prerender's
            Sec-Purpose does not match. Every GET here is read-only; the report
            builds and stored files are left out because each one is a download.
        --}}
        <script type="speculationrules">
            {
                "prefetch": [{
                    "where": {
                        "and": [
                            { "href_matches": "/*" },
                            { "not": { "or": [
                                { "href_matches": "/*/report" },
                                { "href_matches": "/scholarship-applications/*/documents/*" },
                                { "href_matches": "/scholarships/*/required-documents/*" }
                            ] } }
                        ]
                    },
                    "eagerness": "moderate"
                }]
            }
        </script>
    </head>
    <body class="bg-linen font-sans text-body text-graphite antialiased">
        <x-page-progress />

        {{-- First focusable element on every screen: past the rail, straight to the work. --}}
        <a href="#main"
           class="sr-only rounded-sheet focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:block focus:bg-seal focus:px-4 focus:py-2.5 focus:font-semibold focus:text-white focus:shadow-sheet focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2">
            {{ __('Skip to content') }}
        </a>

        {{-- A link pressed in the slide-over closes it at once, so the page being opened shows. --}}
        <div x-data="appShell"
             @keydown.escape.window="closeNav()"
             @tutorial-nav.window="$event.detail ? openTourNav() : closeNav()"
             @page-leaving.window="closeNav()"
             class="min-h-screen bg-linen lg:flex">
            @include('layouts.rail')

            {{-- Isolated from the keyboard and the accessibility tree while the
                 slide-over is open, so the panel is a real dialog. --}}
            <div x-effect="isolate($el)"
                 class="flex min-h-screen min-w-0 flex-1 flex-col {{ $sheet ? 'bg-white' : 'bg-linen' }}">
                {{-- Below the rail's breakpoint: the disclosure control, the seal,
                     and the name of the screen you are on. No links, by rule. --}}
                <header data-strip class="flex h-14 items-center gap-3 border-b border-rule bg-seal px-4 [view-transition-name:strip] lg:hidden">
                    <button type="button"
                            @click="openNav($event)"
                            class="relative -ml-2 inline-flex items-center justify-center rounded-sheet p-2 text-seal-tint transition duration-150 ease-in-out hover:bg-seal-deep hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-seal"
                            aria-label="{{ __('Open navigation') }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>

                    <x-application-logo class="h-8 w-8 shrink-0" aria-hidden="true" />

                    <span data-strip-label class="truncate font-annot text-annot uppercase text-seal-tint">
                        {{ $title ?? __('OSAS Scholarships') }}
                    </span>
                </header>

                <main id="main" class="flex-1">
                    {{ $slot }}
                    <div id="page-end" hidden></div>
                </main>
            </div>
        </div>

        <x-page-skeleton />
    </body>
</html>
