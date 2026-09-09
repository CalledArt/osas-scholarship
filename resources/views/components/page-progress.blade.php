{{--
    Navigation progress. Server-rendered pages have nothing partial to skeleton,
    so the current page stays on screen and only a hairline reports the wait.
    Decorative: the browser already announces the navigation itself.
--}}
<div class="pointer-events-none fixed inset-x-0 top-0 z-[70] h-0.5" aria-hidden="true">
    <div id="page-progress-bar"
         class="h-full w-0 bg-cvsu-green opacity-0 transition-[width,opacity] duration-200 ease-out"></div>
</div>

<script>
    (function () {
        var bar = document.getElementById('page-progress-bar');

        if (!bar) {
            return;
        }

        var revealTimer = null;
        var creepTimer = null;
        var safetyTimer = null;
        var running = false;
        var width = 0;

        var start = function () {
            if (running) {
                return;
            }

            running = true;

            // Navigations that resolve quickly should flash nothing at all.
            revealTimer = setTimeout(function () {
                width = 30;
                bar.style.opacity = '1';
                bar.style.width = width + '%';

                creepTimer = setInterval(function () {
                    // Ease toward 90% and never reach it; the page swap ends the bar.
                    width += (90 - width) * 0.12;
                    bar.style.width = width.toFixed(2) + '%';
                }, 240);
            }, 150);

            safetyTimer = setTimeout(stop, 20000);
        };

        function stop() {
            clearTimeout(revealTimer);
            clearInterval(creepTimer);
            clearTimeout(safetyTimer);
            running = false;
            width = 0;
            bar.style.opacity = '0';
            bar.style.width = '0%';
        }

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

            start();
        });

        document.addEventListener('submit', function (event) {
            if (!event.defaultPrevented) {
                start();
            }
        });

        // Returning through the back-forward cache restores a page mid-animation.
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                stop();
            }
        });
    })();
</script>
