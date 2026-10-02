@props(['steps'])

{{--
    The practice tour's guide card. Each step asks the page's sample stage to show
    its screen and play its actions (a `tutorial-step` event), then spotlights the
    first of its `targets` (matched by `data-tour`) that is visible; a step whose
    targets are all hidden shows as a plain card instead.

    A step's `location` says where its feature lives in the menu: its `trail` is
    always printed on the card, and when the tour moves to a different menu item
    the spotlight stops on that item first, opening the slide-over menu on phones.
    It waits there until the user presses Show me, so the sample screen never moves
    on before they have read where the feature is. Finishing, closing or pressing
    Escape posts the only real request the tour makes: marking it seen, which
    returns to the dashboard.
--}}
<div x-data="tutorial(@js([
        'steps' => $steps,
        'locateHint' => __('You will find this under :item in the menu. Press Show me to open it.'),
     ]))"
     x-show="open"
     x-cloak
     @keydown.escape.window="open && finish()"
     @keydown.tab.window="open && trapFocus($event)"
     @resize.window="open && measure()"
     @scroll.window.passive="open && measure()"
     @tutorial-relayout.window="open && measure()"
     @tutorial-pause.window="waiting = $event.detail">
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
        <div class="flex items-start justify-between gap-3">
            <h2 id="tutorial-title" class="text-base font-semibold text-gray-900" x-text="step.title"></h2>
            <button type="button"
                    @click="finish()"
                    class="-m-1 shrink-0 rounded-md p-1 text-gray-500 transition duration-150 ease-in-out hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green">
                <span class="sr-only">{{ __('Exit tour') }}</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <p :class="{ 'hidden': ! step.location, 'bg-cvsu-gold/20': locating }"
           class="-mx-1.5 mt-1 rounded-md px-1.5 py-0.5 text-xs leading-5 text-gray-600 transition-colors duration-150 ease-in-out">
            <span class="font-medium">{{ __('In the menu:') }}</span>
            <template x-for="(crumb, position) in (step.location ? step.location.trail : [])" :key="position">
                <span>
                    <svg :class="{ 'hidden': position === 0 }" class="inline h-3 w-3 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                    </svg>
                    <span :class="position === 0 ? 'font-semibold text-gray-900' : 'text-gray-700'" x-text="crumb"></span>
                </span>
            </template>
        </p>
        <p id="tutorial-body" class="mt-2 text-sm leading-6 text-gray-600" aria-live="polite" x-text="locating ? locateHint() : step.body"></p>

        <button type="button"
                :class="{ 'hidden': step.actions.length === 0 || locating }"
                @click="present()"
                class="mt-3 rounded-sm text-sm font-semibold text-cvsu-green transition duration-150 ease-in-out hover:text-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">
            {{ __('Play again') }}
        </button>

        <div class="mt-5 flex items-center justify-between gap-3">
            <p class="text-xs tabular-nums text-gray-500" x-text="`${index + 1} of ${steps.length}`"></p>

            <div class="flex gap-2">
                <button type="button"
                        x-show="index > 0"
                        @click="back()"
                        class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">
                    {{ __('Back') }}
                </button>
                <button type="button"
                        x-ref="next"
                        @click="locating ? arrive() : (waiting ? proceed() : next())"
                        class="rounded-lg bg-cvsu-green px-3 py-1.5 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-gold focus-visible:ring-offset-2"
                        x-text="locating ? @js(__('Show me')) : (waiting || (index === steps.length - 1 ? @js(__('Done')) : @js(__('Next'))))"></button>
            </div>
        </div>
    </div>

    <form x-ref="finish" method="POST" action="{{ route('tutorial.complete') }}" class="hidden">
        @csrf
    </form>
</div>

<script>
    window.tutorial = function (config) {
        var GAP = 12;
        var EDGE = 16;

        return {
            steps: config.steps,
            index: 0,
            open: false,
            finishing: false,
            spot: null,
            card: {},
            watcher: null,
            locating: false,
            waiting: null,
            menuItem: null,
            run: 0,

            get step() {
                return this.steps[this.index];
            },

            init() {
                // Late layout shifts, like the web font arriving, move the spotlight's target.
                this.watcher = new ResizeObserver(() => this.open && this.measure());

                // After the stage, which renders first, is listening for steps.
                this.$nextTick(() => {
                    this.open = true;
                    this.show();
                });
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

            play() {
                window.dispatchEvent(new CustomEvent('tutorial-step', { detail: this.step }));
            },

            /**
             * Whether the step's feature lives somewhere other than what it spotlights,
             * so pointing at its menu item first shows the user how to get there.
             */
            visitsMenu(step) {
                return !! step.location && step.targets.indexOf(step.location.target) === -1;
            },

            menu(open) {
                window.dispatchEvent(new CustomEvent('tutorial-nav', { detail: open }));
            },

            locateHint() {
                return config.locateHint.replace(':item', this.step.location.trail[0]);
            },

            finish() {
                if (this.finishing) {
                    return;
                }

                this.finishing = true;
                this.open = false;
                this.spot = null;
                this.$refs.finish.submit();
            },

            target() {
                var names = this.locating ? [this.step.location.target] : this.step.targets;

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
                var step = this.step;
                var run = ++this.run;
                var movesInMenu = step.location && step.location.target !== this.menuItem;

                this.locating = false;
                this.menu(false);
                this.menuItem = step.location ? step.location.target : null;
                this.$nextTick(() => this.$refs.next.focus());

                if (movesInMenu && this.visitsMenu(step)) {
                    return this.locate(run);
                }

                this.present();
            },

            /**
             * Stop on the step's menu item before its screen opens, until the user
             * presses Show me. Below the rail's breakpoint the slide-over menu opens
             * for the moment.
             */
            async locate(run) {
                this.locating = true;

                if (! this.target()) {
                    this.menu(true);
                    // The slide-over's entrance transition.
                    await new Promise(function (resolve) { setTimeout(resolve, 260); });
                }

                if (run === this.run) {
                    this.$nextTick(() => this.measure());
                }
            },

            arrive() {
                this.locating = false;
                this.menu(false);
                this.present();
            },

            /**
             * Let a sample scene that paused for the reader carry on.
             */
            proceed() {
                this.waiting = null;
                window.dispatchEvent(new CustomEvent('tutorial-resume'));
            },

            present() {
                this.waiting = null;
                this.play();

                this.$nextTick(() => {
                    var element = this.target();

                    this.watcher.disconnect();
                    this.watcher.observe(document.body);

                    if (element) {
                        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                        element.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });
                        this.watcher.observe(element);
                    }

                    this.measure();
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
                } else if (box.top - pad - GAP - cardHeight >= EDGE) {
                    this.card = { left: clampLeft(box.left - padX) + 'px', top: box.top - pad - GAP - cardHeight + 'px' };
                } else {
                    // Taller than the room around it: park the card in whichever bottom corner hides less of it.
                    var coversFromLeft = EDGE + cardWidth - (box.left - padX);
                    var coversFromRight = box.right + padX - (viewportWidth - EDGE - cardWidth);

                    this.card = coversFromLeft <= coversFromRight
                        ? { left: EDGE + 'px', bottom: EDGE + 'px' }
                        : { right: EDGE + 'px', bottom: EDGE + 'px' };
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
