<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Forgot your password?') }}</h2>
        <p class="mt-1 text-sm text-gray-500">
            {{ __('No problem. Enter your email address and we will send you a link to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        <a class="font-medium text-cvsu-green hover:text-cvsu-green-dark" href="{{ route('login') }}">
            {{ __('Back to sign in') }}
        </a>
    </p>
</x-guest-layout>
