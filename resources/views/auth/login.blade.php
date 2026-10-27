<x-guest-layout :title="__('Sign in')">
    {{--
        One card (asked 2026-10-25): a header strip in the welcome banners' mint
        with a lock in a round disc, then the password form, an "or" rule, and
        Google sign-up. The local test-account note stays outside the card.
    --}}
    <div class="overflow-hidden rounded-lg border border-rule bg-white shadow-seat">
        <div class="flex items-center gap-4 border-b border-seal-tint bg-seal-wash px-6 py-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-seal-tint/60 text-seal" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </span>
            <div class="min-w-0">
                <h2 class="text-lead font-semibold leading-tight text-ink">{{ __('Sign in') }}</h2>
                <p class="mt-0.5 text-graphite">{{ __('Students sign in or register with their @cvsu.edu.ph Google account.') }}</p>
            </div>
        </div>

        <div class="px-6 py-6">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" x-data="{ loading: false, showPassword: false }" @submit="loading = true" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0-.621.504-1.125 1.125-1.125h17.25c.621 0 1.125.504 1.125 1.125v10.5c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 012.25 17.25V6.75z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.5 6.5 8.5 6.5 8.5-6.5" />
                            </svg>
                        </span>
                        <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@cvsu.edu.ph" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </span>
                        <x-text-input id="password" class="block w-full pl-10 pr-10"
         type="password"
                                        x-bind:type="showPassword ? 'text' : 'password'"
         name="password"
                                        required autocomplete="current-password" />
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate hover:text-graphite" tabindex="-1">
                            <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.774 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-rule-strong text-seal focus:ring-seal" name="remember">
                        <span class="ms-2  text-graphite">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="font-medium text-seal hover:text-seal-deep" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <button type="submit" x-bind:disabled="loading" class="flex w-full items-center justify-center gap-2 rounded-sheet bg-seal px-4 py-2.5  font-semibold text-white shadow-sm transition hover:bg-seal-deep focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
                    <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="loading ? '{{ __('Signing in...') }}' : '{{ __('Sign in with password') }}'"></span>
                </button>
            </form>

            <div class="my-6 flex items-center gap-3" aria-hidden="true">
                <span class="h-px flex-1 bg-rule"></span>
                <span class="font-annot text-annot uppercase text-slate">{{ __('or') }}</span>
                <span class="h-px flex-1 bg-rule"></span>
            </div>

            <!-- Students: Sign up with Google -->
            <section aria-labelledby="student-sign-up">
                <h3 id="student-sign-up" class="sr-only">{{ __('Student sign-up') }}</h3>

                @if (config('services.google.client_id'))
                    {{-- Drawn by Google at the column's own width so it lines up with the password button at every screen size; the reserved height stops the page jumping when it arrives. --}}
                    <div id="google-sign-up" class="flex min-h-10 justify-center"></div>

                    {{-- Relays Google's ID token to the server; nothing else about the student is sent. --}}
                    <form id="google-sign-in" method="POST" action="{{ route('login.google') }}" hidden>
                        @csrf
                        <input type="hidden" name="credential">
                    </form>

                    <script>
                        function handleGoogleCredential(response) {
                            var form = document.getElementById('google-sign-in');
                            form.elements.credential.value = response.credential;
                            form.submit();
                        }

                        function renderGoogleSignUp() {
                            var container = document.getElementById('google-sign-up');

                            google.accounts.id.initialize({
                                client_id: @js(config('services.google.client_id')),
                                callback: handleGoogleCredential,
                                context: 'signup',
                                ux_mode: 'popup',
                                hd: 'cvsu.edu.ph',
                            });

                            google.accounts.id.renderButton(container, {
                                type: 'standard',
                                theme: 'outline',
                                size: 'large',
                                text: 'signup_with',
                                shape: 'rectangular',
                                logo_alignment: 'left',
                                width: Math.min(400, container.clientWidth),
                                locale: 'en',
                            });
                        }
                    </script>
                    <script src="https://accounts.google.com/gsi/client?hl=en" async onload="renderGoogleSignUp()"></script>
                @else
                    <p class="rounded-sheet border border-amber-200 bg-amber-50 px-4 py-3  text-amber-800">
                        {{ __('Google sign-in is not configured yet. Set GOOGLE_CLIENT_ID to enable it.') }}
                    </p>
                @endif

                <x-input-error :messages="$errors->get('google')" class="mt-3 text-center" />
            </section>
        </div>
    </div>
</x-guest-layout>
