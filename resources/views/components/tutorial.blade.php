{{--
    The guided tour of a role's dashboard. Each step spotlights the first of its
    `targets` (matched by `data-tour`) that is visible; a step whose targets are
    all hidden, like rail links inside the closed mobile menu, shows as a plain
    card instead. It starts on its own until the user first finishes or skips
    it, and the rail's Tutorial link replays it.
--}}
@php
    $user = Auth::user();

    $steps = $user->isAdmin()
        ? [
            [
                'targets' => [],
                'title' => __('Welcome to the scholarship office'),
                'body' => __('This tour shows how a scholarship moves from posting to a decision. It takes about a minute.'),
            ],
            [
                'targets' => ['post-scholarship'],
                'title' => __('Post a scholarship'),
                'body' => __('Set the title, number of slots and deadline, then add the documents students must upload.'),
            ],
            [
                'targets' => ['needs-decision', 'dashboard-summary'],
                'title' => __('See what needs a decision'),
                'body' => __('Scholarships with applications waiting on you appear here, soonest deadline first. Open one to review its applicants and their documents.'),
            ],
            [
                'targets' => ['nav-scholarships'],
                'title' => __('Upload the pass list'),
                'body' => __('Inside each scholarship, upload a CSV of who passed. Matched applicants are approved, each filling one slot, and every other pending applicant is rejected.'),
            ],
            [
                'targets' => ['nav-roster'],
                'title' => __('See registered students'),
                'body' => __('Students join the roster by verifying their CvSU Google account and completing the registration form. There is nothing to import.'),
            ],
            [
                'targets' => ['nav-tutorial'],
                'title' => __('Replay this tour'),
                'body' => __('Open Tutorial in the menu whenever you want to see this again.'),
            ],
        ]
        : [
            [
                'targets' => [],
                'title' => __('Welcome to the scholarship portal'),
                'body' => __('This tour shows how to apply for a scholarship and follow it to a decision. It takes about a minute.'),
            ],
            [
                'targets' => ['nav-scholarships'],
                'title' => __('Find scholarships'),
                'body' => __('Scholarships lists everything OSAS has posted, with the slots, deadline and documents each one needs.'),
            ],
            [
                'targets' => ['open-for-you'],
                'title' => __('Apply'),
                'body' => __('These are the scholarships you can still apply to. Open one, choose Apply and upload each required document.'),
            ],
            [
                'targets' => ['my-applications', 'nav-applications'],
                'title' => __('Follow your applications'),
                'body' => __('Each application stays Pending while OSAS reviews it, then changes to Approved or Rejected.'),
            ],
            [
                'targets' => ['nav-profile'],
                'title' => __('Keep your profile current'),
                'body' => __('Your applications use the name, course and year level on your profile, so check them before you apply.'),
            ],
            [
                'targets' => ['nav-tutorial'],
                'title' => __('Replay this tour'),
                'body' => __('Open Tutorial in the menu whenever you want to see this again.'),
            ],
        ];
@endphp

<div x-data="tutorial(@js([
        'steps' => $steps,
        'autostart' => $user->tutorial_completed_at === null || request()->boolean('tutorial'),
        'recordCompletion' => $user->tutorial_completed_at === null,
        'completeUrl' => route('tutorial.complete'),
        'csrfToken' => csrf_token(),
     ]))"
     x-show="open"
     x-cloak
     @tutorial-start.window="start()"
     @keydown.escape.window="open && finish()"
     @keydown.tab.window="open && trapFocus($event)"
     @resize.window="open && measure()"
     @scroll.window.passive="open && measure()">
    <div class="fixed inset-0 z-[80]" aria-hidden="true">
        <div x-show="! spot" class="absolute inset-0 bg-gray-900/50"></div>
        <div x-show="spot"
             class="absolute rounded-lg transition-all duration-200 ease-out motion-reduce:transition-none"
             :style="spot && {
                 top: spot.top + 'px',
                 left: spot.left + 'px',
                 width: spot.width + 'px',
                 height: spot.height + 'px',
                 boxShadow: '0 0 0 2px #FFC72C, 0 0 0 9999px rgb(17 24 39 / 0.5)',
             }"></div>
    </div>

    <div x-ref="card"
         role="dialog"
         aria-modal="true"
         aria-labelledby="tutorial-title"
         aria-describedby="tutorial-body"
         class="fixed z-[81] w-[min(22rem,calc(100vw-2rem))] rounded-lg bg-white p-5 shadow-xl ring-1 ring-black/5"
         :style="card">
        <h2 id="tutorial-title" class="text-base font-semibold text-gray-900" x-text="step.title"></h2>
        <p id="tutorial-body" class="mt-2 text-sm leading-6 text-gray-600" x-text="step.body"></p>

        <div class="mt-5 flex items-center justify-between gap-3">
            <p class="text-xs tabular-nums text-gray-500" x-text="`${index + 1} of ${steps.length}`"></p>

            <div class="flex gap-2">
                <button type="button"
                        x-show="index === 0"
                        @click="finish()"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">
                    {{ __('Skip') }}
                </button>
                <button type="button"
                        x-show="index > 0"
                        @click="back()"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">
                    {{ __('Back') }}
                </button>
                <button type="button"
                        x-ref="next"
                        @click="next()"
                        class="rounded-lg bg-cvsu-green px-3 py-1.5 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-gold focus-visible:ring-offset-2"
                        x-text="index === steps.length - 1 ? @js(__('Done')) : @js(__('Next'))"></button>
            </div>
        </div>
    </div>
