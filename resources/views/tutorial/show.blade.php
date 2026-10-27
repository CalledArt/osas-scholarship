<x-app-layout :title="__('Tutorial')" :sheet="true">
    <div class="border-b border-rule bg-linen-sunk px-6 py-3 lg:px-10">
        <p class="text-graphite">
            <span class="font-semibold text-ink">{{ __('Practice tour') }}</span>
            <span aria-hidden="true">&middot;</span>
            {{ __('Sample data only. Nothing here is saved or sent to anyone.') }}
        </p>
    </div>

    @include(Auth::user()->isAdmin() ? 'tutorial.admin' : 'tutorial.student')

    <script>
        /**
         * The sample stage behind the practice tour. It holds every screen's data in
         * memory and acts out each step: typing into fields, pressing buttons and
         * changing statuses. It has no forms and makes no requests, so nothing it
         * shows can reach the real system.
         *
         * A step's `state` replaces the matching keys of `base`, so Back and Play
         * again always start from the same picture. Its `actions` run in order:
         * ['type', path, text], ['set', path, value], ['push', path, item],
         * ['press', id], ['wait', ms] and ['pause', label]. A pause holds the scene
         * until the reader presses the guide's button, which `label` names, so a
         * before-and-after never moves on before it has been read. With reduced
         * motion everything but a pause applies at once.
         */
        window.tutorialStage = function (config) {
            var clone = function (value) {
                return JSON.parse(JSON.stringify(value));
            };

            return {
                screen: null,
                state: clone(config.base),
                focus: null,
                pressed: null,
                run: 0,
                resume: null,

                perform(step) {
                    var run = ++this.run;
                    var instant = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    // Releases a scene left paused by the step being replaced; it then sees the new run and stops.
                    this.proceed();

                    this.screen = step.screen;
                    this.state = Object.assign(clone(config.base), clone(step.state));
                    this.focus = null;
                    this.pressed = null;
                    this.relayout();

                    this.act(step.actions, run, instant);
                },

                async act(actions, run, instant) {
                    // Lets the spotlight settle on the screen before anything moves.
                    await this.pause(instant ? 0 : 700);

                    for (var i = 0; i < actions.length; i++) {
                        if (run !== this.run) {
                            return;
                        }

                        await this.apply(actions[i], run, instant);
                        this.relayout();
                    }

                    this.focus = null;
                },

                async apply(action, run, instant) {
                    var kind = action[0];

                    if (kind === 'wait') {
                        return this.pause(instant ? 0 : action[1]);
                    }

                    if (kind === 'press') {
                        if (! instant) {
                            this.focus = null;
                            this.pressed = action[1];
                            await this.pause(450);
                            this.pressed = null;
                        }

                        return;
                    }

                    if (kind === 'pause') {
                        var self = this;

                        await new Promise(function (resolve) {
                            self.resume = resolve;
                            window.dispatchEvent(new CustomEvent('tutorial-pause', { detail: action[1] }));
                        });

                        return;
                    }

                    if (kind === 'push') {
                        this.read(action[1]).push(clone(action[2]));

                        return;
                    }

                    if (kind === 'set') {
                        this.write(action[1], clone(action[2]));

                        return;
                    }

                    if (kind === 'type') {
                        var text = String(action[2]);

                        if (instant) {
                            return this.write(action[1], text);
                        }

                        this.focus = action[1];

                        for (var i = 1; i <= text.length; i++) {
                            if (run !== this.run) {
                                return;
                            }

                            this.write(action[1], text.slice(0, i));
                            await this.pause(28);
                        }

                        await this.pause(250);
                    }
                },

                read(path) {
                    return path.split('.').reduce(function (value, key) {
                        return value[key];
                    }, this.state);
                },

                write(path, value) {
                    var keys = path.split('.');
                    var last = keys.pop();

                    (keys.length ? this.read(keys.join('.')) : this.state)[last] = value;
                },

                proceed() {
                    var resume = this.resume;

                    this.resume = null;

                    if (resume) {
                        resume();
                    }
                },

                pause(milliseconds) {
                    return new Promise(function (resolve) {
                        setTimeout(resolve, milliseconds);
                    });
                },

                relayout() {
                    this.$nextTick(function () {
                        window.dispatchEvent(new CustomEvent('tutorial-relayout'));
                    });
                },
            };
        };
    </script>
</x-app-layout>
