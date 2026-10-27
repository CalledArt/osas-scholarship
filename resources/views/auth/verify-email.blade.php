<x-guest-layout :title="__('Verify your email')">
    <div class="mb-8">
        <h2 class="text-lead font-semibold text-ink">{{ __('Verify your email') }}</h2>
        <p class="mt-1  text-slate">
            {{ __("Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.") }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium  text-seal">
            {{ __('A new verification link has been sent to the email address you provided.') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline  text-graphite hover:text-ink rounded-sheet focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-seal">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