</div>

<script>
    window.tutorial = function (config) {
        var GAP = 12;
        var EDGE = 16;

        return {
            steps: config.steps,
            index: 0,
            open: false,
            spot: null,
            card: {},
            returnFocus: null,

            get step() {
                return this.steps[this.index];
            },

            init() {
                // Lets the rail's Tutorial link replay in place instead of reloading.
                window.tutorialAvailable = true;

                if (new URLSearchParams(window.location.search).has('tutorial')) {
                    var url = new URL(window.location.href);
                    url.searchParams.delete('tutorial');
                    window.history.replaceState(null, '', url);
                }

                if (config.autostart) {
                    this.start();
                }
            },

            start() {
                this.returnFocus = document.activeElement;
                this.index = 0;
                this.open = true;
                this.show();
            },

            next() {
                if (this.index === this.steps.length - 1) {
                    return this.finish();
                }

                this.index++;
                this.show();
            },

            back() {
                if (this.index > 0) {
                    this.index--;
                    this.show();
                }
            },

            finish() {
                this.open = false;
                this.spot = null;

                if (config.recordCompletion) {
                    config.recordCompletion = false;

                    fetch(config.completeUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' },
                        keepalive: true,
                    }).catch(function () {});
                }

                if (this.returnFocus && document.contains(this.returnFocus)) {
                    this.returnFocus.focus();
                }
            },

            target() {
                var names = this.step.targets;

                for (var i = 0; i < names.length; i++) {
                    var candidates = document.querySelectorAll('[data-tour="' + names[i] + '"]');

                    for (var j = 0; j < candidates.length; j++) {
                        var box = candidates[j].getBoundingClientRect();

                        if (box.width > 0 && box.height > 0) {
                            return candidates[j];
                        }
                    }
                }

                return null;
            },

            show() {
                var element = this.target();

                if (element) {
                    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    element.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
                }

                this.$nextTick(() => {
                    this.measure();
                    this.$refs.next.focus();
                });
            },

            measure() {
                var element = this.target();
                var viewportWidth = window.innerWidth;
                var viewportHeight = window.innerHeight;
                var cardWidth = this.$refs.card.offsetWidth;
                var cardHeight = this.$refs.card.offsetHeight;

                if (! element) {
                    this.spot = null;
                    this.card = viewportWidth < 640
                        ? { left: EDGE + 'px', right: EDGE + 'px', bottom: EDGE + 'px', width: 'auto' }
                        : { left: (viewportWidth - cardWidth) / 2 + 'px', top: Math.max(EDGE, (viewportHeight - cardHeight) / 2) + 'px' };

                    return;
                }

                var box = element.getBoundingClientRect();
                // Wider than tall: dashboard lists bleed 12px past their section.
                var padX = 16;
                var pad = 8;

                this.spot = {
                    top: box.top - pad,
                    left: box.left - padX,
                    width: box.width + padX * 2,
                    height: box.height + pad * 2,
                };

                // Phones keep the card docked to the bottom edge, clear of the spotlight.
                if (viewportWidth < 640) {
                    var dockTop = box.bottom + pad + GAP + cardHeight > viewportHeight - EDGE;
                    this.card = dockTop
                        ? { left: EDGE + 'px', right: EDGE + 'px', top: EDGE + 'px', width: 'auto' }
                        : { left: EDGE + 'px', right: EDGE + 'px', bottom: EDGE + 'px', width: 'auto' };

                    return;
                }

                var clampLeft = function (left) {
                    return Math.min(Math.max(EDGE, left), viewportWidth - cardWidth - EDGE);
                };
                var clampTop = function (top) {
                    return Math.min(Math.max(EDGE, top), viewportHeight - cardHeight - EDGE);
                };

                if (box.right + padX + GAP + cardWidth <= viewportWidth - EDGE && box.width < viewportWidth / 3) {
                    this.card = { left: box.right + padX + GAP + 'px', top: clampTop(box.top - pad) + 'px' };
                } else if (box.bottom + pad + GAP + cardHeight <= viewportHeight - EDGE) {
                    this.card = { left: clampLeft(box.left - padX) + 'px', top: box.bottom + pad + GAP + 'px' };
                } else {
                    this.card = { left: clampLeft(box.left - padX) + 'px', top: clampTop(box.top - pad - GAP - cardHeight) + 'px' };
                }
            },

            trapFocus(event) {
                var focusable = Array.prototype.filter.call(
                    this.$refs.card.querySelectorAll('button'),
                    function (button) { return button.offsetParent !== null; }
                );

                if (focusable.length === 0) {
                    return;
                }

                var first = focusable[0];
                var last = focusable[focusable.length - 1];

                if (! this.$refs.card.contains(document.activeElement)) {
                    event.preventDefault();
                    first.focus();
                } else if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (! event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },
        };
    };
</script>
