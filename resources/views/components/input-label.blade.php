@props(['value'])

{{-- A field label is annotation around data: Archivo Narrow, uppercase, tracked. --}}
<label {{ $attributes->merge(['class' => 'block font-annot text-annot uppercase text-slate']) }}>
    {{ $value ?? $slot }}
</label>
