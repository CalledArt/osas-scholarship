@props(['href'])

{{--
    The way back to the list a page was opened from, above its heading. One look
    on every screen: Graphite and underlined, because Seal Green colours the
    links that go forward. Its hit area reaches 12px above and below the text,
    so a thumb finds it on a phone without the line taking more room.
--}}
<a href="{{ $href }}" {{ $attributes->class('relative rounded-sheet font-medium text-graphite underline underline-offset-4 before:absolute before:-inset-x-2 before:-inset-y-3 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2') }}>{{ $slot }}</a>
