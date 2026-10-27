@props(['label', 'mono' => false])

{{-- A key/value pair: Archivo Narrow annotation over the value it labels. --}}
<div>
    <dt class="font-annot text-annot uppercase text-slate">{{ $label }}</dt>
    <dd class="mt-0.5 text-ink {{ $mono ? 'font-mono' : '' }}">{{ $slot }}</dd>
</div>
