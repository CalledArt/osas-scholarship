@props(['messages' => null])

{{--
    The container always renders, even with nothing to say, so a field pointing
    at it with aria-describedby is never a dangling reference while the form is
    valid — which is the normal case.

    The loop is written without surrounding whitespace on purpose: Blade would
    otherwise leave a text node inside the list, the element would not match
    :empty, and `empty:hidden` would never hide it.
--}}
<ul {{ $attributes->merge(['class' => 'space-y-1 text-oxide empty:hidden']) }}>@foreach ((array) ($messages ?? []) as $message)<li>{{ $message }}</li>@endforeach</ul>
