<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $scholarship->title }}
            </h2>
            <a href="{{ route('admin.scholarships.edit', $scholarship) }}" class="text-sm font-medium text-cvsu-green hover:text-cvsu-green-dark">{{ __('Edit') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('decisionResults'))
                @php $results = session('decisionResults'); @endphp
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Pass List Results') }}</h3>
                    </div>
                    <div class="p-6 space-y-4 text-sm">
                        <p class="text-gray-700">
                            {{ __(':approved approved, :rejected rejected.', ['approved' => count($results['approved']), 'rejected' => count($results['rejected'])]) }}
                        </p>

                        @if (! empty($results['skipped_capacity']))
                            <div>
                                <p class="font-medium text-amber-700">{{ __('Matched but not approved — no slots remaining:') }}</p>
                                <ul class="mt-1 list-disc list-inside text-gray-600">
                                    @foreach ($results['skipped_capacity'] as $row)
                                        <li>{{ $row['name'] }} ({{ $row['email'] }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! empty($results['ambiguous']))
                            <div>
                                <p class="font-medium text-amber-700">{{ __('Left pending — name matches more than one applicant:') }}</p>
                                <ul class="mt-1 list-disc list-inside text-gray-600">
                                    @foreach ($results['ambiguous'] as $row)
                                        <li>{{ $row['name'] }} ({{ __(':count applicants', ['count' => $row['count']]) }})</li>
                                    @endforeach
                                </ul>
                                <p class="mt-1 text-xs text-gray-400">{{ __('Add their student numbers to the pass list and upload it again.') }}</p>
                            </div>
                        @endif

                        @if (! empty($results['unmatched']))
                            <div>
                                <p class="font-medium text-amber-700">{{ __("Rows in the file that didn't match any pending applicant:") }}</p>
                                <ul class="mt-1 list-disc list-inside text-gray-600">
                                    @foreach ($results['unmatched'] as $row)
                                        <li>
                                            {{ $row['name'] ?? __('(no name)') }}
                                            @if ($row['student_number'])
                                                — {{ $row['student_number'] }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <x-badge :color="$scholarship->status->value === 'open' ? 'green' : 'gray'">
                            {{ $scholarship->status->label() }}
                        </x-badge>
                        <span class="text-sm text-gray-500">{{ $scholarship->remainingSlots() }} / {{ $scholarship->slots }} {{ __('slots remaining') }}</span>
                        @if ($scholarship->deadline)
                            <span class="text-sm text-gray-500">&middot; {{ __('Deadline') }}: {{ $scholarship->deadline->format('M j, Y') }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $scholarship->description }}</p>

                    @if ($scholarship->requiredDocuments->isNotEmpty())
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900">{{ __('Required Documents') }}</h4>
                            <ul class="mt-1 list-disc list-inside text-sm text-gray-600">
                                @foreach ($scholarship->requiredDocuments as $document)
                                    <li>{{ $document->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            @php $pendingCount = $scholarship->applications->where('status', \App\Enums\ApplicationStatus::Pending)->count(); @endphp

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Upload Pass List') }}</h3>

                    @if ($pendingCount > 0)
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Upload a CSV of who passed — a "student_number" column, a "name" column, or both (student number is matched first since it\'s unique; name is the fallback). A header-less single column of names also works. Every other pending applicant (:count) will be rejected automatically.', ['count' => $pendingCount]) }}
                        </p>

                        <form
                            method="POST"
                            action="{{ route('admin.scholarships.applications.import-decisions', $scholarship) }}"
                            enctype="multipart/form-data"
                            class="mt-4 flex items-start gap-3"
                            onsubmit="return confirm('{{ __('This will approve matched names and reject every other pending applicant. Continue?') }}')"
                        >
                            @csrf
                            <x-file-input name="file" accept=".csv,.txt" class="flex-1" required />
                            <x-secondary-button type="submit">{{ __('Apply Decisions') }}</x-secondary-button>
                        </form>
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    @else
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('There are no pending applicants to decide on right now.') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Applicants') }} ({{ $scholarship->applications->count() }})</h3>
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse ($scholarship->applications as $application)
                        <li class="p-6 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $application->user->name }}
                                        @if ($application->user->student_number)
                                            <span class="ms-1 font-normal text-gray-400">({{ $application->user->student_number }})</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $application->user->email }}
                                        @if ($application->user->age)
                                            &middot; {{ __(':age years old', ['age' => $application->user->age]) }}
                                        @endif
                                        @if ($application->user->course)
                                            &middot; {{ $application->user->course }}
                                        @endif
                                    </p>
                                </div>
                                <x-badge :color="match ($application->status->value) {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'gold',
                                }">
                                    {{ $application->status->label() }}
                                </x-badge>
                            </div>

                            @if ($application->documents->isNotEmpty())
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($application->documents as $document)
                                        <a href="{{ route('scholarship-applications.documents.show', [$application, $document]) }}" class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600 hover:border-cvsu-green hover:text-cvsu-green">
                                            {{ $document->requiredDocument->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </li>
                    @empty
                        <li class="p-6 text-center text-sm text-gray-500">{{ __('No applications yet.') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
