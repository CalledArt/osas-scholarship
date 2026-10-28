{{--
    A single column at max-w-leaf: a scholarship as a student reads it, the
    apply form, and the admin's post and edit forms. A scholarship has its
    standing mark, title block and season bar, then the decision register and
    its description; a form has its fields.
--}}
<div data-draft aria-hidden="true" class="draft min-h-[calc(100dvh-3.5rem)] bg-white lg:min-h-screen">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-leaf">
            <span class="draft-line w-40"></span>

            <p data-draft-heading class="mt-6 text-lead font-semibold text-ink [overflow-wrap:anywhere]"><span class="draft-line draft-line--lead w-2/3"></span></p>

            <div data-draft-part="mark" class="mt-5">
                <span class="draft-block h-5 w-20 rounded-sheet"></span>
            </div>

            <div data-draft-part="title-block">
                <div class="-mx-6 mt-5 border-t-2 border-ink bg-linen-sunk px-6 py-4 sm:mx-0">
                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-4">
                        @foreach (['w-8', 'w-24', 'w-28', 'w-20'] as $width)
                            <div>
                                <span class="draft-line draft-line--annot w-14"></span>
                                <span class="draft-line {{ $width }}"></span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-4 h-[3px] bg-rule"></div>
            </div>

            <div data-draft-part="prose">
                <div class="-mx-6 mt-10 divide-y divide-rule border-y border-rule">
                    @foreach (['w-5/6', 'w-2/3', 'w-3/4', 'w-1/2'] as $width)
                        <div class="flex gap-4 px-6 py-3">
                            <span class="draft-line draft-line--annot mt-[0.1875rem] w-14 shrink-0"></span>
                            <span class="draft-line {{ $width }}"></span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12">
                    <span class="draft-line draft-line--lead w-1/3"></span>
                    <div class="mt-3">
                        @foreach (['w-full', 'w-11/12', 'w-full', 'w-3/5'] as $width)
                            <span class="draft-line {{ $width }}"></span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div data-draft-part="fields" class="mt-8 space-y-5">
                @foreach (['w-24' => 'w-2/3', 'w-16' => 'w-1/3', 'w-32' => 'w-5/6', 'w-20' => 'w-1/4', 'w-28' => 'w-1/2'] as $label => $value)
                    <x-page-skeleton.field :label="$label" :value="$value" />
                @endforeach
            </div>
        </div>
    </div>
</div>
