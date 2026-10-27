@props(['document'])

@php
    $modalName = 'sample-'.$document->id;
    $previewUrl = route('scholarships.required-documents.sample', [$document->scholarship_id, $document]);
    $downloadUrl = route('scholarships.required-documents.sample.download', [$document->scholarship_id, $document]);
@endphp

{{-- An image sample is drafted (the light sweeps its frame) until it has loaded or failed; a PDF's viewer gives no load signal everywhere, so it is not. --}}
<figure x-data="{ previewLoaded: true }" {{ $attributes->merge(['class' => 'overflow-hidden border border-rule bg-white']) }}>
    <div class="relative h-48 bg-linen-sunk" :class="{ 'draft-fill': ! previewLoaded }">
        @if ($document->sampleIsImage())
            <img src="{{ $previewUrl }}" alt="{{ __('Sample of :document', ['document' => $document->name]) }}" loading="lazy" class="h-full w-full object-contain"
                 x-init="previewLoaded = $el.complete" x-on:load="previewLoaded = true" x-on:error="previewLoaded = true">
        @else
            <object data="{{ $previewUrl }}#toolbar=0&amp;navpanes=0&amp;view=FitH" type="application/pdf" tabindex="-1" class="block h-full w-full">
                <div class="flex h-full flex-col items-center justify-center gap-2 px-4 text-center">
                    <svg class="h-8 w-8 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <p class="font-annot text-annot uppercase text-graphite">{{ __('PDF sample') }}</p>
                </div>
            </object>
        @endif

        {{-- Laid over the preview so a click anywhere on it, even on the PDF viewer, opens the full-size copy. Keyboard users get the Enlarge button below. --}}
        <button type="button" x-on:click="$dispatch('open-modal', '{{ $modalName }}')" tabindex="-1" aria-hidden="true" class="absolute inset-0 h-full w-full cursor-zoom-in"></button>
    </div>

    <figcaption class="flex items-center justify-between gap-3 border-t border-rule px-3 py-2">
        <span class="font-annot text-annot uppercase text-slate">{{ __('Sample') }}</span>
        <span class="flex items-center gap-3">
            <button type="button" x-on:click="$dispatch('open-modal', '{{ $modalName }}')" class="font-medium text-seal hover:text-seal-deep focus:outline-none focus-visible:rounded-sheet focus-visible:ring-2 focus-visible:ring-seal">
                {{ __('Enlarge') }}<span class="sr-only"> {{ __('sample of :document', ['document' => $document->name]) }}</span>
            </button>
            <a href="{{ $downloadUrl }}" download class="font-medium text-graphite hover:text-ink focus:outline-none focus-visible:rounded-sheet focus-visible:ring-2 focus-visible:ring-seal">
                {{ __('Download') }}<span class="sr-only"> {{ __('sample of :document', ['document' => $document->name]) }}</span>
            </a>
        </span>
    </figcaption>

    {{-- The enlarged view is teleported to <body>: this preview sits inside lists and forms, where a modal cannot be nested. --}}
    <template x-teleport="body">
        <x-modal :name="$modalName" max-width="4xl" focusable :label="__('Sample: :document', ['document' => $document->name])">
            <div class="flex items-start justify-between gap-4 border-b border-rule px-6 py-4">
                <div class="min-w-0">
                    <h2 class="text-lead font-semibold text-ink">{{ __('Sample: :document', ['document' => $document->name]) }}</h2>
                    <p class="truncate text-slate">{{ $document->sample_original_name }}</p>
                </div>
                {{-- Graphite, not a 2.5:1 grey: this is a control, so it owes the 3:1 floor. --}}
                <button type="button" x-on:click="$dispatch('close')" class="shrink-0 rounded-sheet p-1 text-graphite hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-seal">
                    <span class="sr-only">{{ __('Close') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Rendered only while open, so the page does not load every sample a second time at full size. --}}
            <div class="bg-linen-sunk">
                <template x-if="show">
                    @if ($document->sampleIsImage())
                        <img src="{{ $previewUrl }}" alt="{{ __('Sample of :document', ['document' => $document->name]) }}" class="mx-auto block max-h-[70vh] w-auto max-w-full object-contain">
                    @else
                        <object data="{{ $previewUrl }}" type="application/pdf" class="block h-[70vh] w-full">
                            <p class="px-6 py-12 text-center text-graphite">
                                {{ __('This browser cannot preview PDFs here. Download a copy to view it.') }}
                            </p>
                        </object>
                    @endif
                </template>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-rule px-6 py-4">
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="font-medium text-seal hover:text-seal-deep">
                    {{ __('Open in new tab') }}
                </a>
                <a href="{{ $downloadUrl }}" download class="inline-flex items-center gap-2 rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-seat transition hover:bg-seal-deep focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    {{ __('Download a copy') }}
                </a>
            </div>
        </x-modal>
    </template>
</figure>
