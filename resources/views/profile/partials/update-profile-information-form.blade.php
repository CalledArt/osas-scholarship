{{--
    Students only: staff edit their details and password on
    profile.partials.staff-account-form, which this card copies at the
    user's request (2026-10-25): each section is a row with a short title and
    one line on the left from 1024px, its fields on the right, and one Save at
    the foot. Students keep the name hints the staff form drops. Enrollment is
    read-only: OSAS keeps it, which its title says once for all its fields.
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
            <p class="mt-0.5 text-slate">{{ __('Your name and the Google account you sign in with.') }}</p>
        </header>

        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input id="last_name" name="last_name" maxlength="255" type="text" class="mt-1 block w-full" :value="old('last_name', $user->last_name)" required autofocus autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                <p class="mt-1 text-slate">{{ __('Include the full surname, for example "Dela Cruz".') }}</p>
            </div>

            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input id="first_name" name="first_name" maxlength="255" type="text" class="mt-1 block w-full" :value="old('first_name', $user->first_name)" required autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="middle_name" :value="__('Middle Name')" />
                <x-text-input id="middle_name" name="middle_name" maxlength="255" type="text" class="mt-1 block w-full" :value="old('middle_name', $user->middle_name)" autocomplete="additional-name" />
                <x-input-error class="mt-2" :messages="$errors->get('middle_name')" />
                <p class="mt-1 text-slate">{{ __('Optional. Leave blank if you do not use one.') }}</p>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="email" :value="__('Email')" />
                {{-- A student's email belongs to their verified Google identity. --}}
                <x-text-input id="email" type="email" class="mt-1 block w-full bg-linen-sunk text-slate" :value="$user->email" disabled />
                <p class="mt-1 text-slate">{{ __('You sign in with this Google account. Contact OSAS if it needs to change.') }}</p>
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

    <section aria-labelledby="personal-heading" class="grid gap-x-8 gap-y-3 border-t border-rule p-5 lg:grid-cols-[14rem_1fr]">
        <header>
            <h2 id="personal-heading" class="font-semibold text-ink">{{ __('Personal details') }}</h2>
            <p class="mt-0.5 text-slate">{{ __('Only you can see and change your date of birth, contact number and address. OSAS staff cannot.') }}</p>
        </header>

        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $user->date_of_birth?->format('Y-m-d'))" :max="now()->subDay()->toDateString()" required autocomplete="bday" />
                <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
            </div>

            <div>
                <x-input-label for="contact_number" :value="__('Contact Number')" />
                <x-text-input id="contact_number" name="contact_number" type="tel" class="mt-1 block w-full" :value="old('contact_number', $user->contact_number)" required autocomplete="tel" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" x-data x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '')" title="{{ __('11 digits, numbers only') }}" placeholder="09171234567" />
                <x-input-error class="mt-2" :messages="$errors->get('contact_number')" />
            </div>

            <div>
                <x-input-label for="address" :value="__('Address')" />
                <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->address)" required autocomplete="street-address" maxlength="255" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
        </div>
    </section>

    <section aria-labelledby="enrollment-heading" class="grid gap-x-8 gap-y-3 border-t border-rule p-5 lg:grid-cols-[14rem_1fr]">
        <header>
            <h2 id="enrollment-heading" class="font-semibold text-ink">{{ __('Enrollment') }}</h2>
            <p id="enrollment-hint" class="mt-0.5 text-slate">{{ __('Managed by OSAS. Contact the office if this is incorrect.') }}</p>
        </header>

        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <x-input-label for="student_number" :value="__('Student Number')" />
                <x-text-input id="student_number" type="text" class="mt-1 block w-full bg-linen-sunk text-slate" :value="$user->student_number ?? __('Not set')" aria-describedby="enrollment-hint" disabled />
            </div>

            <div>
                <x-input-label for="year_level" :value="__('Year Level')" />
                <x-text-input id="year_level" type="text" class="mt-1 block w-full bg-linen-sunk text-slate" :value="$user->year_level ? __('Year :level', ['level' => $user->year_level]) : __('Not set')" aria-describedby="enrollment-hint" disabled />
            </div>

            <div>
                <x-input-label for="course" :value="__('Course')" />
                <x-text-input id="course" type="text" class="mt-1 block w-full bg-linen-sunk text-slate" :value="$user->course ?? __('Not set')" aria-describedby="enrollment-hint" disabled />
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
