<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Scholarship') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <form method="POST" action="{{ route('admin.scholarships.update', $scholarship) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $scholarship->title)" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>{{ old('description', $scholarship->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="slots" :value="__('Available Slots')" />
                            <x-text-input id="slots" name="slots" type="number" min="1" class="mt-1 block w-full" :value="old('slots', $scholarship->slots)" required />
                            <x-input-error :messages="$errors->get('slots')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="deadline" :value="__('Application Deadline')" />
                            <x-text-input id="deadline" name="deadline" type="date" class="mt-1 block w-full" :value="old('deadline', $scholarship->deadline?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green">
                                @foreach (\App\Enums\ScholarshipStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', $scholarship->status->value) === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                        <a href="{{ route('admin.scholarships.show', $scholarship) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Required Documents') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Documents applicants must upload for this scholarship.') }}</p>

                    <ul class="mt-4 divide-y divide-gray-100">
                        @forelse ($scholarship->requiredDocuments as $document)
                            <li class="flex items-center justify-between py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $document->name }}</p>
                                    @if ($document->description)
                                        <p class="text-sm text-gray-500">{{ $document->description }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.scholarships.required-documents.destroy', [$scholarship, $document]) }}" onsubmit="return confirm('{{ __('Remove this required document?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                </form>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-400">{{ __('No documents required yet.') }}</li>
                        @endforelse
                    </ul>

                    <form method="POST" action="{{ route('admin.scholarships.required-documents.store', $scholarship) }}" class="mt-4 flex items-start gap-3 border-t border-gray-100 pt-4">
                        @csrf
                        <div class="flex-1 space-y-2">
                            <x-text-input name="name" type="text" class="block w-full" placeholder="{{ __('Document name (e.g. 2x2 Picture)') }}" required />
                            <x-text-input name="description" type="text" class="block w-full" placeholder="{{ __('Notes for applicants (optional)') }}" />
                        </div>
                        <x-secondary-button type="submit">{{ __('Add') }}</x-secondary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
