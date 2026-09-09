<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $application->scholarship->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <x-badge :color="match ($application->status->value) {
                            'approved' => 'green',
                            'rejected' => 'red',
                            default => 'gold',
                        }">
                            {{ $application->status->label() }}
                        </x-badge>
                        <span class="text-sm text-gray-500">{{ __('Submitted') }} {{ $application->submitted_at->format('M j, Y') }}</span>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">{{ __('Submitted Documents') }}</h4>
                        <ul class="mt-2 divide-y divide-gray-100">
                            @foreach ($application->documents as $document)
                                <li class="flex items-center justify-between py-2 text-sm">
                                    <span class="text-gray-700">{{ $document->requiredDocument->name }}</span>
                                    <a href="{{ route('scholarship-applications.documents.show', [$application, $document]) }}" class="font-medium text-cvsu-green hover:text-cvsu-green-dark">{{ __('Download') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <a href="{{ route('scholarship-applications.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">&larr; {{ __('Back to My Applications') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
