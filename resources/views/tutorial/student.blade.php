@php
    $student = Auth::user();
    $title = 'DOST-SEI Merit Scholarship';
    $government = \App\Enums\ScholarshipType::Government->label();
    $badges = [
        'pending' => 'bg-gold-wash text-ink',
        'approved' => 'bg-grant-wash text-grant-deep',
        'rejected' => 'bg-oxide-wash text-oxide-deep',
    ];
    $labels = collect(\App\Enums\ApplicationStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
    $count = fn (int $number): string => '<span class="font-semibold tabular-nums text-ink">'.$number.'</span>';
    $openRows = [
        ['key' => 'sample', 'title' => $title, 'type' => $government, 'closes' => now()->addDays(9)->format('M j, Y'), 'left' => __('Closes in :count days', ['count' => 9]), 'documents' => trans_choice('{0} No documents|{1} :count document|[2,*] :count documents', 2, ['count' => 2])],
        ['key' => 'other', 'title' => 'CHED Tulong Dunong Program', 'type' => $government, 'closes' => now()->addDays(23)->format('M j, Y'), 'left' => __('Closes in :count days', ['count' => 23]), 'documents' => trans_choice('{0} No documents|{1} :count document|[2,*] :count documents', 3, ['count' => 3])],
    ];
    $documents = [
        ['key' => 'picture', 'name' => '2x2 Picture', 'note' => __('White background, taken within the last 6 months.'), 'file' => '2x2-picture.jpg'],
        ['key' => 'registration', 'name' => 'Certificate of Registration', 'note' => __('This semester’s COR from the Registrar.'), 'file' => 'certificate-of-registration.pdf'],
    ];
    $notSet = __('Not set');
    $details = [
        ['key' => 'last_name', 'label' => __('Last Name'), 'value' => $student->last_name !== null ? mb_strtoupper($student->last_name) : $notSet],
        ['key' => 'first_name', 'label' => __('First Name'), 'value' => $student->first_name !== null ? mb_strtoupper($student->first_name) : $notSet],
        ['key' => 'middle_name', 'label' => __('Middle Name'), 'value' => $student->middle_name ? mb_strtoupper($student->middle_name) : '—'],
        ['key' => 'age', 'label' => __('Age'), 'value' => $student->age ?? $notSet],
        ['key' => 'email', 'label' => __('Email'), 'value' => $student->email],
        ['key' => 'student_number', 'label' => __('Student Number'), 'value' => $student->student_number ?? $notSet],
        ['key' => 'course', 'label' => __('Course'), 'value' => $student->course ?? $notSet],
        ['key' => 'year_level', 'label' => __('Year Level'), 'value' => $student->year_level ?? $notSet],
        ['key' => 'contact_number', 'label' => __('Contact Number'), 'value' => $student->contact_number ?? $notSet],
        ['key' => 'address', 'label' => __('Address'), 'value' => $student->address ?? $notSet],
    ];
    $filledDetails = collect($details)->mapWithKeys(fn (array $detail): array => [$detail['key'] => (string) $detail['value']])->all();
    $reference = now()->format('Y').'-00042';
    $application = [
        'title' => $title,
        'type' => $government,
        'scholarshipStatus' => \App\Enums\ScholarshipStatus::Open->label(),
        'reference' => $reference,
        'applied' => now()->format('M j, Y'),
        'waiting' => trans_choice(':count day waiting|:count days waiting', 0, ['count' => 0]),
        'status' => 'pending',
    ];
    $activeRow = ['title' => $title, 'reference' => $reference];
    $limit = \App\Models\User::SCHOLARSHIP_LIMIT;
    $openSentence = fn (int $number): string => $count($number).' '.e(trans_choice('scholarship is open to you.|scholarships are open to you.', $number));
    $caughtUp = e(__("You're all caught up."));

    $base = [
        'badges' => $badges,
        'labels' => $labels,
        'clock' => null,
        'summary' => $openSentence(2),
        'appsSummary' => e(__('Your applications appear here once you apply.')),
        'unread' => 2,
        'canApply' => true,
        'applications' => [],
        'active' => [],
        'open' => $openRows,
        'details' => array_fill_keys(array_keys($filledDetails), ''),
        'files' => ['picture' => '', 'registration' => ''],
        'certified' => false,
        'submitted' => false,
    ];

    $applied = [
        'summary' => $count(1).' '.e(__('application is waiting for a decision.')).' '.$openSentence(1),
        'appsSummary' => $count(1).' '.e(__('application is waiting for a decision.')),
        'applications' => [$application],
        'open' => [$openRows[1]],
    ];

    $decided = [
        'clock' => __('Two weeks later'),
        'summary' => $caughtUp,
        'appsSummary' => $caughtUp,
        'canApply' => false,
        'applications' => [array_merge($application, ['status' => 'approved'])],
        'active' => [$activeRow],
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
            'targets' => ['tour-bell'],
            'location' => ['target' => 'nav-dashboard', 'trail' => [__('Dashboard')]],
            'title' => __('New scholarships arrive in your bell'),
            'body' => __('When OSAS posts a scholarship you can apply to, the bell at the top right of your Dashboard counts it. Open the bell and choose one to go straight to that scholarship.'),
            'state' => [],
            'actions' => [
                ['wait', 300],
                ['press', 'bell'],
            ],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['tour-open'],
            'location' => ['target' => 'nav-dashboard', 'trail' => [__('Dashboard'), __('Available to apply')]],
            'title' => __('Find a scholarship'),
            'body' => __('Available to apply on your Dashboard shows the scholarships you can still apply to, with when they close and how many documents they need. You can hold up to :limit at a time, counting applications waiting for a decision.', ['limit' => $limit]),
            'state' => [],
            'actions' => [],
        ],
        [
            'screen' => 'scholarships',
            'targets' => ['tour-scholarships'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships')]],
            'title' => __('Every scholarship open to you'),
            'body' => __('Scholarships in the menu lists every scholarship you can apply to, beside where you stand. Open one to read it and apply.'),
            'state' => [],
            'actions' => [
                ['wait', 300],
                ['press', 'open-sample'],
            ],
        ],
        [
            'screen' => 'apply',
            'targets' => ['tour-details'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), $title, __('Apply now')]],
            'title' => __('Your details are filled in for you'),
            'body' => __('When you apply, the form takes your name, student number, course and contact details from the account you signed in with. There is nothing to type. If your name or contact details are wrong, correct them on your profile. Course, year level and student number are kept by OSAS, so email the office about those.'),
            'state' => [],
            'actions' => collect($filledDetails)
                ->flatMap(fn (string $value, string $key): array => [['set', 'details.'.$key, $value], ['wait', 120]])
                ->values()
                ->all(),
        ],
        [
            'screen' => 'apply',
            'targets' => ['tour-documents'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), $title, __('Apply now')]],
            'title' => __('Upload your documents'),
            'body' => __('That leaves one job: upload each document the scholarship asks for. Where OSAS posted a sample of a document, it appears beside its upload field.'),
            'state' => ['details' => $filledDetails],
            'actions' => [
                ['wait', 300],
                ['press', 'choose-picture'],
                ['set', 'files.picture', $documents[0]['file']],
                ['wait', 500],
                ['press', 'choose-registration'],
                ['set', 'files.registration', $documents[1]['file']],
            ],
        ],
        [
            'screen' => 'apply',
            'targets' => ['tour-certification'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), $title, __('Apply now')]],
            'title' => __('Certify and submit'),
            'body' => __('Before submitting, tick the box to certify that your documents are true and authentic. A false or altered document voids the application.'),
            'state' => ['details' => $filledDetails, 'files' => collect($documents)->mapWithKeys(fn (array $document): array => [$document['key'] => $document['file']])->all()],
            'actions' => [
                ['wait', 300],
                ['press', 'certify'],
                ['set', 'certified', true],
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
            'body' => __('Each application you send is a card on My Applications, with its reference number and how long it has waited. It stays Pending while OSAS reviews it, then changes to Approved or Rejected. Press Skip ahead to see the decision arrive.'),
            'state' => $applied,
            'actions' => [
                ['pause', __('Skip ahead two weeks')],
                ['set', 'clock', __('Two weeks later')],
                ['wait', 500],
                ['set', 'applications.0.status', 'approved'],
                ['set', 'appsSummary', $caughtUp],
            ],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['tour-standing'],
            'location' => ['target' => 'nav-dashboard', 'trail' => [__('Dashboard'), __('Where you stand')]],
            'title' => __('Your scholarship is active'),
            'body' => __('An approved scholarship shows in Where you stand on your Dashboard until OSAS ends it. While you hold one, Available to apply says so, and you can apply again once it ends.'),
            'state' => $decided,
            'actions' => [],
        ],
        [
            'screen' => 'profile',
            'targets' => ['tour-profile'],
            'location' => ['target' => 'nav-profile', 'trail' => [__('Profile')]],
            'title' => __('Keep your profile current'),
            'body' => __('Your profile holds the details your applications use. You can change your name, date of birth, contact number and address there, all with one Save; OSAS keeps your course, year level and student number.'),
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

    {{--
        Mirrors the student dashboard: the welcome banner with its bell, then
        the Where you stand and Available to apply panes. As on the real page,
        the panes sit side by side only while there is something to apply to,
        and stack once the student holds an active scholarship.
    --}}
    <div :class="{ 'hidden': screen !== 'dashboard' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Welcome back, :name!', ['name' => mb_strtoupper($student->surname_first_name)])">
                <span x-html="state.summary"></span>
                <x-badge class="ms-1" ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />

                <x-slot:corner>
                    <span data-tour="tour-bell"
                          class="relative flex h-10 w-10 items-center justify-center rounded-full text-seal transition duration-150 ease-in-out"
                          :class="pressed === 'bell' && 'bg-seal-tint/60 ring-2 ring-seal ring-offset-2 ring-offset-seal-wash'">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span class="sr-only">{{ __('Notifications') }}</span>
                        <span :class="{ 'hidden': ! state.unread }" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-gold px-1 text-annot font-semibold tabular-nums text-ink ring-2 ring-seal-wash" x-text="state.unread"></span>
                    </span>
                </x-slot:corner>
            </x-welcome-banner>

            <div class="mt-6 grid gap-6" :class="state.canApply && 'lg:grid-cols-[22rem_minmax(0,1fr)] lg:grid-rows-[auto_1fr] lg:items-start'">
                <section data-tour="tour-standing" :class="state.canApply && 'lg:col-start-1 lg:row-start-1'">
                    <h2 class="text-lead font-semibold text-ink">{{ __('Where you stand') }}</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2" :class="state.canApply ? 'lg:grid-cols-1' : 'xl:grid-flow-col xl:grid-cols-none xl:auto-cols-fr'">
                        <div class="border border-rule bg-white shadow-seat sm:col-span-2" :class="state.canApply ? 'lg:col-span-1' : 'xl:col-span-1'">
                            <div class="px-5 py-4">
                                <h3 class="font-semibold uppercase text-ink">{{ $student->surname_first_name }}</h3>
                                <p class="mt-0.5 text-slate">{{ $student->course ?? __('Course not set') }}</p>
                            </div>
                            <dl class="grid grid-cols-2 border-t border-rule">
                                <div class="px-5 py-2.5">
                                    <dt class="font-annot text-annot uppercase text-slate">{{ __('Student number') }}</dt>
                                    <dd class="font-mono text-ink">{{ $student->student_number ?? '—' }}</dd>
                                </div>
                                <div class="border-l border-rule px-5 py-2.5">
                                    <dt class="font-annot text-annot uppercase text-slate">{{ __('Year level') }}</dt>
                                    <dd class="text-ink">{{ $student->year_level ? __('Year :level', ['level' => $student->year_level]) : '—' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <x-free-tertiary-education />

                        <template x-for="row in state.active" :key="row.title">
                            <div class="border border-rule bg-white px-5 py-4 shadow-seat">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-semibold text-ink [overflow-wrap:anywhere]" x-text="row.title"></h3>
                                    <x-badge class="shrink-0" color="green">{{ __('Active') }}</x-badge>
                                </div>
                                <p class="mt-1 text-slate">{{ __('Ref') }} <span class="font-mono" x-text="row.reference"></span></p>
                            </div>
                        </template>

                        <div :class="{ 'hidden': state.active.length > 0 }" class="border border-rule bg-white px-5 py-4 shadow-seat">
                            <h3 class="font-semibold text-ink">{{ __('Active scholarship') }}</h3>
                            <p class="mt-0.5 text-slate">{{ __('None yet.') }}</p>
                        </div>
                    </div>
                </section>

                <section data-tour="tour-open" :class="state.canApply && 'lg:col-start-2 lg:row-span-2 lg:row-start-1'">
                    <h2 class="text-lead font-semibold text-ink">
                        {{ __('Available to apply') }}
                        <span :class="{ 'hidden': ! state.canApply }" class="font-normal text-slate">(<span x-text="state.open.length"></span>)</span>
                    </h2>

                    <p :class="{ 'hidden': state.canApply }" class="mt-4 border border-rule bg-white px-5 py-8 text-center text-slate shadow-seat">
                        {{ __('You hold an active scholarship. Apply again once it ends.') }}
                    </p>

                    <ul :class="{ 'hidden': ! state.canApply }" class="mt-4 grid gap-4 sm:grid-cols-2" role="list">
                        <template x-for="row in state.open" :key="row.key">
                            <li class="flex">
                                <div class="flex w-full flex-col border bg-white shadow-seat transition duration-150 ease-in-out"
                                     :class="pressed === 'open-' + row.key ? 'border-seal' : 'border-rule'">
                                    <div class="flex-1 px-5 py-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <h3 class="font-semibold [overflow-wrap:anywhere]" :class="pressed === 'open-' + row.key ? 'text-seal' : 'text-ink'" x-text="row.title"></h3>
                                            <span class="inline-flex shrink-0 items-center rounded-sheet bg-linen-sunk px-2 py-0.5 font-annot text-annot uppercase text-graphite" x-text="row.type"></span>
                                        </div>
                                    </div>

                                    <dl class="border-t border-rule">
                                        <div class="px-5 py-2.5">
                                            <dt class="font-annot text-annot uppercase text-slate">{{ __('Closes') }}</dt>
                                            <dd class="text-ink">
                                                <span x-text="row.closes"></span>
                                                <span class="block text-slate" x-text="row.left"></span>
                                            </dd>
                                        </div>
                                    </dl>

                                    <div class="flex items-center justify-between gap-3 border-t border-rule px-5 py-2.5">
                                        <span class="text-slate" x-text="row.documents"></span>
                                        <span class="rounded-sheet px-4 py-1.5 font-semibold text-white transition duration-150 ease-in-out"
                                              :class="pressed === 'open-' + row.key ? 'bg-seal-deep' : 'bg-seal'">{{ __('Apply') }}</span>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </section>

                <section :class="{ 'hidden': state.applications.filter((application) => application.status !== 'approved').length === 0, 'lg:col-start-1 lg:row-start-2': state.canApply }">
                    <h2 class="text-lead font-semibold text-ink">{{ __('My applications') }}</h2>

                    <ul class="mt-3 grid gap-3 sm:grid-cols-2" :class="state.canApply ? 'lg:grid-cols-1' : 'xl:grid-cols-3'" role="list">
                        <template x-for="application in state.applications.filter((application) => application.status !== 'approved')" :key="application.title">
                            <li>
                                <div class="block border px-5 py-3.5 shadow-seat"
                                     :class="application.status === 'pending' ? 'border-gold-deep bg-gold-wash' : 'border-rule bg-white'">
                                    <p class="font-semibold text-ink [overflow-wrap:anywhere]" x-text="application.title"></p>
                                    <div class="mt-1.5 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                        <p class="text-ink">
                                            <span class="font-mono" x-text="application.reference"></span>
                                            <span :class="{ 'hidden': application.status !== 'pending' }">&middot; <span x-text="application.waiting"></span></span>
                                        </p>
                                        <span class="inline-flex shrink-0 items-center rounded-sheet px-2 py-0.5 font-annot text-annot uppercase"
                                              :class="application.status === 'pending' ? 'bg-white text-ink' : state.badges[application.status]"
                                              x-text="state.labels[application.status]"></span>
                                    </div>
                                </div>
                            </li>
                        </template>
                    </ul>
                </section>
            </div>
        </div>
    </div>

    {{-- Mirrors the My Applications page: the banner, then one card per application. --}}
    <div :class="{ 'hidden': screen !== 'applications' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('My Applications')">
                <span x-html="state.appsSummary"></span>
                <x-badge class="ms-1" ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />
            </x-welcome-banner>

            <ul class="mt-8 grid gap-4 grid-cols-[repeat(auto-fill,minmax(min(20rem,100%),1fr))]" role="list" data-tour="tour-applications">
                <template x-for="application in state.applications" :key="application.title">
                    <li>
                        <div class="flex h-full flex-col rounded-sheet border border-rule bg-white p-5">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 text-lead font-semibold leading-snug text-ink [overflow-wrap:anywhere]" x-text="application.title"></p>
                                <span class="inline-flex shrink-0 items-center rounded-sheet px-2 py-0.5 font-annot text-annot uppercase transition-colors duration-150 ease-in-out"
                                      :class="state.badges[application.status]"
                                      x-text="state.labels[application.status]"></span>
                            </div>
                            <p class="mt-1 text-slate">
                                <span x-text="application.type"></span> <span aria-hidden="true">&middot;</span> <span x-text="application.scholarshipStatus"></span>
                            </p>

                            <div class="mt-auto pt-5">
                                <dl class="grid grid-cols-2 gap-3 border-t border-rule pt-4">
                                    <div class="min-w-0">
                                        <dt class="text-slate">{{ __('Reference') }}</dt>
                                        <dd class="mt-0.5 font-mono text-lead text-ink" x-text="application.reference"></dd>
                                    </div>
                                    <div class="min-w-0">
                                        <dt class="text-slate">{{ __('Applied') }}</dt>
                                        <dd class="mt-0.5 text-lead font-semibold text-ink" x-text="application.applied"></dd>
                                    </div>
                                </dl>

                                <p :class="{ 'hidden': application.status !== 'pending' }" class="mt-4 font-semibold tabular-nums text-ink" x-text="application.waiting"></p>
                            </div>

                            <span class="mt-5 font-semibold text-seal">{{ __('View application') }} &rarr;</span>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    {{-- Mirrors the Scholarships page: the banner, then where the student stands beside every scholarship open to them. --}}
    <div :class="{ 'hidden': screen !== 'scholarships' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Scholarships')">
                <span class="font-semibold tabular-nums text-ink" x-text="state.open.length"></span>
                <span x-text="state.open.length === 1 ? @js(__('scholarship is open to you.')) : @js(__('scholarships are open to you.'))"></span>
            </x-welcome-banner>

            <div class="mt-6 grid gap-6 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start">
                <section>
                    <h2 class="text-lead font-semibold text-ink">{{ __('Where you stand') }}</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <x-free-tertiary-education />

                        <div class="border border-rule bg-white px-5 py-4 shadow-seat">
                            <h3 class="font-semibold text-ink">{{ __('Active scholarship') }}</h3>
                            <p class="mt-0.5 text-slate">{{ __('None yet.') }}</p>
                        </div>
                    </div>
                </section>

                <div data-tour="tour-scholarships">
                    <section>
                        <h2 class="text-lead font-semibold text-ink">
                            {{ __('Available to apply') }}
                            <span :class="{ 'hidden': ! state.canApply }" class="font-normal text-slate">(<span x-text="state.open.length"></span>)</span>
                        </h2>

                        <p :class="{ 'hidden': state.canApply }" class="mt-4 border border-rule bg-white px-5 py-8 text-center text-slate shadow-seat">
                            {{ __('You hold an active scholarship. Apply again once it ends.') }}
                        </p>

                        <ul :class="{ 'hidden': ! state.canApply }" class="mt-4 grid gap-4 sm:grid-cols-2" role="list">
                            <template x-for="row in state.open" :key="row.key">
                                <li class="flex">
                                    <div class="flex w-full flex-col border bg-white shadow-seat transition duration-150 ease-in-out"
                                         :class="pressed === 'open-' + row.key ? 'border-seal' : 'border-rule'">
                                        <div class="flex-1 px-5 py-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <h3 class="font-semibold [overflow-wrap:anywhere]" :class="pressed === 'open-' + row.key ? 'text-seal' : 'text-ink'" x-text="row.title"></h3>
                                                <span class="inline-flex shrink-0 items-center rounded-sheet bg-linen-sunk px-2 py-0.5 font-annot text-annot uppercase text-graphite" x-text="row.type"></span>
                                            </div>
                                        </div>

                                        <dl class="border-t border-rule">
                                            <div class="px-5 py-2.5">
                                                <dt class="font-annot text-annot uppercase text-slate">{{ __('Closes') }}</dt>
                                                <dd class="text-ink">
                                                    <span x-text="row.closes"></span>
                                                    <span class="block text-slate" x-text="row.left"></span>
                                                </dd>
                                            </div>
                                        </dl>

                                        <div class="flex items-center justify-between gap-3 border-t border-rule px-5 py-2.5">
                                            <span class="text-slate" x-text="row.documents"></span>
                                            <span class="rounded-sheet px-4 py-1.5 font-semibold text-white transition duration-150 ease-in-out"
                                                  :class="pressed === 'open-' + row.key ? 'bg-seal-deep' : 'bg-seal'">{{ __('Apply') }}</span>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </section>

                </div>
            </div>
        </div>
    </div>

    {{-- Mirrors the student profile: one card of rows with one Save. --}}
    <div :class="{ 'hidden': screen !== 'profile' }" x-cloak class="px-6 py-6 lg:px-10">
        <div class="mx-auto max-w-[88rem]">
            <h1 class="text-lead font-semibold text-ink">{{ __('Profile') }}</h1>

            <div class="mt-4 rounded-lg border border-rule bg-white" data-tour="tour-profile">
                @foreach ([
                    [__('Profile information'), __('Your name and the Google account you sign in with.'), [
                        [__('Last Name'), $student->last_name ?? '', '', false, __('Include the full surname, for example "Dela Cruz".')],
                        [__('First Name'), $student->first_name ?? '', '', false, ''],
                        [__('Middle Name'), $student->middle_name ?? '', '', false, __('Optional. Leave blank if you do not use one.')],
                        [__('Email'), $student->email, 'sm:col-span-2', true, __('You sign in with this Google account. Contact OSAS if it needs to change.')],
                    ]],
                    [__('Personal details'), __('Only you can see and change your date of birth, contact number and address. OSAS staff cannot.'), [
                        [__('Date of Birth'), $student->date_of_birth?->format('M j, Y') ?? '', '', false, ''],
                        [__('Contact Number'), $student->contact_number ?? '', '', false, ''],
                        [__('Address'), $student->address ?? '', '', false, ''],
                    ]],
                    [__('Enrollment'), __('Managed by OSAS. Contact the office if this is incorrect.'), [
                        [__('Student Number'), $student->student_number ?? $notSet, '', true, ''],
                        [__('Year Level'), $student->year_level ?? $notSet, '', true, ''],
                        [__('Course'), $student->course ?? $notSet, '', true, ''],
                    ]],
                ] as [$section, $hint, $fields])
                    <section class="grid gap-x-8 gap-y-3 p-5 lg:grid-cols-[14rem_1fr] {{ $loop->first ? '' : 'border-t border-rule' }}">
                        <header>
                            <h2 class="font-semibold text-ink">{{ $section }}</h2>
                            <p class="mt-0.5 text-slate">{{ $hint }}</p>
                        </header>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach ($fields as [$label, $value, $span, $locked, $note])
                                <div class="{{ $span }}">
                                    <span class="block font-annot text-annot uppercase text-graphite">{{ $label }}</span>
                                    <span class="mt-1 block min-h-[2.625rem] rounded-sheet border border-rule-strong px-3 py-2 {{ $locked ? 'bg-linen-sunk text-slate' : 'bg-white text-ink' }}">{{ $value }}</span>
                                    @if ($note !== '')
                                        <p class="mt-1 text-slate">{{ $note }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="flex items-center justify-end gap-4 border-t border-rule px-5 py-3">
                    <span class="inline-flex items-center rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm">{{ __('Save') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Mirrors the application form: the documents to upload, the details OSAS will see, then the certification. --}}
    <div :class="{ 'hidden': screen !== 'apply' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <span class="font-medium text-graphite underline underline-offset-4">{{ __('Back to scholarship') }}</span>

            <h1 class="mt-6 text-lead font-semibold leading-tight text-ink [overflow-wrap:anywhere]">{{ __('Apply for :title', ['title' => $title]) }}</h1>

            <div :class="{ 'hidden': ! state.submitted }" class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3  text-grant-deep">
                {{ __('Application submitted.') }}
            </div>

            <div class="mt-10 space-y-12">
                <section data-tour="tour-documents">
                    <h2 class="text-lead font-semibold text-ink">{{ __('Required Documents') }}</h2>
                    <p class="mt-1  text-slate">{{ __('Upload each document below. Accepted formats: JPG, PNG, PDF (max 5MB each).') }}</p>

                    <ul class="mt-3 divide-y divide-rule border-y border-rule">
                        @foreach ($documents as $document)
                            <li class="py-5">
                                <p class="block  font-medium text-graphite">{{ $document['name'] }}</p>
                                <p class="mt-1  text-slate">{{ $document['note'] }}</p>
                                <span class="mt-3 flex min-w-0 items-center gap-4  text-graphite">
                                    <span class="shrink-0 rounded-sheet border border-rule-strong bg-white px-4 py-2  font-semibold text-graphite"
                                          :class="pressed === @js('choose-'.$document['key']) && 'bg-linen-sunk ring-2 ring-seal ring-offset-2'">{{ __('Choose file') }}</span>
                                    <span class="truncate" x-text="state.files[@js($document['key'])] || @js(__('No file chosen'))"></span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section data-tour="tour-details">
                    <h2 class="text-lead font-semibold text-ink">{{ __('Your Information') }}</h2>
                    <p class="mt-1 max-w-prose  text-slate">
                        {{ __('Pulled automatically from your account so it cannot be altered. Need a correction? Update your') }}
                        <span class="font-medium text-seal">{{ __('profile') }}</span>
                        {{ __('first.') }}
                    </p>

                    <dl class="mt-3 grid border-t border-rule  sm:grid-cols-2 sm:gap-x-8">
                        @foreach ($details as $detail)
                            <div class="grid grid-cols-[8rem_minmax(0,1fr)] gap-3 border-b border-rule py-2.5">
                                <dt class="text-slate">{{ $detail['label'] }}</dt>
                                <dd class="min-h-[1.25rem] text-ink [overflow-wrap:anywhere]" x-text="state.details[@js($detail['key'])]"></dd>
                            </div>
                        @endforeach
                    </dl>
                </section>

                <div data-tour="tour-certification" class="space-y-12">
                    <section>
                        <h2 class="text-lead font-semibold text-ink">{{ __('Certification') }}</h2>

                        <span class="mt-3 flex items-start gap-3 border-y border-rule py-4">
                            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border transition duration-150 ease-in-out"
                                  :class="{ 'border-seal bg-seal': state.certified, 'border-rule-strong bg-white': ! state.certified, 'ring-2 ring-seal ring-offset-2': pressed === 'certify' }">
                                <svg :class="{ 'hidden': ! state.certified }" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 10.5 3.5 3.5 7.5-8" />
                                </svg>
                            </span>
                            <span class="max-w-prose  leading-relaxed text-graphite">
                                {{ __('I hereby certify that all documents submitted with this application are true, correct, and authentic, and have not been altered in any way. I understand that the submission of any false or falsified document shall render this application null and void.') }}
                            </span>
                        </span>
                    </section>

                    <div class="flex items-center justify-end gap-4">
                        <span class="font-medium text-graphite">{{ __('Cancel') }}</span>
                        <span class="inline-flex items-center justify-center rounded-sheet px-4 py-2.5  font-semibold text-white shadow-sm transition duration-150 ease-in-out"
                              :class="pressed === 'submit' ? 'bg-seal-deep ring-2 ring-gold ring-offset-2' : 'bg-seal'">
                            {{ __('Submit application') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-tutorial :steps="$steps" />
