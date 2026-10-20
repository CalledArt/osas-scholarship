{{--
    The documents come first because uploading them is the task; the details
    OSAS will see follow as a record to check just before submitting.
--}}
@php
    $details = [
        __('First Name') => $applicant->first_name ?? __('Not set'),
        __('Middle Name') => $applicant->middle_name ?: '—',
        __('Last Name') => $applicant->last_name ?? __('Not set'),
        __('Age') => $applicant->age ?? __('Not set'),
        __('Email') => $applicant->email,
        __('Student Number') => $applicant->student_number ?? __('Not set'),
        __('Course') => $applicant->course ?? __('Not set'),
        __('Year Level') => $applicant->year_level ?? __('Not set'),
        __('Contact Number') => $applicant->contact_number ?? __('Not set'),
        __('Address') => $applicant->address ?? __('Not set'),
    ];
@endphp

<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('scholarships.show', $scholarship) }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Back to scholarship') }}</a>

            <h1 class="mt-6 text-xl font-semibold leading-tight text-gray-900 [overflow-wrap:anywhere]">{{ __('Apply for :title', ['title' => $scholarship->title]) }}</h1>

            {{--
                Each document goes straight to storage the moment it is chosen,
                with real progress (see resources/js/document-uploads.js), so the
                form itself carries only small tokens: Vercel turns away request
                bodies over 4.5 MB. Pressing Submit while a file is still on its
                way waits for it. Once sending, the documents dim and turn inert,
                and the button refuses a second press. Without JavaScript the
                file inputs keep their names and the form uploads as before.
            --}}
            <form method="POST" action="{{ route('scholarships.apply.store', $scholarship) }}" enctype="multipart/form-data"
                  x-data="documentUploads(@js([
                      'documentIds' => $scholarship->requiredDocuments->modelKeys(),
                      'linkUrl' => route('scholarships.apply.uploads.store', $scholarship),
                      'maxBytes' => \App\Support\StagedUpload::MAX_BYTES,
                      'labels' => [
                          'progress' => __('Uploading · :loaded of :size'),
                          'uploaded' => __('Uploaded · :size'),
                          'wrongType' => __('Choose a JPG, PNG or PDF file.'),
                          'empty' => __('This file is empty. Choose another.'),
                          'tooLarge' => __('This file is :size. The limit is 5 MB, so choose a smaller copy.'),
                          'failed' => __("The upload didn't finish. Check your connection and try again."),
                          'forbidden' => __('This scholarship is no longer taking your application. Reload the page to see why.'),
                          'expired' => __('Your session expired. Reload the page, then choose the file again.'),
                      ],
                  ]))"
                  @submit="submit($event)"
                  @pageshow.window="if ($event.persisted) restore()"
                  class="mt-10 space-y-12">
                @csrf

                <section aria-labelledby="documents-heading" :inert="sending" :aria-busy="sending" :class="{ 'opacity-70': sending }" class="transition-opacity duration-150 ease-in-out">
                    <h2 id="documents-heading" class="text-base font-semibold text-gray-900">{{ __('Required Documents') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Upload each document below. Accepted formats: JPG, PNG, PDF (max 5MB each).') }}</p>

                    @if ($scholarship->requiredDocuments->isEmpty())
                        <p class="mt-3 border-y border-gray-200 py-6 text-sm text-gray-500">{{ __('No documents are required for this scholarship.') }}</p>
                    @else
                        <ul class="mt-3 divide-y divide-gray-100 border-y border-gray-200">
                            @foreach ($scholarship->requiredDocuments as $document)
                                <li class="flex flex-col gap-4 py-5 sm:flex-row sm:items-start sm:gap-6">
                                    <div class="min-w-0 flex-1">
                                        <x-input-label :for="'document-'.$document->id" :value="$document->name" />
                                        @if ($document->description)
                                            <p class="mt-1 text-sm text-gray-500">{{ $document->description }}</p>
                                        @endif
                                        {{-- The name comes off once the file is in storage, so the form does not send it a second time. --}}
                                        <x-file-input
                                            id="document-{{ $document->id }}"
                                            name="documents[{{ $document->id }}]"
                                            x-bind:name="rows[{{ $document->id }}].token ? null : 'documents[{{ $document->id }}]'"
                                            x-on:change="choose($event.target, {{ $document->id }})"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            class="mt-3 w-full"
                                            aria-describedby="document-{{ $document->id }}-status"
                                            required
                                        />
                                        <input type="hidden" name="uploads[{{ $document->id }}]" x-bind:value="rows[{{ $document->id }}].token" x-bind:disabled="! rows[{{ $document->id }}].token" disabled>

                                        <div id="document-{{ $document->id }}-status" aria-live="polite">
                                            <div x-show="['uploading', 'done', 'failed'].includes(rows[{{ $document->id }}].state)" x-cloak class="mt-3 flex items-center gap-3">
                                                <img x-show="rows[{{ $document->id }}].preview" x-bind:src="rows[{{ $document->id }}].preview" alt="" class="h-10 w-10 shrink-0 rounded-md border border-gray-200 object-cover">
                                                <svg x-show="! rows[{{ $document->id }}].preview" class="h-10 w-10 shrink-0 rounded-md border border-gray-200 bg-gray-50 p-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>

                                                <div class="min-w-0 flex-1">
                                                    {{-- The native input already shows the file's name, so this line only reports progress. --}}
                                                    <p x-show="rows[{{ $document->id }}].state !== 'failed'" class="flex items-center gap-1.5 text-sm tabular-nums" x-bind:class="rows[{{ $document->id }}].state === 'done' ? 'text-cvsu-green-dark' : 'text-gray-500'">
                                                        <svg x-show="rows[{{ $document->id }}].state === 'done'" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 10.5 3.5 3.5 7.5-8" />
                                                        </svg>
                                                        <span x-text="progressText({{ $document->id }})"></span>
                                                    </p>
                                                    <div x-show="rows[{{ $document->id }}].state !== 'failed'"
                                                         class="mt-1.5 h-1 overflow-hidden rounded-full bg-gray-200"
                                                         role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                                         x-bind:aria-valuenow="Math.round(fraction({{ $document->id }}) * 100)"
                                                         aria-label="{{ __('Upload of :document', ['document' => $document->name]) }}">
                                                        <div class="h-full w-full origin-left rounded-full bg-cvsu-green transition-transform duration-150 ease-out motion-reduce:transition-none"
                                                             x-bind:style="`transform: scaleX(${fraction({{ $document->id }})})`"></div>
                                                    </div>
                                                    <p x-show="rows[{{ $document->id }}].state === 'failed'" class="mt-1 text-sm text-red-600">
                                                        <span x-text="rows[{{ $document->id }}].message"></span>
                                                        <button type="button" x-on:click="retry({{ $document->id }})" class="ms-1 rounded font-semibold text-red-700 underline underline-offset-2 hover:text-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">{{ __('Try again') }}</button>
                                                    </p>
                                                </div>
                                            </div>

                                            <p x-show="rows[{{ $document->id }}].state === 'invalid'" x-cloak class="mt-2 text-sm text-red-600" x-text="rows[{{ $document->id }}].message"></p>
                                        </div>

                                        <x-input-error :messages="[...$errors->get('documents.'.$document->id), ...$errors->get('uploads.'.$document->id)]" class="mt-2" />
                                    </div>
                                    @if ($document->hasSample())
                                        <x-sample-preview :document="$document" class="w-full shrink-0 sm:w-56" />
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section aria-labelledby="information-heading">
                    <h2 id="information-heading" class="text-base font-semibold text-gray-900">{{ __('Your Information') }}</h2>
                    <p class="mt-1 max-w-prose text-sm text-gray-500">
                        {{ __('Pulled automatically from your account so it cannot be altered. Need a correction? Update your') }}
                        <a href="{{ route('profile.edit') }}" class="rounded font-medium text-cvsu-green hover:text-cvsu-green-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('profile') }}</a>
                        {{ __('first.') }}
                    </p>

                    <dl class="mt-3 grid border-t border-gray-200 text-sm sm:grid-cols-2 sm:gap-x-8">
                        @foreach ($details as $label => $value)
                            <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-3 border-b border-gray-200 py-2.5">
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="text-gray-900 [overflow-wrap:anywhere]">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <section aria-labelledby="certification-heading">
                    <h2 id="certification-heading" class="text-base font-semibold text-gray-900">{{ __('Certification') }}</h2>

                    <label for="certification" class="mt-3 flex cursor-pointer items-start gap-3 border-y border-gray-200 py-4">
                        <input id="certification" type="checkbox" name="certification" value="1" required @checked(old('certification'))
                               aria-describedby="certification-error"
                               class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-cvsu-green focus:ring-cvsu-green">
                        <span class="max-w-prose text-sm leading-relaxed text-gray-700">
                            {{ __('I hereby certify that all documents submitted with this application are true, correct, and authentic, and have not been altered in any way. I understand that the submission of any false or falsified document shall render this application null and void.') }}
                        </span>
                    </label>

                    <x-input-error id="certification-error" :messages="$errors->get('certification')" class="mt-2" />
                </section>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('scholarships.show', $scholarship) }}" class="rounded text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Cancel') }}</a>
                    {{-- The labels share one grid cell, so the button keeps its width whichever one shows. --}}
                    <x-primary-button x-bind:disabled="sending || finishing">
                        <span class="grid">
                            <span class="col-start-1 row-start-1" :class="{ 'invisible': sending || finishing }">{{ __('Submit application') }}</span>
                            <span class="invisible col-start-1 row-start-1 inline-flex items-center justify-center gap-2" :class="{ '!visible': finishing }">
                                <svg class="h-4 w-4 shrink-0 animate-spin motion-reduce:animate-pulse" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ __('Finishing uploads...') }}
                            </span>
                            <span class="invisible col-start-1 row-start-1 inline-flex items-center justify-center gap-2" :class="{ '!visible': sending }">
                                <svg class="h-4 w-4 shrink-0 animate-spin motion-reduce:animate-pulse" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ __('Submitting...') }}
                            </span>
                        </span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
