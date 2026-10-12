@php
    $student = Auth::user();
    $title = 'DOST-SEI Merit Scholarship';
    $badges = [
        'pending' => 'bg-cvsu-gold/20 text-cvsu-green-dark',
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];
    $labels = collect(\App\Enums\ApplicationStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
    $welcome = e(__('Welcome, :name!', ['name' => $student->full_name]));
    $count = fn (int $number): string => '<span class="font-semibold tabular-nums text-gray-900">'.$number.'</span>';
    $openRows = [
        ['key' => 'sample', 'title' => $title, 'meta' => __('Closes in :count days', ['count' => 9]).' · '.trans_choice(':count slot left|:count slots left', 2, ['count' => 2])],
        ['key' => 'other', 'title' => 'CHED Tulong Dunong Program', 'meta' => __('Closes in :count days', ['count' => 23]).' · '.trans_choice(':count slot left|:count slots left', 9, ['count' => 9])],
    ];
    $documents = [
        ['key' => 'picture', 'name' => '2x2 Picture', 'note' => __('White background, taken within the last 6 months.'), 'file' => '2x2-picture.jpg'],
        ['key' => 'registration', 'name' => 'Certificate of Registration', 'note' => __('This semester’s COR from the Registrar.'), 'file' => 'certificate-of-registration.pdf'],
    ];
    $notSet = __('Not set');
    $details = [
        ['key' => 'first_name', 'label' => __('First Name'), 'value' => $student->first_name ?? $notSet],
        ['key' => 'middle_name', 'label' => __('Middle Name'), 'value' => $student->middle_name ?: '—'],
        ['key' => 'last_name', 'label' => __('Last Name'), 'value' => $student->last_name ?? $notSet],
        ['key' => 'age', 'label' => __('Age'), 'value' => $student->age ?? $notSet],
        ['key' => 'email', 'label' => __('Email'), 'value' => $student->email],
        ['key' => 'student_number', 'label' => __('Student Number'), 'value' => $student->student_number ?? $notSet],
        ['key' => 'course', 'label' => __('Course'), 'value' => $student->course ?? $notSet],
        ['key' => 'year_level', 'label' => __('Year Level'), 'value' => $student->year_level ?? $notSet],
        ['key' => 'contact_number', 'label' => __('Contact Number'), 'value' => $student->contact_number ?? $notSet],
        ['key' => 'address', 'label' => __('Address'), 'value' => $student->address ?? $notSet],
    ];
    $filledDetails = collect($details)->mapWithKeys(fn (array $detail): array => [$detail['key'] => (string) $detail['value']])->all();
    $application = ['title' => $title, 'submitted' => __('Submitted :date', ['date' => now()->format('M j, Y')]), 'status' => 'pending'];
    $activeRow = ['title' => $title, 'applied' => __('Applied :date', ['date' => now()->format('M j, Y')])];
    $limit = \App\Models\User::SCHOLARSHIP_LIMIT;

    $base = [
        'badges' => $badges,
        'labels' => $labels,
        'clock' => null,
        'summary' => $welcome.' '.e(__("You haven't applied to any scholarships yet.")),
        'applications' => [],
        'active' => [],
        'places' => $limit,
        'open' => $openRows,
        'details' => array_fill_keys(array_keys($filledDetails), ''),
        'files' => ['picture' => '', 'registration' => ''],
        'submitted' => false,
    ];

    $applied = [
        'summary' => $welcome.' '.$count(1).' '.e(__('application is waiting for a decision.')),
        'applications' => [$application],
        'places' => $limit - 1,
        'open' => [$openRows[1]],
    ];

    $decided = [
        'clock' => __('Two weeks later'),
        'summary' => $welcome.' '.e(__('Every application you sent has been decided.')),
        'applications' => [array_merge($application, ['status' => 'approved'])],
        'active' => [$activeRow],
        'places' => $limit - 1,
        'open' => [$openRows[1]],
    ];

    $steps = [
        [
            'screen' => 'dashboard',
            'targets' => [],
            'location' => null,
            'title' => __('Welcome to the scholarship portal'),
            'body' => __('This practice tour applies to a sample scholarship and follows it to a decision, on screens like the real ones. Nothing here is saved or sent to OSAS.'),
            'state' => [],
            'actions' => [],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['tour-open'],
            'location' => ['target' => 'nav-dashboard', 'trail' => [__('Dashboard'), __('Available to apply')]],
            'title' => __('Find a scholarship'),
            'body' => __('Available to apply on your Dashboard lists the scholarships you can still apply to, with their deadline and slots left. You can hold up to :limit scholarships at a time, counting applications waiting for a decision. Scholarships in the menu shows the full lists.', ['limit' => $limit]),
            'state' => [],
            'actions' => [
                ['wait', 300],
                ['press', 'open-sample'],
            ],
        ],
        [
            'screen' => 'apply',
            'targets' => ['tour-details'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), $title, __('Apply Now')]],
            'title' => __('Your details are filled in for you'),
            'body' => __('When you apply, the form takes your name, student number, course and contact details from the account you signed in with. There is nothing to type. If something is wrong, correct it in your profile before applying.'),
            'state' => [],
            'actions' => collect($filledDetails)
                ->flatMap(fn (string $value, string $key): array => [['set', 'details.'.$key, $value], ['wait', 120]])
                ->values()
                ->all(),
        ],
        [
            'screen' => 'apply',
            'targets' => ['tour-documents'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), $title, __('Apply Now')]],
            'title' => __('Upload your documents'),
            'body' => __('That leaves one job: upload each document the scholarship asks for, then submit. Where OSAS posted a sample of a document, it appears beside its upload field.'),
            'state' => ['details' => $filledDetails],
            'actions' => [
                ['wait', 300],
                ['press', 'choose-picture'],
                ['set', 'files.picture', $documents[0]['file']],
                ['wait', 500],
                ['press', 'choose-registration'],
                ['set', 'files.registration', $documents[1]['file']],
                ['wait', 600],
                ['press', 'submit'],
                ['set', 'submitted', true],
            ],
        ],
        [
            'screen' => 'applications',
            'targets' => ['tour-applications'],
            'location' => ['target' => 'nav-applications', 'trail' => [__('My Applications')]],
            'title' => __('Follow your application'),
            'body' => __('Your application stays Pending while OSAS reviews it, then changes to Approved or Rejected. An approved scholarship shows under Active scholarships on your Dashboard until OSAS ends it. Press Skip ahead to see the decision arrive.'),
            'state' => $applied,
            'actions' => [
                ['pause', __('Skip ahead two weeks')],
                ['set', 'clock', __('Two weeks later')],
                ['wait', 500],
                ['set', 'applications.0.status', 'approved'],
            ],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['nav-profile'],
            'location' => ['target' => 'nav-profile', 'trail' => [__('Profile')]],
            'title' => __('Keep your profile current'),
            'body' => __('Your applications use the name, course and year level on your profile, so check them before you apply.'),
            'state' => $decided,
            'actions' => [],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['nav-tutorial'],
            'location' => ['target' => 'nav-tutorial', 'trail' => [__('Tutorial')]],
            'title' => __('Replay this tour'),
            'body' => __('Open Tutorial in the menu whenever you want to run this practice again.'),
            'state' => $decided,
            'actions' => [],
        ],
    ];
