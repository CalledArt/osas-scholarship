<x-mail::message>
# {{ __('Congratulations!') }}

{{ __('You passed. The Office of Student Affairs and Services has approved your application for the :title scholarship.', ['title' => $title]) }}

{{ __('Keep an eye on your email and the OSAS office for instructions on what happens next.') }}

<x-mail::button :url="$applicationsUrl">
{{ __('View my applications') }}
</x-mail::button>

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
