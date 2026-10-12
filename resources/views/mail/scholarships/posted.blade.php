<x-mail::message>
# {{ __('New scholarship: :title', ['title' => $title]) }}

{{ __('The Office of Student Affairs and Services has posted a new scholarship on :app.', ['app' => config('app.name')]) }}

{{ $summary }}

**{{ __('Slots') }}:** {{ $slots }}
@if ($deadline)
**{{ __('Deadline') }}:** {{ $deadline }}
@endif

<x-mail::button :url="$scholarshipUrl">
{{ __('View scholarship') }}
</x-mail::button>

{{ __('Sign in with your CvSU Google account to see the required documents and apply.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
