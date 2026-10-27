{{--
    Navigation progress: a hairline across the content column with a 2px
    witness tick at its head, drawn like the season bar's "today". Decorative:
    the browser already announces the navigation itself. The bar grows by
    scaleX and the tick by translateX, so each step is composited instead of
    laying the page out again. Over the Seal Green strip at the top of a
    phone, Seal on Seal would vanish, so there it is drawn in Gold, the
    system's pairing on green. The page that answers runs the bar out to its
    end before letting it go. With reduced motion it appears at full width
    and fades, without the creep.

    Every navigation is announced on window for x-page-skeleton: page-leaving
    with the URL, the method and the link or button that started it, and
    page-staying when it came to nothing (Escape, the back-forward cache, or
    20s without an answer). The button that submits a form gives up its label
    to a 16px ring until the next page, unless it draws its own progress.
--}}
<div id="page-progress" class="group pointer-events-none fixed inset-x-0 top-0 z-[70] h-1.5 opacity-0 transition-opacity duration-200 ease-out lg:left-rail" aria-hidden="true">
    <div id="page-progress-bar"
         class="h-0.5 w-full origin-left bg-seal transition-transform duration-200 ease-out group-data-[over-shell]:bg-gold"
         style="transform: scaleX(0)"></div>
    <div id="page-progress-tip" class="absolute inset-0 transition-transform duration-200 ease-out motion-reduce:hidden" style="transform: translateX(0)">
        <span class="absolute inset-y-0 left-0 w-0.5 -translate-x-full bg-ink group-data-[over-shell]:bg-white"></span>
    </div>
</div>

<script>
    (function () {
        var track = document.getElementById('page-progress');
        var bar = document.getElementById('page-progress-bar');
        var tip = document.getElementById('page-progress-tip');

        if (!track || !bar || !tip) {
            return;
        }

        var revealTimer = null;
        var creepTimer = null;
        var safetyTimer = null;
        var running = false;
        var width = 0;
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        var draw = function () {
            bar.style.transform = 'scaleX(' + (width / 100).toFixed(4) + ')';
            tip.style.transform = 'translateX(' + width.toFixed(2) + '%)';
        };

        // Move without animating, so a reset never shows the bar running backwards.
        var jump = function (value) {
            bar.style.transition = tip.style.transition = 'none';
            width = value;
            draw();
            bar.getBoundingClientRect();
            bar.style.transition = tip.style.transition = '';
        };

        var show = function (visible) {
            track.style.opacity = visible ? '1' : '0';
        };

        // Below lg the strip heads the page until it scrolls away.
        var placeOverShell = function () {
            var strip = document.querySelector('[data-strip]');

            track.toggleAttribute('data-over-shell', !!strip && strip.getBoundingClientRect().bottom > 1);
        };

        var hold = function (button) {
            if (!button || button.tagName !== 'BUTTON' || button.querySelector('.animate-spin')) {
                return;
            }

            button.style.setProperty('--busy-ink', window.getComputedStyle(button).color);
            button.setAttribute('data-busy', '');
            button.setAttribute('aria-disabled', 'true');
        };

        var release = function () {
            document.querySelectorAll('button[data-busy]').forEach(function (button) {
                button.removeAttribute('data-busy');
                button.removeAttribute('aria-disabled');
                button.style.removeProperty('--busy-ink');
            });
        };

        var start = function (detail) {
            window.dispatchEvent(new CustomEvent('page-leaving', { detail: detail }));

            if (running) {
                return;
            }

            running = true;

            // Navigations that resolve quickly should flash nothing at all.
            revealTimer = setTimeout(function () {
                placeOverShell();
                jump(0);
                width = reduceMotion.matches ? 100 : 30;
                show(true);
                draw();

                if (reduceMotion.matches) {
                    return;
                }

                creepTimer = setInterval(function () {
                    // Ease toward 90% and never reach it; the page swap ends the bar.
                    width += (90 - width) * 0.12;
                    draw();
                }, 240);
            }, 150);

            safetyTimer = setTimeout(stop, 20000);
        };

        function stop() {
            clearTimeout(revealTimer);
            clearInterval(creepTimer);
            clearTimeout(safetyTimer);
            running = false;
            show(false);
            window.dispatchEvent(new CustomEvent('page-staying'));
        }

        // The page that asked for this one left its bar part way: run it out here, then let it go.
        var carried = null;

        try {
            carried = JSON.parse(window.sessionStorage.getItem('page-progress'));
            window.sessionStorage.removeItem('page-progress');
        } catch (error) {
            carried = null;
        }

        if (carried && typeof carried.width === 'number' && Date.now() - carried.at < 30000) {
            track.style.transition = 'none';
            show(true);
            jump(reduceMotion.matches ? 100 : carried.width);

            requestAnimationFrame(function () {
                placeOverShell();
                track.style.transition = '';
                width = 100;
                draw();

                setTimeout(function () {
                    if (!running) {
                        show(false);
                    }
                }, 220);
            });
        }

        window.addEventListener('pagehide', function () {
            if (running && track.style.opacity === '1') {
                try {
                    window.sessionStorage.setItem('page-progress', JSON.stringify({ width: width, at: Date.now() }));
                } catch (error) {
                    // Without storage the next page simply starts clean.
                }
            }
        });

        window.addEventListener('scroll', function () {
            if (running) {
                placeOverShell();
            }
        }, { passive: true });

        document.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            var link = event.target.closest('a[href]');

            if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) {
                return;
            }

            var url = new URL(link.href, window.location.href);

            if (url.origin !== window.location.origin) {
                return;
            }

            // Same-page anchors and no-op links don't navigate away.
            if (url.href === window.location.href) {
                return;
            }

            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
                return;
            }

            start({ url: url, method: 'get', source: link });
        });

        document.addEventListener('submit', function (event) {
            var form = event.target;
            var submitter = event.submitter;
            var method = ((submitter && submitter.getAttribute('formmethod')) || form.getAttribute('method') || 'get').toLowerCase();

            if (event.defaultPrevented || method === 'dialog' || (form.target && form.target !== '_self')) {
                return;
            }

            var action = (submitter && submitter.getAttribute('formaction')) || form.getAttribute('action') || window.location.href;

            hold(submitter);
            start({ url: new URL(action, window.location.href), method: method === 'get' ? 'get' : 'post', source: submitter || form });
        });

        // Escape stops a page load in the browser; put this page back with it.
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && running) {
                stop();
                release();
            }
        });

        // Returning through the back-forward cache restores a page mid-animation.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                stop();
                release();

                try {
                    window.sessionStorage.removeItem('page-progress');
                } catch (error) {
                    // Nothing was carried, then.
                }
            }
        });
    })();
</script>
