@props(['label' => 'w-20', 'value' => 'w-1/2'])

{{-- An input as drawn: its label to come over a white frame in a lighter rule than a live field's, its value to come inside. --}}
<div {{ $attributes }}>
    <span class="draft-line draft-line--annot {{ $label }}"></span>
    <span class="mt-1 flex h-[2.625rem] items-center rounded-sheet border border-rule bg-white px-3"><span class="draft-line {{ $value }}"></span></span>
</div>
