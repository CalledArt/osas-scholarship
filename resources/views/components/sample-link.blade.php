@props(['document'])

@php
    $modalName = 'sample-'.$document->id;
    $previewUrl = route('scholarships.required-documents.sample', [$document->scholarship_id, $document]);
    $downloadUrl = route('scholarships.required-documents.sample.download', [$document->scholarship_id, $document]);
@endphp

{{-- The preview is teleported to <body>: this link sits inside lists and form labels, where a modal cannot be nested. --}}
<span x-data {{ $attributes }}>
    <button type="button" x-on:click="$dispatch('open-modal', '{{ $modalName }}')" class="font-medium text-cvsu-green hover:text-cvsu-green-dark focus:outline-none focus-visible:rounded-sm focus-visible:ring-2 focus-visible:ring-cvsu-green">
        {{ __('View sample') }}<span class="sr-only"> {{ __('of :document', ['document' => $document->name]) }}</span>
    </button>

    <template x-teleport="body">
        <x-modal :name="$modalName" max-width="4xl" focusable>
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('Sample: :document', ['document' => $document->name]) }}</h2>
                    <p class="truncate text-sm text-gray-500">{{ $document->sample_original_name }}</p>
                </div>
                <button type="button" x-on:click="$dispatch('close')" class="shrink-0 rounded-md p-1 text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green">
                    <span class="sr-only">{{ __('Close') }}</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Rendered only while open, so a page listing several samples does not download them all up front. --}}
            <div class="bg-gray-50">
                <template x-if="show">
                    @if ($document->sampleIsImage())
                        <img src="{{ $previewUrl }}" alt="{{ __('Sample of :document', ['document' => $document->name]) }}" class="mx-auto block max-h-[70vh] w-auto max-w-full object-contain">
                    @else
                        <object data="{{ $previewUrl }}" type="application/pdf" class="block h-[70vh] w-full">
                            <p class="px-6 py-12 text-center text-sm text-gray-600">
                                {{ __('This browser cannot preview PDFs here. Download a copy to view it.') }}
                            </p>
                        </object>
                    @endif
                </template>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    {{ __('Open in new tab') }}
                </a>
                <a href="{{ $downloadUrl }}" class="inline-flex items-center gap-2 rounded-lg bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cvsu-green-dark focus:outline-none focus:ring-2 focus:ring-cvsu-gold focus:ring-offset-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    {{ __('Download a copy') }}
                </a>
            </div>
        </x-modal>
    </template>
</span>