@endphp

<div x-data="tutorialStage(@js(['base' => $base]))"
     @tutorial-step.window="perform($event.detail)"
     @tutorial-resume.window="proceed()"
     role="region"
     aria-label="{{ __('Sample screens') }}">

    {{-- Mirrors the student dashboard. --}}
    <div :class="{ 'hidden': screen !== 'dashboard' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Dashboard') }}</h1>
                <x-badge ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />
            </div>
            <p class="mt-2 text-base text-gray-600" x-html="state.summary"></p>

            <section class="mt-10">
                <h2 class="text-base font-semibold text-gray-900">{{ __('Active scholarships') }}</h2>

                <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                    <x-free-tertiary-education :linked="false" />

                    <template x-for="row in state.active" :key="row.title">
                        <li class="flex items-center gap-4 px-3 py-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere]" x-text="row.title"></p>
                                <p class="mt-1 text-sm text-gray-500" x-text="row.applied"></p>
                            </div>
                            <x-badge class="shrink-0" color="green">{{ __('Active') }}</x-badge>
                            <svg class="h-5 w-5 shrink-0 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                            </svg>
                        </li>
                    </template>
                </ul>
            </section>

            <section class="mt-10" data-tour="tour-open">
                <h2 class="text-base font-semibold text-gray-900">{{ __('Available to apply') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('You can hold up to :limit scholarships at a time, counting applications waiting for a decision.', ['limit' => $limit]) }}
                    <span class="font-semibold tabular-nums text-gray-900" x-text="@js(__('You can apply to :count more.')).replace(':count', state.places)"></span>
                </p>

                <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                    <template x-for="row in state.open" :key="row.key">
                        <li class="flex items-center gap-4 px-3 py-4 transition duration-150 ease-in-out"
                            :class="pressed === 'open-' + row.key && 'bg-gray-50'">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold [overflow-wrap:anywhere]"
                                   :class="pressed === 'open-' + row.key ? 'text-cvsu-green' : 'text-gray-900'"
                                   x-text="row.title"></p>
                                <p class="mt-1 text-sm tabular-nums text-gray-500" x-text="row.meta"></p>
                            </div>
                            <span class="hidden shrink-0 text-sm font-semibold text-cvsu-green sm:inline">{{ __('Apply') }} &rarr;</span>
                        </li>
                    </template>
                </ul>
            </section>

            <section :class="{ 'hidden': state.applications.length === 0 }" class="mt-10">
                <h2 class="text-base font-semibold text-gray-900">{{ __('My applications') }}</h2>

                <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                    <template x-for="application in state.applications" :key="application.title">
                        <li class="flex items-center gap-4 px-3 py-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere]" x-text="application.title"></p>
                                <p class="mt-1 text-sm text-gray-500" x-text="application.submitted"></p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors duration-150 ease-in-out"
                                  :class="state.badges[application.status]"
                                  x-text="state.labels[application.status]"></span>
                            <svg class="h-5 w-5 shrink-0 text-gray-500" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8 5 5 5-5 5" />
                            </svg>
                        </li>
                    </template>
                </ul>
            </section>
        </div>
    </div>

    {{-- Mirrors the My Applications page. --}}
    <div :class="{ 'hidden': screen !== 'applications' }" x-cloak class="min-h-screen bg-gray-100">
        <div class="flex flex-wrap items-center gap-3 border-b border-gray-200 bg-white px-6 py-5 lg:px-10">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('My Applications') }}</h2>
            <x-badge ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm" data-tour="tour-applications">
                    <ul class="divide-y divide-gray-100">
                        <template x-for="application in state.applications" :key="application.title">
                            <li class="flex items-center justify-between gap-4 p-6">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere]" x-text="application.title"></p>
                                    <p class="text-sm text-gray-500" x-text="application.submitted"></p>
                                </div>
                                <div class="flex shrink-0 items-center gap-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors duration-150 ease-in-out"
                                          :class="state.badges[application.status]"
                                          x-text="state.labels[application.status]"></span>
                                    <span class="text-sm font-medium text-cvsu-green">{{ __('View') }}</span>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Mirrors the application form. --}}
    <div :class="{ 'hidden': screen !== 'apply' }" x-cloak class="min-h-screen bg-gray-100">
        <div class="border-b border-gray-200 bg-white px-6 py-5 lg:px-10">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ __('Apply for :title', ['title' => $title]) }}</h2>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                <div :class="{ 'hidden': ! state.submitted }" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ __('Application submitted.') }}
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 p-6" data-tour="tour-details">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Your Information') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('Pulled automatically from your account so it cannot be altered. Need a correction? Update your') }}
                            <span class="font-medium text-cvsu-green">{{ __('profile') }}</span>
                            {{ __('first.') }}
                        </p>

                        <dl class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                            @foreach ($details as $detail)
                                <div @class(['col-span-2' => $detail['key'] === 'address'])>
                                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $detail['label'] }}</dt>
                                    <dd class="min-h-[1.25rem] text-sm text-gray-900 [overflow-wrap:anywhere]" x-text="state.details[@js($detail['key'])]"></dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    <div class="space-y-6 p-6" data-tour="tour-documents">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ __('Required Documents') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Upload each document below. Accepted formats: JPG, PNG, PDF (max 5MB each).') }}</p>
                        </div>

                        <div class="space-y-4">
                            @foreach ($documents as $document)
                                <div>
                                    <p class="block text-sm font-medium text-gray-700">{{ $document['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $document['note'] }}</p>
                                    <span class="mt-1 flex min-w-0 items-center gap-4 text-sm text-gray-600">
                                        <span class="shrink-0 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"
                                              :class="pressed === @js('choose-'.$document['key']) && 'bg-gray-50 ring-2 ring-cvsu-green ring-offset-2'">{{ __('Choose file') }}</span>
                                        <span class="truncate" x-text="state.files[@js($document['key'])] || @js(__('No file chosen'))"></span>
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-6">
                            <span class="text-sm font-medium text-gray-500">{{ __('Cancel') }}</span>
                            <span class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out"
                                  :class="pressed === 'submit' ? 'bg-cvsu-green-dark ring-2 ring-cvsu-gold ring-offset-2' : 'bg-cvsu-green'">
                                {{ __('Submit Application') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-tutorial :steps="$steps" />
