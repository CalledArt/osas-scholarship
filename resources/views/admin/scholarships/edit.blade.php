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
                    <p class="text-sm text-gray-500">{{ __('Documents applicants must upload for this scholarship. Post a sample so applicants can see what a correct one looks like.') }}</p>

                    <ul class="mt-4 divide-y divide-gray-100">
                        @forelse ($scholarship->requiredDocuments as $document)
                            <li class="flex flex-col gap-2 py-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ $document->name }}</p>
                                    @if ($document->description)
                                        <p class="text-sm text-gray-500">{{ $document->description }}</p>
                                    @endif
                                    @if ($document->hasSample())
                                        <p class="mt-1 text-sm text-gray-500 [overflow-wrap:anywhere]">{{ $document->sample_original_name }}</p>
                                        <x-sample-preview :document="$document" class="mt-2 w-full sm:w-64" />
                                    @else
                                        <p class="mt-1 text-sm text-gray-400">{{ __('No sample posted.') }}</p>
                                    @endif
                                </div>
                                <div class="flex shrink-0 flex-wrap items-center gap-x-4 gap-y-1">
                                    <form method="POST" action="{{ route('admin.scholarships.required-documents.sample.update', [$scholarship, $document]) }}" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <label class="cursor-pointer rounded-sm text-sm font-medium text-cvsu-green hover:text-cvsu-green-dark focus-within:ring-2 focus-within:ring-cvsu-green focus-within:ring-offset-2">
                                            {{ $document->hasSample() ? __('Replace sample') : __('Upload sample') }}
                                            <input type="file" name="sample" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                    @if ($document->hasSample())
                                        <form method="POST" action="{{ route('admin.scholarships.required-documents.sample.destroy', [$scholarship, $document]) }}" onsubmit="return confirm('{{ __('Remove the sample for this document?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-gray-500 hover:text-red-600">{{ __('Remove sample') }}</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.scholarships.required-documents.destroy', [$scholarship, $document]) }}" onsubmit="return confirm('{{ __('Remove this required document?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">{{ __('Remove') }}</button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-400">{{ __('No documents required yet.') }}</li>
                        @endforelse
                    </ul>

                    <form method="POST" action="{{ route('admin.scholarships.required-documents.store', $scholarship) }}" enctype="multipart/form-data" class="mt-4 flex items-start gap-3 border-t border-gray-100 pt-4">
                        @csrf
                        <div class="flex-1 space-y-2">
                            <x-text-input name="name" type="text" class="block w-full" placeholder="{{ __('Document name (e.g. 2x2 Picture)') }}" required />
                            <x-text-input name="description" type="text" class="block w-full" placeholder="{{ __('Notes for applicants (optional)') }}" />
                            <div>
                                <x-input-label for="new-document-sample" :value="__('Sample for applicants (optional)')" />
                                <x-file-input id="new-document-sample" name="sample" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full" />
                                <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG, or PDF, max 5MB.') }}</p>
                            </div>
                        </div>
                        <x-secondary-button type="submit">{{ __('Add') }}</x-secondary-button>
                    </form>
                    <x-input-error :messages="$errors->get('sample')" class="mt-2" />
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-red-100">
                <div class="p-6 sm:flex sm:items-start sm:justify-between sm:gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Delete Scholarship') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Permanently removes this scholarship, its required documents and samples, and every application with the documents students uploaded. To stop accepting applications but keep the records, set the status to Closed instead.') }}
                        </p>
                    </div>
                    <x-danger-button
                        type="button"
                        class="mt-4 shrink-0 sm:mt-0"
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-scholarship-deletion')"
                    >{{ __('Delete') }}</x-danger-button>
                </div>
            </div>

            <x-modal name="confirm-scholarship-deletion" focusable>
                <form method="POST" action="{{ route('admin.scholarships.destroy', $scholarship) }}" class="p-6">
                    @csrf
                    @method('DELETE')

                    <h2 class="text-lg font-medium text-gray-900 [overflow-wrap:anywhere]">
                        {{ __('Delete ":title"?', ['title' => $scholarship->title]) }}
                    </h2>

                    <p class="mt-2 text-sm text-gray-600">
                        @if ($scholarship->applications_count > 0)
                            {{ trans_choice('{1} This also deletes 1 student application and the documents uploaded with it.|[2,*] This also deletes :count student applications and every document uploaded with them.', $scholarship->applications_count, ['count' => $scholarship->applications_count]) }}
                        @else
                            {{ __('No students have applied yet.') }}
                        @endif
                        {{ __('This cannot be undone.') }}
                    </p>

                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button>
                            {{ __('Delete Scholarship') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>
</x-app-layout>
