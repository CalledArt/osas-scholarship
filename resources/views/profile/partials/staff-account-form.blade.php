{{--
    Staff only: their name, email and password in one form with one Save
    (decided 2026-10-24), laid out wide so the profile fits one screen. Each
    section is a row with its title on the left from 1024px and its fields on
    the right. Left blank, the password fields keep the current password.

    The verification form sits outside, since forms cannot nest; its button
    reaches it through the form attribute.
--}}
<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="rounded-lg border border-rule bg-white">
    @csrf
    @method('patch')

    <section aria-labelledby="details-heading" class="grid gap-x-8 gap-y-3 p-5 lg:grid-cols-[14rem_1fr]">
        <header>
            <h2 id="details-heading" class="font-semibold text-ink">{{ __('Profile information') }}</h2>
            <p class="mt-0.5 text-slate">{{ __('Your name and the email you sign in with.') }}</p>
        </header>

        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>

            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="middle_name" :value="__('Middle Name')" />
                <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name', $user->middle_name)" :placeholder="__('Optional')" autocomplete="additional-name" />
                <x-input-error class="mt-2" :messages="$errors->get('middle_name')" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="mt-2 text-ink">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="rounded-sheet text-graphite underline hover:text-ink focus:outline-none focus:ring-2 focus:ring-seal focus:ring-offset-2">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-grant-deep">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </section>

    <section aria-labelledby="password-heading" class="grid gap-x-8 gap-y-3 border-t border-rule p-5 lg:grid-cols-[14rem_1fr]">
        <header>
            <h2 id="password-heading" class="font-semibold text-ink">{{ __('Password') }}</h2>
            <p class="mt-0.5 text-slate">{{ __('Leave blank to keep your current password.') }}</p>
        </header>

        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('New Password')" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-4 border-t border-rule px-5 py-3">
        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-graphite">{{ __('Saved.') }}</p>
        @endif

        <x-primary-button>{{ __('Save') }}</x-primary-button>
    </div>
</form>
