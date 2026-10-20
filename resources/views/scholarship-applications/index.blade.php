{{--
    Every application the student has sent, newest first, as the same ruled
    rows the dashboard uses so the two read as one list at different lengths.
--}}
<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('My Applications') }}</h1>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($applications->isEmpty())
                <p class="mt-8 border-y border-gray-200 py-6 text-sm text-gray-500">
                    {{ __("You haven't applied to any scholarships yet.") }}
                    <a href="{{ route('scholarships.index') }}" class="rounded font-semibold text-cvsu-green hover:text-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Browse scholarships') }}</a>
                </p>
            @else
                <ul class="-mx-3 mt-8 divide-y divide-gray-100 border-y border-gray-200">
                    @foreach ($applications as $application)
                        <li>
                            <a href="{{ route('scholarship-applications.show', $application) }}"
                               class="group flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-cvsu-green">
                                <div class="min-w-0 flex-1">
                                    <p data-vt-title class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere] group-hover:text-cvsu-green">{{ $application->scholarship->title }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Applied :date', ['date' => $application->submitted_at->format('M j, Y')]) }}</p>
                                </div>

                                <x-badge class="shrink-0" :color="match ($application->status->value) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'gold',
                                }">
                                    {{ $application->status->label() }}
                                </x-badge>

                                <span class="hidden shrink-0 text-sm font-semibold text-cvsu-green sm:inline">{{ __('View') }} &rarr;</span>
                                <svg class="h-5 w-5 shrink-0 text-gray-500 sm:hidden" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                                </svg>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($applications->hasPages())
                    <div class="mt-6">
                        {{ $applications->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
