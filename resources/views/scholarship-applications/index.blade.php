<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <ul class="divide-y divide-gray-100">
                    @forelse ($applications as $application)
                        <li class="flex items-center justify-between p-6">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $application->scholarship->title }}</p>
                                <p class="text-sm text-gray-500">{{ __('Submitted') }} {{ $application->submitted_at->format('M j, Y') }}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <x-badge :color="match ($application->status->value) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'gold',
                                }">
                                    {{ $application->status->label() }}
                                </x-badge>
                                <a href="{{ route('scholarship-applications.show', $application) }}" class="text-sm font-medium text-cvsu-green hover:text-cvsu-green-dark">{{ __('View') }}</a>
                            </div>
                        </li>
                    @empty
                        <li class="p-6 text-center text-sm text-gray-500">
                            {{ __("You haven't applied to any scholarships yet.") }}
                            <a href="{{ route('scholarships.index') }}" class="font-medium text-cvsu-green hover:text-cvsu-green-dark">{{ __('Browse scholarships') }}</a>
                        </li>
                    @endforelse
                </ul>

                @if ($applications->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
