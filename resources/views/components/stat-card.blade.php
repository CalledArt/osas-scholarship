@props(['label', 'value', 'tone' => 'seal'])

{{--
    One figure on the admin dashboard, drawn to the mock OSAS supplied
    (decided 2026-10-24) like the welcome banner above it: a rounded tinted
    box with a round icon disc, which DESIGN.md otherwise reserves against.
    Renders a <div> of <dt>/<dd> pairs, so place it inside a <dl>. `icon`
    takes the SVG; the default slot is the note under the figure.
--}}
@php
    $tones = [
        'seal' => ['box' => 'border-seal-tint bg-seal-wash', 'disc' => 'bg-seal-tint/60 text-seal'],
        'blue' => ['box' => 'border-blue-200 bg-blue-50', 'disc' => 'bg-blue-100 text-blue-700'],
        'gold' => ['box' => 'border-gold/40 bg-gold-wash/60', 'disc' => 'bg-gold/60 text-ink'],
    ][$tone];
@endphp

<div {{ $attributes->class(['rounded-lg border p-5', $tones['box']]) }}>
    <span class="flex h-10 w-10 items-center justify-center rounded-full {{ $tones['disc'] }}" aria-hidden="true">{{ $icon }}</span>
    <dt class="mt-4 font-semibold text-graphite">{{ $label }}</dt>
    <dd class="mt-1 text-figure font-semibold tabular-nums text-ink">{{ $value }}</dd>
    <dd class="mt-2 text-slate">{{ $slot }}</dd>
</div>
