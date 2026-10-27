{{-- Profile, for either role: the plain heading, then one card of rows with a single Save at its foot. --}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-white lg:min-h-screen">
    <div class="px-6 py-6 lg:px-10">
        <div class="mx-auto max-w-[88rem]">
            <p data-draft-heading class="text-lead font-semibold text-ink"><span class="draft-line draft-line--lead w-24"></span></p>

            <div class="mt-4 rounded-lg border border-rule bg-white">
                @foreach (['w-36', 'w-32', 'w-28'] as $width)
                    <div @class(['grid gap-x-8 gap-y-3 p-5 lg:grid-cols-[14rem_1fr]', 'border-t border-rule' => ! $loop->first])>
                        <div>
                            <span class="draft-line {{ $width }}"></span>
                            <span class="draft-line mt-0.5 w-48"></span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach (['w-20', 'w-24', 'w-24'] as $label)
                                <div>
                                    <span class="draft-line draft-line--annot {{ $label }}"></span>
                                    <span class="draft-block mt-1 h-[2.625rem] w-full rounded-sheet"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-end border-t border-rule px-5 py-4">
                    <span class="draft-block h-[2.625rem] w-24 rounded-sheet"></span>
                </div>
            </div>
        </div>
    </div>
</div>
