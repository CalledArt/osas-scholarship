<x-mail::message>
# {{ __('Notice of Approval') }}

{{ __('Dear Applicant,') }}

{{ __('We are pleased to inform you that your application for the') }} **{{ $title }}** {{ __('has been approved by the Office of Student Affairs and Services (OSAS) of Cavite State University.') }}

{{ __('OSAS will send further instructions regarding the next steps, including any additional requirements. Please monitor your CvSU email account and OSAS announcements regularly so that you do not miss any important updates.') }}

<x-mail::button :url="$applicationsUrl">
{{ __('View My Applications') }}
</x-mail::button>

{{ __('Congratulations, and we wish you continued success in your studies.') }}

{{ __('Respectfully,') }}<br>
{{ __('Office of Student Affairs and Services') }}<br>
{{ __('Cavite State University') }}

<x-mail::subcopy>
{{ __('This is a system-generated message. For questions about your application, please contact or visit the OSAS office.') }}
</x-mail::subcopy>
</x-mail::message>
