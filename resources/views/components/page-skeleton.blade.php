{{--
    Drafts: the page being opened, drawn before its records arrive.

    x-page-progress announces every navigation (page-leaving) and every one
    that came to nothing (page-staying). The rail's plate moves to the item
    pressed at once. If the next page has not answered within 300ms, which a
    cold instance can stretch to seconds, the page in hand gives way to the
    draft of the one asked for: its real chrome (banner, panels, rules and
    any fixed words), its heading when this map or the opened link knows it,
    and bars where its records will be. A record's title rises from the row
    that was pressed into the draft's heading and holds there until the page
    arrives over it. Going nowhere new (a search, a sort, another page of the
    same list) dims the list being replaced instead, since the filters just
    used stay on screen.

    Escape, the back-forward cache or the progress bar's 20s limit put the
    page back as it was. A path with no entry below draws nothing: the
    downloads, and the tutorial's practice screens.
--}}
<script type="application/json" id="page-skeletons">@json($pages)</script>

@foreach ($shapes as $shape)
    <template data-skeleton="{{ $shape }}">
        <x-dynamic-component :component="'page-skeleton.'.$shape" />
    </template>
@endforeach

<script>
    (function () {
        var main = document.getElementById('main');
        var map = document.getElementById('page-skeletons');

        if (!main || !map) {
            return;
        }

        var pages = JSON.parse(map.textContent);
        var strip = document.querySelector('[data-strip-label]');
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        var draftTimer = null;
        var refreshTimer = null;
        var drawn = null;
        var restorePoint = null;

        var pathOf = function (url) {
            return new URL(url, window.location.href).pathname;
        };

        // `*` stands for one path segment; the first page that matches wins.
        var pageFor = function (path) {
            for (var i = 0; i < pages.length; i++) {
                var pattern = pages[i].path.split('*').map(function (part) {
                    return part.replace(/[.?+^$()[\]{}|\\]/g, '\\$&');
                }).join('[^/]+');

                if (new RegExp('^' + pattern + '$').test(path)) {
                    return pages[i];
                }
            }

            return null;
        };

        // The rail item whose section holds the path, so a scholarship's page lights Scholarships.
        var markRail = function (path) {
            var links = document.querySelectorAll('[data-rail-link]');
            var section = null;

            links.forEach(function (link) {
                var linkPath = pathOf(link.href);

                if ((path === linkPath || path.indexOf(linkPath + '/') === 0) && (section === null || linkPath.length > section.length)) {
                    section = linkPath;
                }
            });

            if (section === null) {
                return;
            }

            links.forEach(function (link) {
                if (pathOf(link.href) === section) {
                    link.setAttribute('data-going', '');
                } else if (link.getAttribute('aria-current') === 'page') {
                    link.setAttribute('data-leaving', '');
                }
            });
        };

        var unmarkRail = function () {
            document.querySelectorAll('[data-rail-link]').forEach(function (link) {
                link.removeAttribute('data-going');
                link.removeAttribute('data-leaving');
            });
        };

        var titleIn = function (source) {
            if (!source || source.tagName !== 'A') {
                return null;
            }

            return source.hasAttribute('data-vt-title') ? source : source.querySelector('[data-vt-title]');
        };

        var undraw = function () {
            if (drawn) {
                drawn.remove();
                drawn = null;
                main.removeAttribute('data-drafting');
                main.removeAttribute('aria-busy');
            }
        };

        var draw = function (page, source, animate) {
            var template = document.querySelector('template[data-skeleton="' + page.shape + '"]');

            if (!template) {
                return;
            }

            var sheet = template.content.firstElementChild.cloneNode(true);

            (page.hide || []).forEach(function (part) {
                sheet.querySelectorAll('[data-draft-part="' + part + '"]').forEach(function (element) {
                    element.remove();
                });
            });

            var title = titleIn(source);
            var heading = sheet.querySelector('[data-draft-heading]');
            var text = page.heading || (title ? title.textContent.replace(/\s+/g, ' ').trim() : '');
            var label = page.label || text;

            // Only a record's own title travels: a fixed heading is already where the page will print it.
            var morph = !page.heading && title && heading && text;

            if (heading && text) {
                heading.textContent = text;
            }

            if (morph) {
                heading.setAttribute('data-vt-title', '');
            }

            if (restorePoint === null) {
                restorePoint = { scroll: window.scrollY, label: strip ? strip.textContent : null, source: source };
            }

            var swap = function () {
                undraw();
                main.insertBefore(sheet, main.firstChild);
                main.setAttribute('data-drafting', '');
                main.setAttribute('aria-busy', 'true');
                drawn = sheet;

                if (strip && label) {
                    strip.textContent = label;
                }

                window.scrollTo(0, 0);
            };

            if (!animate || !document.startViewTransition || reduceMotion.matches) {
                if (animate && !reduceMotion.matches) {
                    sheet.classList.add('draft-arrive');
                }

                swap();

                return;
            }

            if (morph) {
                title.style.viewTransitionName = 'record-title';
            }

            document.startViewTransition(function () {
                // The draft's heading keeps the name, so the next page's arrival carries it on.
                if (morph) {
                    title.style.viewTransitionName = '';
                    heading.style.viewTransitionName = 'record-title';
                }

                swap();
            });
        };

        var stay = function () {
            var point = restorePoint;

            clearTimeout(draftTimer);
            clearTimeout(refreshTimer);
            main.removeAttribute('data-refreshing');
            unmarkRail();
            restorePoint = null;

            if (!drawn) {
                return;
            }

            undraw();

            if (strip && point.label !== null) {
                strip.textContent = point.label;
            }

            window.scrollTo(0, point.scroll);

            if (point.source && point.source.isConnected && point.source.focus) {
                point.source.focus({ preventScroll: true });
            }
        };

        window.addEventListener('page-leaving', function (event) {
            if (event.detail.method !== 'get') {
                return;
            }

            var path = event.detail.url.pathname;
            var page = path === window.location.pathname ? null : pageFor(path);

            clearTimeout(draftTimer);
            clearTimeout(refreshTimer);
            main.removeAttribute('data-refreshing');
            unmarkRail();

            if (path === window.location.pathname || (drawn && !page)) {
                stay();
            }

            if (path === window.location.pathname) {
                refreshTimer = setTimeout(function () {
                    main.setAttribute('data-refreshing', '');
                }, 150);

                return;
            }

            markRail(path);

            if (!page) {
                return;
            }

            // A second press while a draft stands replaces it at once; the reader is already waiting.
            if (drawn) {
                draw(page, event.detail.source, false);
            } else {
                draftTimer = setTimeout(function () {
                    draw(page, event.detail.source, true);
                }, 300);
            }
        });

        window.addEventListener('page-staying', stay);
    })();
</script>
