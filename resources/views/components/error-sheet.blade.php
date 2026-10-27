@props(['heading', 'code'])

{{--
    The body of a student-reachable error, rendered inside the application shell
    when there is a session to render it in. Reopening an apply URL already used
    is the most natural "did that go through?" action on a phone, and it used to
    land on an unstyled page with no seal, no rail and no way back.
--}}
<div class="px-6 py-10 lg:px-10">
    <div class="mx-auto max-w-leaf">
        <p class="font-annot text-annot uppercase text-slate">{{ __('Error :code', ['code' => $code]) }}</p>
        <h1 class="mt-1 text-lead font-semibold text-ink">{{ $heading }}</h1>

        <div class="mt-4 leading-relaxed text-graphite">{{ $slot }}</div>

        <div class="mt-8 flex flex-wrap gap-3">
            @auth
                <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                   class="inline-flex items-center justify-center rounded-sheet border border-transparent bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2">
                    {{ __('Go to dashboard') }}
                </a>
                @if (auth()->user()->isStudent())
                    <a href="{{ route('scholarship-applications.index') }}"
                       class="inline-flex items-center justify-center rounded-sheet border border-rule-strong bg-linen px-4 py-2 font-semibold text-graphite shadow-seat transition duration-150 ease-in-out hover:bg-linen-sunk focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">
                        {{ __('My Applications') }}
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-sheet border border-transparent bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition duration-150 ease-in-out hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2">
                    {{ __('Sign in') }}
                </a>
            @endauth
        </div>
    </div>
</div>
