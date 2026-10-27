@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-grant-deep']) }}>
        {{ $status }}
    </div>
@endif
