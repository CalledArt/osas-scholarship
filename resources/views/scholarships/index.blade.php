<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Available Scholarships') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($scholarships as $scholarship)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $scholarship->title }}</h3>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $scholarship->description }}</p>
                            </div>
                            <x-badge :color="$scholarship->isOpenForApplications() ? 'green' : 'gray'">
                                {{ $scholarship->isOpenForApplications() ? __('Open') : __('Closed') }}
                            </x-badge>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                            <span>
                                {{ $scholarship->remainingSlots() }} {{ __('slots remaining') }}
                                @if ($scholarship->deadline)
                                    &middot; {{ __('Deadline') }}: {{ $scholarship->deadline->format('M j, Y') }}
                                @endif
                            </span>
                            <a href="{{ route('scholarships.show', $scholarship) }}" class="font-medium text-cvsu-green hover:text-cvsu-green-dark">{{ __('View details') }} &rarr;</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100 p-6 text-center text-sm text-gray-500">
                    {{ __('There are no scholarships posted at the moment. Please check back later.') }}
                </div>
            @endforelse

            @if ($scholarships->hasPages())
                <div>{{ $scholarships->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
