{{--
    Three groups on one sheet, each set off by a rule: the scholarship's own
    fields, its required documents, and its deletion. Slots, deadline and
    status share a row from 640px; the status note runs the full width under
    them instead of stretching one column three times taller than the rest.
--}}
<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('admin.scholarships.show', $scholarship) }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Back to scholarship') }}</a>

            <h1 class="mt-6 text-xl font-semibold leading-tight text-gray-900">{{ __('Edit Scholarship') }}</h1>

            <form method="POST" action="{{ route('admin.scholarships.update', $scholarship) }}" class="mt-8 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="title" :value="__('Title')" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $scholarship->title)" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="type" :value="__('Type')" />
                    <select id="type" name="type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                        @foreach (\App\Enums\ScholarshipType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('type', $scholarship->type->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Description')" />
                    <textarea id="description" name="description" rows="6" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>{{ old('description', $scholarship->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <div class="grid gap-5 sm:grid-cols-3 sm:gap-4">
                        <div>
                            <x-input-label for="slots" :value="__('Available Slots')" />
                            <x-text-input id="slots" name="slots" type="number" min="1" class="mt-1 block w-full tabular-nums" :value="old('slots', $scholarship->slots)" required />
                            <x-input-error :messages="$errors->get('slots')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="deadline" :value="__('Application Deadline')" />
                            <x-text-input id="deadline" name="deadline" type="date" class="mt-1 block w-full tabular-nums" :value="old('deadline', $scholarship->deadline?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" aria-describedby="status-help" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green">
                                @foreach (\App\Enums\ScholarshipStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', $scholarship->status->value) === $status->value)>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>

                    <p id="status-help" class="mt-2 max-w-prose text-sm text-gray-500">
                        {{ __('Closed stops new applications. Set Ended once the grant period is over: approved students stop holding it, freeing one of their :limit scholarship places.', ['limit' => \App\Models\User::SCHOLARSHIP_LIMIT]) }}
                    </p>
                </div>

                <div>
                    <label for="stem_courses_only" class="flex cursor-pointer items-start gap-3">
                        <input id="stem_courses_only" type="checkbox" name="stem_courses_only" value="1" @checked(old('stem_courses_only', $scholarship->stem_courses_only))
                               class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-cvsu-green focus:ring-cvsu-green">
                        <span class="text-sm">
                            <span class="font-medium text-gray-900">{{ __('STEM courses only') }}</span>
                            <span class="mt-0.5 block text-gray-500">{{ __('Only students in a STEM course can apply. Students who already applied keep their applications.') }}</span>
                        </span>
                    </label>
                    <x-stem-courses class="ms-7 mt-2" />
                </div>

                <div class="flex items-center justify-end gap-4 pt-3">
                    <a href="{{ route('admin.scholarships.show', $scholarship) }}" class="rounded text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>

            <section aria-labelledby="documents-heading" class="mt-12 border-t border-gray-200 pt-10">
                <h2 id="documents-heading" class="text-base font-semibold text-gray-900">{{ __('Required Documents') }}</h2>
                <p class="mt-1 max-w-prose text-sm text-gray-500">{{ __('Documents applicants must upload for this scholarship. Post a sample so applicants can see what a correct one looks like.') }}</p>

                <ul class="mt-4 divide-y divide-gray-100 border-y border-gray-200">
                    @forelse ($scholarship->requiredDocuments as $document)
                        <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $document->name }}</p>
                                @if ($document->description)
                                    <p class="mt-1 text-sm text-gray-500">{{ $document->description }}</p>
                                @endif
                                @if ($document->hasSample())
                                    <p class="mt-1 text-sm text-gray-500 [overflow-wrap:anywhere]">{{ $document->sample_original_name }}</p>
                                    <x-sample-preview :document="$document" class="mt-3 w-full sm:w-64" />
                                @else
                                    <p class="mt-1 text-sm text-gray-500">{{ __('No sample posted.') }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-x-4 gap-y-2">
                                <form method="POST" action="{{ route('admin.scholarships.required-documents.sample.update', [$scholarship, $document]) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <label class="cursor-pointer rounded-sm text-sm font-semibold text-cvsu-green hover:text-cvsu-green-dark focus-within:ring-2 focus-within:ring-cvsu-green focus-within:ring-offset-2">
                                        {{ $document->hasSample() ? __('Replace sample') : __('Upload sample') }}
                                        <input type="file" name="sample" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" onchange="this.form.requestSubmit()">
                                    </label>
                                </form>
                                @if ($document->hasSample())
                                    <form method="POST" action="{{ route('admin.scholarships.required-documents.sample.destroy', [$scholarship, $document]) }}" onsubmit="return confirm('{{ __('Remove the sample for this document?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded text-sm font-medium text-gray-600 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">{{ __('Remove sample') }}</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.scholarships.required-documents.destroy', [$scholarship, $document]) }}" onsubmit="return confirm('{{ __('Remove this required document?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded text-sm font-medium text-red-600 hover:text-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">{{ __('Remove') }}</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="py-6 text-sm text-gray-500">{{ __('No documents required yet.') }}</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('admin.scholarships.required-documents.store', $scholarship) }}" enctype="multipart/form-data" class="mt-6 rounded-lg border border-gray-200 p-4">
                    @csrf
                    <div class="space-y-3">
                        <x-text-input name="name" type="text" class="block w-full" placeholder="{{ __('Document name (e.g. 2x2 Picture)') }}" aria-label="{{ __('Document name') }}" required />
                        <x-text-input name="description" type="text" class="block w-full" placeholder="{{ __('Notes for applicants (optional)') }}" aria-label="{{ __('Notes for applicants') }}" />
                        <div>
                            <x-input-label for="new-document-sample" :value="__('Sample for applicants (optional)')" />
                            <x-file-input id="new-document-sample" name="sample" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('JPG, PNG, or PDF, max 5MB.') }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <x-secondary-button type="submit">{{ __('Add') }}</x-secondary-button>
                    </div>
                </form>
                <x-input-error :messages="$errors->get('sample')" class="mt-2" />
            </section>

            <section aria-labelledby="delete-heading" class="mt-12 border-t border-gray-200 pt-10 sm:flex sm:items-start sm:justify-between sm:gap-8">
                <div>
                    <h2 id="delete-heading" class="text-base font-semibold text-gray-900">{{ __('Delete Scholarship') }}</h2>
                    <p class="mt-1 max-w-prose text-sm text-gray-500">
                        {{ __('Permanently removes this scholarship, its required documents and samples, and every application with the documents students uploaded. To stop accepting applications but keep the records, set the status to Closed instead.') }}
                    </p>
                </div>
                <x-danger-button
                    type="button"
                    class="mt-4 shrink-0 sm:mt-0"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-scholarship-deletion')"
                >{{ __('Delete') }}</x-danger-button>
            </section>

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
