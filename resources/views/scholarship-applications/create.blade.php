{{--
    The documents come first because uploading them is the task; the details
    OSAS will see follow as a record to check just before submitting.

    The standing sits under the heading rather than being left behind on the
    previous page: the deadline and the remaining capacity are what decide
    whether starting this is worth it, and a student who opens the form and
    returns an hour later has no other prompt to finish.
--}}
@php
    $availability = $scholarship->availability();
    $isOpen = $availability === \App\Enums\ScholarshipAvailability::Open;

    // Grouped so no single block asks the reader to hold more than four rows.
    $groups = [
        __('Name') => [
            __('Last Name') => $applicant->last_name !== null ? mb_strtoupper($applicant->last_name) : __('Not set'),
            __('First Name') => $applicant->first_name !== null ? mb_strtoupper($applicant->first_name) : __('Not set'),
            __('Middle Name') => $applicant->middle_name ? mb_strtoupper($applicant->middle_name) : '—',
            __('Age') => $applicant->age ?? __('Not set'),
        ],
        __('Academic record') => [
            __('Student Number') => $applicant->student_number ?? __('Not set'),
            __('Course') => $applicant->course ?? __('Not set'),
            __('Year Level') => $applicant->year_level ?? __('Not set'),
        ],
        __('Contact') => [
            __('Email') => $applicant->email,
            __('Contact Number') => $applicant->contact_number ?? __('Not set'),
            __('Address') => $applicant->address ?? __('Not set'),
        ],
    ];
@endphp

