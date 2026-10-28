{{--
    Search Student, Edit Student Data and a fund allocation: the banner, the
    filter panel, then the list in a bordered panel. From lg the page fits the
    screen, as theirs do, and the rows stop at its foot. Columns join as the
    width allows, so a phone never scrolls sideways through bars.
--}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-white lg:min-h-screen">
    <div class="px-6 py-10 lg:flex lg:h-screen lg:flex-col lg:px-10 lg:py-6">
        <div class="mx-auto max-w-[100rem] lg:flex lg:min-h-0 lg:w-full lg:flex-col">
            <x-page-skeleton.banner>
                <span data-draft-part="action" class="draft-block h-[2.625rem] w-52 shrink-0 self-start rounded-sheet sm:self-auto"></span>
            </x-page-skeleton.banner>

            <div class="mt-6 rounded-lg border border-rule bg-linen-sunk p-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1.25fr)_auto] lg:items-end">
                    <x-page-skeleton.field label="w-24" value="w-1/3" />
                    @foreach (['w-14' => 'w-2/3', 'w-16' => 'w-3/4', 'w-12' => 'w-2/3'] as $label => $value)
                        <x-page-skeleton.field data-draft-part="filters" :label="$label" :value="$value" />
                    @endforeach
                    <div class="sm:col-span-2 lg:col-span-1">
                        <span class="draft-block h-[2.625rem] w-24 rounded-sheet"></span>
                    </div>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-lg border border-rule bg-white lg:flex lg:min-h-0 lg:flex-1 lg:flex-col">
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-5 py-4">
                    <span class="draft-line draft-line--lead w-52"></span>
                    <span class="draft-line w-32"></span>
                </div>

                <div class="grid grid-cols-[minmax(0,3fr)_minmax(0,2fr)] gap-6 border-t border-rule bg-linen-sunk px-5 py-2.5 sm:grid-cols-[minmax(0,3fr)_minmax(0,2fr)_minmax(0,2fr)] lg:grid-cols-[minmax(0,3fr)_minmax(0,3fr)_minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)]">
                    <span class="draft-line w-16"></span>
                    <span class="draft-line w-12"></span>
                    <span class="draft-line hidden w-14 sm:block"></span>
                    <span class="draft-line hidden w-10 lg:block"></span>
                    <span class="draft-line hidden w-12 lg:block"></span>
                </div>

                <div class="divide-y divide-rule border-t border-rule lg:min-h-0 lg:flex-1 lg:overflow-hidden">
                    @foreach (['w-3/4', 'w-1/2', 'w-2/3', 'w-5/12', 'w-7/12', 'w-3/5', 'w-1/2', 'w-2/3', 'w-5/12', 'w-3/4'] as $width)
                        <div class="grid grid-cols-[minmax(0,3fr)_minmax(0,2fr)] items-center gap-6 px-5 py-3 sm:grid-cols-[minmax(0,3fr)_minmax(0,2fr)_minmax(0,2fr)] lg:grid-cols-[minmax(0,3fr)_minmax(0,3fr)_minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)]">
                            <span class="draft-line {{ $width }}"></span>
                            <span class="draft-line w-5/6"></span>
                            <span class="draft-line hidden w-2/3 sm:block"></span>
                            <span class="draft-line hidden w-12 lg:block"></span>
                            <span class="draft-block hidden h-5 w-16 rounded-sheet lg:block"></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
