{{--
    One sheet, read top to bottom: the details everyone can see, then, for
    staff only, the password and the account's removal, each set off by a rule.
--}}
<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Profile') }}</h1>

            <div class="mt-8 max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Students sign in with Google and have no password, and deleting an account is confirmed with one. --}}
            @unless ($user->isStudent())
                <div class="mt-12 max-w-xl border-t border-gray-200 pt-10">
                    @include('profile.partials.update-password-form')
                </div>

                <div class="mt-12 max-w-xl border-t border-gray-200 pt-10">
                    @include('profile.partials.delete-user-form')
                </div>
            @endunless
        </div>
    </div>
</x-app-layout>