<x-app-layout :title="__('Apply for :title', ['title' => $scholarship->title])" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-leaf">
            <x-back-link :href="route('scholarships.show', $scholarship)">{{ __('Back to scholarship') }}</x-back-link>

            <h1 class="mt-6 text-lead font-semibold text-ink [overflow-wrap:anywhere]">{{ __('Apply for :title', ['title' => $scholarship->title]) }}</h1>

            <x-title-block class="-mx-6 mt-4 sm:mx-0">
                <x-field :label="__('Slots')">{{ $scholarship->slots }}</x-field>
                <x-field :label="$isOpen ? __('Closes') : __('Closed')">
                    @if ($scholarship->deadline)
                        <time datetime="{{ $scholarship->deadline->toDateString() }}">{{ $scholarship->deadline->format('M j, Y') }}</time>
                    @else
                        <span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No deadline') }}</span>
                    @endif
                </x-field>
                <x-field :label="__('Standing')">
                    <x-availability :scholarship="$scholarship" :urgent="true" />
                </x-field>
            </x-title-block>

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
                          'busy' => __('Too many uploads at once. Wait a minute, then choose the file again.'),
                      ],
                  ]))"
                  @submit="submit($event)"
                  @pageshow.window="if ($event.persisted) restore()"
                  class="mt-10 space-y-12">
                @csrf

                <section aria-labelledby="documents-heading" :inert="sending" :aria-busy="sending" :class="{ 'opacity-70': sending }" class="transition-opacity duration-150 ease-in-out">
                    <h2 id="documents-heading" class="text-lead font-semibold text-ink">{{ __('Required Documents') }}</h2>
                    <p class="mt-1 text-slate">{{ __('Upload each document below. Accepted formats: JPG, PNG, PDF (max 5MB each).') }}</p>

                    @if ($scholarship->requiredDocuments->isEmpty())
                        <p class="mt-3 border-y border-rule py-6 text-slate">{{ __('No documents are required for this scholarship.') }}</p>
                    @else
                        <ul class="mt-3 divide-y divide-rule border-y border-rule">
                            @foreach ($scholarship->requiredDocuments as $document)
                                <li class="flex flex-col gap-4 py-5 sm:flex-row sm:items-start sm:gap-6">
                                    <div class="min-w-0 flex-1">
                                        <x-input-label :for="'document-'.$document->id" :value="$document->name" />
                                        @if ($document->description)
                                            <p class="mt-1 text-slate">{{ $document->description }}</p>
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
                                                {{-- Built only once there is a preview, so no image ever stands without a source. --}}
                                                <template x-if="rows[{{ $document->id }}].preview">
                                                    <img x-bind:src="rows[{{ $document->id }}].preview" alt="" class="h-10 w-10 shrink-0 rounded-sheet border border-rule object-cover">
                                                </template>
                                                <svg x-show="! rows[{{ $document->id }}].preview" class="h-10 w-10 shrink-0 rounded-sheet border border-rule bg-linen-sunk p-2 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>

                                                <div class="min-w-0 flex-1">
                                                    {{-- The native input already shows the file's name, so this line only reports progress. --}}
                                                    <p x-show="rows[{{ $document->id }}].state !== 'failed'" class="flex items-center gap-1.5" x-bind:class="rows[{{ $document->id }}].state === 'done' ? 'text-grant-deep' : 'text-slate'">
                                                        <svg x-show="rows[{{ $document->id }}].state === 'done'" class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 10.5 3.5 3.5 7.5-8" />
                                                        </svg>
                                                        <span x-text="progressText({{ $document->id }})"></span>
                                                    </p>
                                                    <div x-show="rows[{{ $document->id }}].state !== 'failed'"
                                                         class="mt-1.5 h-1 overflow-hidden bg-rule"
                                                         role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                                         x-bind:aria-valuenow="Math.round(fraction({{ $document->id }}) * 100)"
                                                         aria-label="{{ __('Upload of :document', ['document' => $document->name]) }}">
                                                        <div class="h-full w-full origin-left bg-seal transition-transform duration-150 ease-out motion-reduce:transition-none"
                                                             x-bind:style="`transform: scaleX(${fraction({{ $document->id }})})`"></div>
                                                    </div>
                                                    <p x-show="rows[{{ $document->id }}].state === 'failed'" class="mt-1 text-oxide">
                                                        <span x-text="rows[{{ $document->id }}].message"></span>
                                                        <button type="button" x-on:click="retry({{ $document->id }})" class="ms-1 rounded-sheet font-semibold text-oxide-deep underline underline-offset-2 hover:text-oxide focus:outline-none focus-visible:ring-2 focus-visible:ring-oxide focus-visible:ring-offset-2">{{ __('Try again') }}</button>
                                                    </p>
                                                </div>
                                            </div>

                                            <p x-show="rows[{{ $document->id }}].state === 'invalid'" x-cloak class="mt-2 text-oxide" x-text="rows[{{ $document->id }}].message"></p>
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
                    <h2 id="information-heading" class="text-lead font-semibold text-ink">{{ __('Your Information') }}</h2>
                    <p class="mt-1 text-slate">
                        {{ __('Pulled automatically from your account so it cannot be altered.') }}
                        {{ __('Your name, date of birth, contact number and address are yours to correct on your') }}
                        <a href="{{ route('profile.edit') }}" class="rounded-sheet font-medium text-seal underline underline-offset-4 hover:text-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('profile') }}</a>{{ __('.') }}
                        {{ __('Course, year level and student number are kept by OSAS — email the office if one of them is wrong.') }}
                    </p>

                    <div class="mt-4 space-y-6">
                        @foreach ($groups as $group => $rows)
                            <div>
                                <h3 class="font-annot text-annot uppercase text-slate">{{ $group }}</h3>
                                <dl class="mt-2 grid border-t border-rule sm:grid-cols-2 sm:gap-x-8">
                                    @foreach ($rows as $label => $value)
                                        <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-3 border-b border-rule py-2.5">
                                            <dt class="text-slate">{{ $label }}</dt>
                                            <dd class="text-ink [overflow-wrap:anywhere]">{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section aria-labelledby="certification-heading">
                    <h2 id="certification-heading" class="text-lead font-semibold text-ink">{{ __('Certification') }}</h2>

                    <label for="certification" class="mt-3 flex cursor-pointer items-start gap-3 border-y border-rule py-4">
                        <input id="certification" type="checkbox" name="certification" value="1" required @checked(old('certification'))
                               aria-describedby="certification-error"
                               class="mt-0.5 h-4 w-4 shrink-0 rounded-sheet border-rule-strong text-seal focus:ring-seal">
                        <span class="leading-relaxed text-graphite">
                            {{ __('I hereby certify that all documents submitted with this application are true, correct, and authentic, and have not been altered in any way. I understand that the submission of any false or falsified document shall render this application null and void.') }}
                        </span>
                    </label>

                    <x-input-error id="certification-error" :messages="$errors->get('certification')" class="mt-2" />
                </section>

                <div class="flex flex-col gap-4 border-t border-rule pt-6">
                    {{-- There is no edit and no withdraw, so the form says so before
                         the press rather than after it. --}}
                    <p class="text-slate">
                        {{ __('Once sent, an application cannot be changed or withdrawn. OSAS decides every applicant at once with one pass list.') }}
                    </p>

                    <div class="flex items-center justify-end gap-4">
                        {{-- Cancel discards files already in storage, so it asks first
                             when there is something to lose. --}}
                        <a href="{{ route('scholarships.show', $scholarship) }}"
                           x-on:click="if (Object.values(rows).some(row => row.token || row.state === 'uploading') && ! confirm(@js(__('Leave without applying? The files you have already uploaded will be discarded.')))) $event.preventDefault()"
                           class="rounded-sheet font-medium text-graphite hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Cancel') }}</a>

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
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
