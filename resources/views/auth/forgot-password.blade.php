<x-guest-layout :title="__('Forgot your password?')">
    <div class="mb-8">
        <h2 class="text-lead font-semibold text-ink">{{ __('Forgot your password?') }}</h2>
        <p class="mt-1  text-slate">
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

    <p class="mt-8 text-center  text-slate">
        <a class="font-medium text-seal hover:text-seal-deep" href="{{ route('login') }}">
            {{ __('Back to sign in') }}
        </a>
    </p>
</x-guest-layout>
