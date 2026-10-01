<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $scholarship->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <x-badge :color="$scholarship->isOpenForApplications() ? 'green' : 'gray'">
                            {{ $scholarship->isOpenForApplications() ? __('Open') : __('Closed') }}
                        </x-badge>
                        <span class="text-sm text-gray-500">{{ $scholarship->remainingSlots() }} / {{ $scholarship->slots }} {{ __('slots remaining') }}</span>
                        @if ($scholarship->deadline)
                            <span class="text-sm text-gray-500">&middot; {{ __('Deadline') }}: {{ $scholarship->deadline->format('M j, Y') }}</span>
                        @endif
                    </div>

                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $scholarship->description }}</p>

                    @if ($scholarship->requiredDocuments->isNotEmpty())
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">{{ __('Documents you will need to upload') }}</h4>
                            <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                                @foreach ($scholarship->requiredDocuments as $document)
                                    <li>
                                        {{ $document->name }}
                                        @if ($document->description)
                                            <span class="text-gray-400">— {{ $document->description }}</span>
                                        @endif
                                        @if ($document->hasSample())
                                            <x-sample-link :document="$document" class="ms-1" />
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 pt-4">
                        @if ($application)
                            <p class="text-sm text-gray-600">
                                {{ __('You applied on :date.', ['date' => $application->submitted_at->format('M j, Y')]) }}
                            </p>
                            <x-badge :color="match ($application->status->value) {
                                'approved' => 'green',
                                'rejected' => 'red',
                                default => 'gold',
                            }" class="mt-2">
                                {{ __('Status') }}: {{ $application->status->label() }}
                            </x-badge>
                        @elseif ($scholarship->isOpenForApplications())
                            <a href="{{ route('scholarships.apply', $scholarship) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cvsu-green-dark focus:outline-none focus:ring-2 focus:ring-cvsu-gold focus:ring-offset-2">
                                {{ __('Apply Now') }}
                            </a>
                        @else
                            <p class="text-sm text-gray-500">{{ __('This scholarship is no longer accepting applications.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
