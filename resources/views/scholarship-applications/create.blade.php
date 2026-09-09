<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Apply for :title', ['title' => $scholarship->title]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="border-b border-gray-100 p-6">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Your Information') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('Pulled automatically from your account so it cannot be altered. Need a correction? Update your') }}
                        <a href="{{ route('profile.edit') }}" class="font-medium text-cvsu-green hover:text-cvsu-green-dark">{{ __('profile') }}</a>
                        {{ __('first.') }}
                    </p>

                    <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Name') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Age') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->age ?? __('Not set') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Email') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Student Number') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->student_number ?? __('Not set') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Course') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->course ?? __('Not set') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Year Level') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->year_level ?? __('Not set') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Contact Number') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->contact_number ?? __('Not set') }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Address') }}</dt>
                            <dd class="text-sm text-gray-900">{{ $applicant->address ?? __('Not set') }}</dd>
                        </div>
                    </dl>
                </div>

                <form method="POST" action="{{ route('scholarships.apply.store', $scholarship) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Required Documents') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Upload each document below. Accepted formats: JPG, PNG, PDF (max 5MB each).') }}</p>

                        @if ($scholarship->requiredDocuments->isEmpty())
                            <p class="mt-4 text-sm text-gray-400">{{ __('No documents are required for this scholarship.') }}</p>
                        @else
                            <div class="mt-4 space-y-4">
                                @foreach ($scholarship->requiredDocuments as $document)
                                    <div>
                                        <x-input-label :for="'document-'.$document->id" :value="$document->name" />
                                        @if ($document->description)
                                            <p class="text-xs text-gray-500">{{ $document->description }}</p>
                                        @endif
                                        <x-file-input
                                            id="document-{{ $document->id }}"
                                            name="documents[{{ $document->id }}]"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            class="mt-1 w-full"
                                            required
                                        />
                                        <x-input-error :messages="$errors->get('documents.'.$document->id)" class="mt-2" />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                        <a href="{{ route('scholarships.show', $scholarship) }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Submit Application') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
