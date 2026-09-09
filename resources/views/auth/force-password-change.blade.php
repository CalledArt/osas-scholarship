<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Set a new password') }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('Your account was created with a temporary password. Choose a new one before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="current_password" :value="__('Temporary Password')" />
            <x-text-input id="current_password" name="current_password" type="password" class="block mt-1 w-full" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Save and Continue') }}</x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('Log out instead') }}</button>
    </form>
</x-guest-layout>
