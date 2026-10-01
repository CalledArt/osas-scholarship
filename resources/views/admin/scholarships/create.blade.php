<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Post Scholarship') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <form method="POST" action="{{ route('admin.scholarships.store') }}" enctype="multipart/form-data" class="p-6 space-y-6" x-data="{
                    documents: [],
                    nextKey: 0,
                    addDocument() { this.documents.push({ key: this.nextKey++, name: '', description: '' }) },
                    removeDocument(index) { this.documents.splice(index, 1) },
                }">
                    @csrf

                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="slots" :value="__('Available Slots')" />
                            <x-text-input id="slots" name="slots" type="number" min="1" class="mt-1 block w-full" :value="old('slots')" required />
                            <x-input-error :messages="$errors->get('slots')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="deadline" :value="__('Application Deadline')" />
                            <x-text-input id="deadline" name="deadline" type="date" class="mt-1 block w-full" :value="old('deadline')" />
                            <x-input-error :messages="$errors->get('deadline')" class="mt-2" />
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('Required Documents') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('Documents applicants must upload, e.g. 2x2 Picture.') }}</p>
                            </div>
                            <button type="button" @click="addDocument()" class="text-sm font-medium text-cvsu-green hover:text-cvsu-green-dark">
                                {{ __('+ Add document') }}
                            </button>
                        </div>

                        <div class="mt-4 space-y-4">
                            {{-- Keyed by a stable id, not the index: a file input cannot be x-model'd, so removing a row must take that row's own input with it. --}}
                            <template x-for="(document, index) in documents" :key="document.key">
                                <div class="flex items-start gap-3 rounded-lg border border-gray-200 p-4">
                                    <div class="flex-1 space-y-2">
                                        <input type="text" :name="'required_documents[' + index + '][name]'" x-model="document.name" placeholder="{{ __('Document name (e.g. 2x2 Picture)') }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                                        <input type="text" :name="'required_documents[' + index + '][description]'" x-model="document.description" placeholder="{{ __('Notes for applicants (optional)') }}" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-cvsu-green focus:ring-cvsu-green">
                                        <div>
                                            <label class="text-xs font-medium text-gray-600" :for="'document-sample-' + document.key">{{ __('Sample for applicants (optional): JPG, PNG, or PDF, max 5MB') }}</label>
                                            <x-file-input ::id="'document-sample-' + document.key" ::name="'required_documents[' + index + '][sample]'" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full" />
                                        </div>
                                    </div>
                                    <button type="button" @click="removeDocument(index)" class="text-gray-400 hover:text-red-600" title="{{ __('Remove') }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <p x-show="documents.length === 0" class="text-sm text-gray-400">{{ __('No documents required yet.') }}</p>
                        </div>
                        <x-input-error :messages="\Illuminate\Support\Arr::flatten($errors->get('required_documents.*.name'))" class="mt-2" />
                        <x-input-error :messages="\Illuminate\Support\Arr::flatten($errors->get('required_documents.*.sample'))" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                        <a href="{{ route('admin.scholarships.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Post Scholarship') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
