{{--
    Editing one student's data: the way back, the banner, then one card whose
    sections set their title on the left from lg. The section titles are
    fixed, so they are written.
--}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-white lg:min-h-screen">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <span class="draft-line w-36"></span>

            <div class="mt-4">
                <x-page-skeleton.banner />
            </div>

            <div class="mt-6 overflow-hidden rounded-lg border border-rule bg-white">
                @foreach ([__('Name') => 3, __('Enrollment') => 6, __('Student status') => 1] as $heading => $fields)
                    <div @class(['grid gap-x-8 gap-y-4 px-5 py-6 sm:px-6 lg:grid-cols-[10rem_minmax(0,1fr)]', 'border-t border-rule' => ! $loop->first])>
                        <p class="text-lead font-semibold text-ink">{{ $heading }}</p>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach (array_slice(['w-20', 'w-24', 'w-28', 'w-16', 'w-24', 'w-20'], 0, $fields) as $width)
                                <div>
                                    <span class="draft-line draft-line--annot {{ $width }}"></span>
                                    <span class="draft-block mt-1 h-[2.625rem] w-full rounded-sheet"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
