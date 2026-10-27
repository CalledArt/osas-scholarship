{{--
    One record's page: an application, a scholarship's admin page, the Act.
    The way back, the banner and its action, the title block, then the work
    and its reference as two panels from lg.
--}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-white lg:min-h-screen">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <span class="draft-line w-40"></span>

            <div class="mt-4">
                <x-page-skeleton.banner>
                    <span data-draft-part="action" class="draft-block h-10 w-40 shrink-0 self-start rounded-sheet sm:self-auto"></span>
                </x-page-skeleton.banner>
            </div>

            <div data-draft-part="title-block" class="mt-6 border-t-2 border-ink bg-linen-sunk px-6 py-4">
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-4">
                    @foreach (['w-28', 'w-24', 'w-12', 'w-20'] as $width)
                        <div>
                            <span class="draft-line draft-line--annot w-16"></span>
                            <span class="draft-line {{ $width }}"></span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-start xl:grid-cols-[minmax(0,1fr)_28rem]">
                <x-page-skeleton.panel :rows="5" />
                <x-page-skeleton.panel :rows="3" />
            </div>
        </div>
    </div>
</div>
