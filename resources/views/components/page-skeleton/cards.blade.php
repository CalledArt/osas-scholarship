{{-- My Applications, Manage Scholarships and Fund Allocation: the banner over the auto-fill grid of cards. --}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-white lg:min-h-screen">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-page-skeleton.banner>
                <span data-draft-part="action" class="draft-block h-[2.625rem] w-44 shrink-0 self-start rounded-sheet sm:self-auto"></span>
            </x-page-skeleton.banner>

            <div class="mt-8 grid grid-cols-[repeat(auto-fill,minmax(min(20rem,100%),1fr))] gap-4">
                @foreach (['w-3/4', 'w-1/2', 'w-2/3', 'w-5/6', 'w-7/12', 'w-2/3'] as $width)
                    <div class="flex flex-col rounded-sheet border border-rule bg-white p-5">
                        <div class="flex items-start justify-between gap-3">
                            <span class="draft-line draft-line--lead {{ $width }}"></span>
                            <span class="draft-block h-5 w-16 shrink-0 rounded-sheet"></span>
                        </div>
                        <span class="draft-line mt-1 w-1/3"></span>

                        <div class="mt-auto pt-5">
                            <div class="grid grid-cols-2 gap-3 border-t border-rule pt-4">
                                @foreach (['w-24', 'w-28'] as $figure)
                                    <div>
                                        <span class="draft-line w-16"></span>
                                        <span class="draft-line draft-line--lead mt-0.5 {{ $figure }}"></span>
                                    </div>
                                @endforeach
                            </div>
                            <span class="draft-line mt-4 w-32"></span>
                        </div>

                        <span class="draft-line mt-5 w-28"></span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
