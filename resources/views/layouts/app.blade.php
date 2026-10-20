<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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
                        name(titleFor(pathOf(event.activation.entry.url)), event.viewTransition);
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
    </head>
    <body class="font-sans antialiased">
        <x-page-progress />

        <div x-data="{ navOpen: false }" @keydown.escape.window="navOpen = false" @tutorial-nav.window="navOpen = $event.detail" class="min-h-screen bg-gray-100 lg:flex">
            @include('layouts.rail')

            <div class="flex min-h-screen min-w-0 flex-1 flex-col {{ $sheet ? 'bg-white' : 'bg-gray-100' }}">
                <!-- Rail trigger, below the rail's own breakpoint -->
                <div class="flex items-center gap-2 border-b border-gray-200 bg-white px-4 py-3 [view-transition-name:strip] lg:hidden">
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
                    <div id="page-end" hidden></div>
                </main>
            </div>
        </div>
    </body>
</html>
