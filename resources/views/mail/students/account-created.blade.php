<x-mail::message>
# {{ __('Hello, :name', ['name' => $name]) }}

{{ __('The Office of Student Affairs and Services has created a :app account for you. You can use it to browse scholarships and submit your applications.', ['app' => config('app.name')]) }}

**{{ __('Email') }}:** {{ $email }}
**{{ __('Temporary password') }}:** {{ $password }}

<x-mail::button :url="$loginUrl">
{{ __('Sign in') }}
</x-mail::button>

{{ __('This password is temporary. The first time you sign in, the portal will ask you to replace it with one only you know before you can go any further.') }}

{{ __('If you did not expect this email, please contact the OSAS office.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
