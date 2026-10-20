<x-mail::message>
# {{ __('Scholarship Application Result') }}

{{ __('Dear Applicant,') }}

{{ __('Thank you for your interest in the') }} **{{ $title }}** {{ __('and for the time and effort you devoted to your application.') }}

{{ __('After careful evaluation, we regret to inform you that your application has not been approved, as your name was not included in the final list of qualified applicants.') }}

{{ __('This result does not affect your eligibility for other scholarships offered through OSAS. We encourage you to review the scholarships that are currently open and to apply for those for which you qualify.') }}

<x-mail::button :url="$scholarshipsUrl">
{{ __('Browse Open Scholarships') }}
</x-mail::button>

{{ __('We wish you success in your studies.') }}

{{ __('Respectfully,') }}<br>
{{ __('Office of Student Affairs and Services') }}<br>
{{ __('Cavite State University') }}

<x-mail::subcopy>
{{ __('This is a system-generated message. For questions about your application, please contact or visit the OSAS office.') }}
</x-mail::subcopy>
</x-mail::message>
