<x-mail::message>
# {{ __('Your application result') }}

{{ __('Thank you for applying for the :title scholarship. Your name was not on the pass list, so your application was not approved this time.', ['title' => $title]) }}

{{ __('Other scholarships may still be open. You are welcome to apply for any you qualify for.') }}

<x-mail::button :url="$scholarshipsUrl">
{{ __('Browse scholarships') }}
</x-mail::button>

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
