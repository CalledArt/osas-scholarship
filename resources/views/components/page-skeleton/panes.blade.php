{{-- The student Dashboard and Scholarships page: the banner, then "Where you stand" beside the open-scholarship boxes. --}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-linen lg:min-h-screen">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-page-skeleton.banner />

            <div class="mt-6 grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start">
                <div>
                    <p class="text-lead font-semibold text-ink">{{ __('Where you stand') }}</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <div data-draft-part="id-box" class="border border-rule bg-white shadow-seat sm:col-span-2 lg:col-span-1">
                            <div class="px-5 py-4">
                                <span class="draft-line w-3/4"></span>
                                <span class="draft-line mt-0.5 w-1/2"></span>
                            </div>
                            <div class="grid grid-cols-2 border-t border-rule">
                                <div class="px-5 py-2.5">
                                    <span class="draft-line draft-line--annot w-24"></span>
                                    <span class="draft-line w-28"></span>
                                </div>
                                <div class="border-l border-rule px-5 py-2.5">
                                    <span class="draft-line draft-line--annot w-16"></span>
                                    <span class="draft-line w-14"></span>
                                </div>
                            </div>
                        </div>

                        <div class="border border-rule bg-white px-5 py-4 shadow-seat">
                            <span class="draft-line w-2/3"></span>
                            <span class="draft-line mt-0.5 w-1/3"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-lead font-semibold text-ink">{{ __('Available to apply') }}</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach (['w-3/4', 'w-1/2', 'w-2/3', 'w-7/12'] as $width)
                            <div class="flex flex-col border border-rule bg-white shadow-seat">
                                <div class="flex flex-1 items-start justify-between gap-3 px-5 py-4">
                                    <span class="draft-line {{ $width }}"></span>
                                    <span class="draft-block h-5 w-16 shrink-0 rounded-sheet"></span>
                                </div>
                                <div class="border-t border-rule px-5 py-2.5">
                                    <span class="draft-line draft-line--annot w-12"></span>
                                    <span class="draft-line w-24"></span>
                                    <span class="draft-line w-20"></span>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-t border-rule px-5 py-2.5">
                                    <span class="draft-line w-24"></span>
                                    <span class="draft-block h-[2.125rem] w-[4.25rem] shrink-0 rounded-sheet"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
