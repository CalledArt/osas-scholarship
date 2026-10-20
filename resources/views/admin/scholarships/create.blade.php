{{--
    Two groups, set apart by a rule rather than a box: what the scholarship
    is, then what applicants must send. A preset at the top fills in the
    title, type and course limit of a scholarship OSAS posts every year.
    Slots and deadline pair on one row from 640px and stack below it, where
    two inputs side by side get too narrow for a date.
--}}
<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('admin.scholarships.index') }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Back to scholarships') }}</a>

            <h1 class="mt-6 text-xl font-semibold leading-tight text-gray-900">{{ __('Post Scholarship') }}</h1>

            <form method="POST" action="{{ route('admin.scholarships.store') }}" enctype="multipart/form-data" class="mt-8 space-y-10" x-data="{
                presets: @js($presets),
                preset: '',
                title: @js(old('title', '')),
                type: @js(old('type', '')),
                stemCoursesOnly: @js((bool) old('stem_courses_only')),
                applyPreset() {
                    const preset = this.presets[this.preset]
                    if (! preset) return
                    this.title = preset.title
                    this.type = preset.type
                    this.stemCoursesOnly = preset.stem_courses_only
                },
                documents: [],
                nextKey: 0,
                sending: false,
                addDocument() { this.documents.push({ key: this.nextKey++, name: '', description: '' }) },
                removeDocument(index) { this.documents.splice(index, 1) },
            }"
                  @submit="sending = true"
                  @pageshow.window="if ($event.persisted) sending = false">
                @csrf

                {{-- Posting emails every student inside the request, so it can take a while: the fields lock and the button refuses a second press, which would post the scholarship twice. --}}
                <section class="space-y-5 transition-opacity duration-150 ease-in-out" :inert="sending" :class="{ 'opacity-70': sending }">
                    {{-- No name: the preset only fills in the fields below, which are what gets posted. --}}
                    <div>
                        <x-input-label for="preset" :value="__('Start from a preset')" />
                        <select id="preset" x-model="preset" x-on:change="applyPreset()" aria-describedby="preset-help" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" autofocus>
                            <option value="">{{ __('None, start blank') }}</option>
                            @foreach ($presets as $key => $preset)
                                <option value="{{ $key }}">{{ $preset['title'] }}</option>
                            @endforeach
                        </select>
                        <p id="preset-help" class="mt-2 text-sm text-gray-500">{{ __('Fills in the title, type and course limit. You can still change any of them.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" x-model="title" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="type" :value="__('Type')" />
                        <select id="type" name="type" x-model="type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                            <option value="">{{ __('Choose a type') }}</option>
                            @foreach (\App\Enums\ScholarshipType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="6" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <x-input-label for="slots" :value="__('Available Slots')" />
                            <x-text-input id="slots" name="slots" type="number" min="1" class="mt-1 block w-full tabular-nums" :value="old('slots')" required />
                            <x-input-error :messages="$errors->get('slots')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="deadline" :value="__('Application Deadline')" />
                            <x-text-input id="deadline" name="deadline" type="date" class="mt-1 block w-full tabular-nums" :value="old('deadline')" />
                            <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <label for="stem_courses_only" class="flex cursor-pointer items-start gap-3">
                            <input id="stem_courses_only" type="checkbox" name="stem_courses_only" value="1" x-model="stemCoursesOnly" @checked(old('stem_courses_only'))
                                   class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-cvsu-green focus:ring-cvsu-green">
                            <span class="text-sm">
                                <span class="font-medium text-gray-900">{{ __('STEM courses only') }}</span>
                                <span class="mt-0.5 block text-gray-500">{{ __('Only students in a STEM course can apply, and only they are emailed about it.') }}</span>
                            </span>
                        </label>
                        <x-stem-courses class="ms-7 mt-2" />
                    </div>
                </section>

                <section aria-labelledby="documents-heading" class="border-t border-gray-200 pt-8 transition-opacity duration-150 ease-in-out" :inert="sending" :class="{ 'opacity-70': sending }">
                    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-3">
                        <div>
                            <h2 id="documents-heading" class="text-base font-semibold text-gray-900">{{ __('Required Documents') }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Documents applicants must upload, e.g. 2x2 Picture.') }}</p>
                        </div>
                        <x-secondary-button x-on:click="addDocument()" class="shrink-0 gap-2">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" d="M10 4.5v11M4.5 10h11" />
                            </svg>
                            {{ __('Add document') }}
                        </x-secondary-button>
                    </div>

                    <div class="mt-4 space-y-3">
                        {{-- Keyed by a stable id, not the index: a file input cannot be x-model'd, so removing a row must take that row's own input with it. --}}
                        <template x-for="(document, index) in documents" :key="document.key">
                            <div class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
                                <div class="min-w-0 flex-1 space-y-3">
                                    <input type="text" :name="'required_documents[' + index + '][name]'" x-model="document.name" placeholder="{{ __('Document name (e.g. 2x2 Picture)') }}" aria-label="{{ __('Document name') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                                    <input type="text" :name="'required_documents[' + index + '][description]'" x-model="document.description" placeholder="{{ __('Notes for applicants (optional)') }}" aria-label="{{ __('Notes for applicants') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green">
                                    <div>
                                        <label class="text-xs font-medium text-gray-600" :for="'document-sample-' + document.key">{{ __('Sample for applicants (optional): JPG, PNG, or PDF, max 5MB') }}</label>
                                        <x-file-input ::id="'document-sample-' + document.key" ::name="'required_documents[' + index + '][sample]'" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full" />
                                    </div>
                                </div>
                                <button type="button" @click="removeDocument(index)" class="-m-1 shrink-0 rounded-md p-1 text-gray-500 transition-colors duration-150 ease-in-out hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500" title="{{ __('Remove') }}">
                                    <span class="sr-only">{{ __('Remove') }}</span>
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <p x-show="documents.length === 0" class="border-y border-gray-200 py-6 text-sm text-gray-500">{{ __('No documents required yet.') }}</p>
                    </div>
                    <x-input-error :messages="\Illuminate\Support\Arr::flatten($errors->get('required_documents.*.name'))" class="mt-2" />
                    <x-input-error :messages="\Illuminate\Support\Arr::flatten($errors->get('required_documents.*.sample'))" class="mt-2" />
                </section>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.scholarships.index') }}" class="rounded text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Cancel') }}</a>
                    <x-primary-button x-bind:disabled="sending">
                        <span class="grid">
                            <span class="col-start-1 row-start-1" :class="{ 'invisible': sending }">{{ __('Post Scholarship') }}</span>
                            <span class="invisible col-start-1 row-start-1 inline-flex items-center justify-center gap-2" :class="{ '!visible': sending }">
                                <svg class="h-4 w-4 shrink-0 animate-spin motion-reduce:animate-pulse" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ __('Posting...') }}
                            </span>
                        </span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
