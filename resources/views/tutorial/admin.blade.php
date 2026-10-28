@php
    $admin = Auth::user();
    $title = 'DOST-SEI Merit Scholarship';
    $deadline = now()->addDays(9);
    $description = 'For second-year science and engineering students with a GWA of 1.75 or better.';
    $government = \App\Enums\ScholarshipType::Government->label();
    $badges = [
        'pending' => 'bg-gold-wash text-ink',
        'approved' => 'bg-grant-wash text-grant-deep',
        'rejected' => 'bg-oxide-wash text-oxide-deep',
    ];
    $labels = collect(\App\Enums\ApplicationStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
    $applicants = [
        ['name' => 'Villanueva, Andrea', 'number' => '202201877', 'course' => 'BS Civil Engineering', 'status' => 'pending'],
        ['name' => 'Mendoza, Carlo', 'number' => '202202310', 'course' => 'BS Computer Science', 'status' => 'pending'],
        ['name' => 'Dela Cruz, Juan', 'number' => '202201234', 'course' => 'BS Information Technology', 'status' => 'pending'],
    ];
    $passListHint = __('Upload a CSV of who passed — a "student_number" column, a "name" column, or both (a row with a student number is matched by that number only; a name is matched only on rows without one). A header-less single column of names also works. Every other pending applicant (:count) will be rejected automatically.');
    $count = fn (int $number): string => '<span class="font-semibold tabular-nums text-ink">'.$number.'</span>';
    $samplePosting = ['key' => 'sample', 'title' => $title, 'description' => $description, 'type' => $government, 'deadline' => $deadline->format('M j, Y'), 'status' => 'open'];
    $otherPosting = ['key' => 'other', 'title' => 'CHED Tulong Dunong Program', 'description' => 'Financial assistance for students in public colleges and universities.', 'type' => $government, 'deadline' => now()->addDays(23)->format('M j, Y'), 'status' => 'open'];
    $takingApplications = fn (int $number): string => e(__('No pass lists are due yet.')).' '.$count($number).' '.e(trans_choice('scholarship with applicants is still taking applications.|scholarships with applicants are still taking applications.', $number));
    $cards = [
        ['key' => 'sample', 'title' => $title, 'type' => $government, 'left' => __(':left of :total', ['left' => 2, 'total' => 2]), 'applicants' => 0, 'closes' => $deadline->format('M j, Y')],
        ['key' => 'other', 'title' => 'CHED Tulong Dunong Program', 'type' => $government, 'left' => __(':left of :total', ['left' => 10, 'total' => 10]), 'applicants' => 5, 'closes' => now()->addDays(23)->format('M j, Y')],
    ];
    $passed = [$applicants[0], $applicants[1]];
    $roster = [
        ['key' => 'juan', 'name' => 'Dela Cruz, Juan', 'email' => 'juan.delacruz@cvsu.edu.ph', 'number' => '202201234', 'course' => 'BS Information Technology', 'year' => __('Year :level', ['level' => 3]), 'loa' => false],
        ['key' => 'bea', 'name' => 'Fernandez, Bea', 'email' => 'bea.fernandez@cvsu.edu.ph', 'number' => '202310045', 'course' => 'BS Hospitality Management', 'year' => __('Year :level', ['level' => 2]), 'loa' => false],
        ['key' => 'mark', 'name' => 'Garcia, Mark', 'email' => 'mark.garcia@cvsu.edu.ph', 'number' => '202110233', 'course' => 'BS Civil Engineering', 'year' => __('Year :level', ['level' => 4]), 'loa' => true],
        ['key' => 'carlo', 'name' => 'Mendoza, Carlo', 'email' => 'carlo.mendoza@cvsu.edu.ph', 'number' => '202202310', 'course' => 'BS Computer Science', 'year' => __('Year :level', ['level' => 3]), 'loa' => false],
        ['key' => 'andrea', 'name' => 'Villanueva, Andrea', 'email' => 'andrea.villanueva@cvsu.edu.ph', 'number' => '202201877', 'course' => 'BS Civil Engineering', 'year' => __('Year :level', ['level' => 3]), 'loa' => false],
    ];
    $active = \App\Enums\StudentStatus::Active->label();
    $onLeave = \App\Enums\StudentStatus::LeaveOfAbsence->label();
    $share = \App\Support\Peso::format(2500000);

    $base = [
        'badges' => $badges,
        'labels' => $labels,
        'clock' => null,
        'summary' => $takingApplications(1),
        'stats' => ['total' => 1, 'open' => 1, 'closed' => 0, 'ended' => 0],
        'postings' => [$otherPosting],
        'cards' => $cards,
        'post' => ['title' => '', 'description' => '', 'slots' => '', 'deadline' => '', 'document' => '', 'hasDocument' => false],
        'applicants' => [],
        'remaining' => 2,
        'passList' => '',
        'results' => '',
        'fund' => ['total' => '', 'saved' => false],
        'search' => '',
        'studentStatus' => $active,
        'studentSaved' => false,
    ];

    $decided = [
        'clock' => __(':count days later', ['count' => 9]),
        'summary' => $takingApplications(1),
        'stats' => ['total' => 2, 'open' => 1, 'closed' => 1, 'ended' => 0],
        'postings' => [array_merge($samplePosting, ['status' => 'closed']), $otherPosting],
    ];

    $steps = [
        [
            'screen' => 'dashboard',
            'targets' => [],
            'location' => null,
            'title' => __('Welcome to the scholarship office'),
            'body' => __('This practice tour runs a sample scholarship from posting to a decision and on to its fund allocation, on screens like the real ones. Nothing here is saved or sent to students.'),
            'state' => [],
            'actions' => [],
        ],
        [
            'screen' => 'post',
            'targets' => ['tour-post'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), __('Post Scholarship')]],
            'title' => __('Post a scholarship'),
            'body' => __('Set the title, slots and deadline, then list the documents students must upload. You can attach a sample of each so students know what a correct one looks like. Your Dashboard has a Post scholarship button too.'),
            'state' => [],
            'actions' => [
                ['type', 'post.title', $title],
                ['type', 'post.description', $description],
                ['type', 'post.slots', '2'],
                ['set', 'post.deadline', $deadline->toDateString()],
                ['wait', 300],
                ['press', 'add-document'],
                ['set', 'post.hasDocument', true],
                ['type', 'post.document', '2x2 Picture'],
                ['press', 'post'],
            ],
        ],
        [
            'screen' => 'scholarships',
            'targets' => ['tour-cards'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships')]],
            'title' => __('Your scholarships'),
            'body' => __('Every scholarship you post is a card on Scholarships, with its slots left, applicants and closing date. Open a card to review it, or press Edit to change it.'),
            'state' => [],
            'actions' => [
                ['wait', 300],
                ['press', 'card-sample'],
            ],
        ],
        [
            'screen' => 'scholarship',
            'targets' => ['tour-applicants'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), $title]],
            'title' => __('Students apply'),
            'body' => __('Students apply from their own portal. Each application arrives here as Pending, with the documents they uploaded.'),
            'state' => [],
            'actions' => [
                ['wait', 300],
                ['push', 'applicants', $applicants[0]],
                ['wait', 700],
                ['push', 'applicants', $applicants[1]],
                ['wait', 700],
                ['push', 'applicants', $applicants[2]],
            ],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['tour-overview'],
            'location' => ['target' => 'nav-dashboard', 'trail' => [__('Dashboard')]],
            'title' => __('See what is ready to decide'),
            'body' => __('Your Dashboard\'s banner counts the scholarships ready for a pass list and the ones still taking applications, above your totals and the newest postings. A scholarship is ready for a pass list once it stops taking applications. Press Skip ahead to jump to this one\'s deadline.'),
            'state' => [
                'summary' => $takingApplications(2),
                'stats' => ['total' => 2, 'open' => 2, 'closed' => 0, 'ended' => 0],
                'postings' => [$samplePosting, $otherPosting],
            ],
            'actions' => [
                ['pause', __('Skip ahead :count days', ['count' => 9])],
                ['set', 'clock', __(':count days later', ['count' => 9])],
                ['wait', 500],
                ['set', 'postings.0.status', 'closed'],
                ['set', 'stats.open', 1],
                ['set', 'stats.closed', 1],
                ['set', 'summary', $count(1).' '.e(__('scholarship is ready for a pass list.')).' '.$count(1).' '.e(__('more is still taking applications.'))],
            ],
        ],
        [
            'screen' => 'scholarship',
            'targets' => ['tour-decide'],
            'location' => ['target' => 'nav-scholarships', 'trail' => [__('Scholarships'), $title, __('Upload Pass List')]],
            'title' => __('Upload the pass list'),
            'body' => __('Upload a CSV of who passed. Matched applicants are approved, each filling one slot, and every other pending applicant is rejected.'),
            'state' => [
                'clock' => __(':count days later', ['count' => 9]),
                'applicants' => $applicants,
            ],
            'actions' => [
                ['wait', 300],
                ['press', 'choose-file'],
                ['set', 'passList', 'pass-list.csv'],
                ['wait', 600],
                ['press', 'apply'],
                ['set', 'results', __(':approved approved, :rejected rejected.', ['approved' => 2, 'rejected' => 1])],
                ['wait', 300],
                ['set', 'applicants.0.status', 'approved'],
                ['set', 'remaining', 1],
                ['wait', 300],
                ['set', 'applicants.1.status', 'approved'],
                ['set', 'remaining', 0],
                ['wait', 300],
                ['set', 'applicants.2.status', 'rejected'],
            ],
        ],
        [
            'screen' => 'fundList',
            'targets' => ['tour-fund-list'],
            'location' => ['target' => 'nav-allocation', 'trail' => [__('Fund Allocation')]],
            'title' => __('Fund Allocation'),
            'body' => __('Students who pass move to Fund Allocation. Its banner counts the scholarships still waiting for their total funds, and each one is a card you open to set them.'),
            'state' => $decided,
            'actions' => [
                ['wait', 300],
                ['press', 'open-fund'],
            ],
        ],
        [
            'screen' => 'fund',
            'targets' => ['tour-fund'],
            'location' => ['target' => 'nav-allocation', 'trail' => [__('Fund Allocation'), $title]],
            'title' => __('Set the total funds'),
            'body' => __('Enter the scholarship\'s total funds and it is split equally among the students who passed. Download Excel sheet gives you the list with each student\'s amount and a column for their signature.'),
            'state' => $decided,
            'actions' => [
                ['type', 'fund.total', '50000'],
                ['wait', 300],
                ['press', 'set-funds'],
                ['set', 'fund.saved', true],
            ],
        ],
        [
            'screen' => 'roster',
            'targets' => ['tour-roster'],
            'location' => ['target' => 'nav-roster', 'trail' => [__('Search Student')]],
            'title' => __('Search Student'),
            'body' => __('Find registered students by name, email or student number. Filter by year level or course, and sort by surname, student number or year level.'),
            'state' => $decided,
            'actions' => [
                ['type', 'search', 'Dela Cruz'],
            ],
        ],
        [
            'screen' => 'students',
            'targets' => ['tour-students'],
            'location' => ['target' => 'nav-students', 'trail' => [__('Edit Student Data')]],
            'title' => __('Edit Student Data'),
            'body' => __('Find the student whose record needs correcting and press Edit.'),
            'state' => $decided,
            'actions' => [
                ['wait', 300],
                ['press', 'edit-juan'],
            ],
        ],
        [
            'screen' => 'student',
            'targets' => ['tour-student'],
            'location' => ['target' => 'nav-students', 'trail' => [__('Edit Student Data'), 'DELA CRUZ, JUAN']],
            'title' => __('Correct a student\'s details'),
            'body' => __('Correct their name, student number, course, year level or program length, or mark them on leave of absence, then save. Their sign-in email and personal details cannot be changed here.'),
            'state' => $decided,
            'actions' => [
                ['wait', 300],
                ['press', 'status'],
                ['set', 'studentStatus', $onLeave],
                ['wait', 500],
                ['press', 'save-student'],
                ['set', 'studentSaved', true],
            ],
        ],
        [
            'screen' => 'profile',
            'targets' => ['tour-profile'],
            'location' => ['target' => 'nav-profile', 'trail' => [__('Profile')]],
            'title' => __('Your profile'),
            'body' => __('Change your name, email and password on Profile, all with one Save. Leave the password fields blank to keep your current password.'),
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

    {{-- Mirrors the admin dashboard: the welcome banner, the three totals, then the newest postings. --}}
    <div :class="{ 'hidden': screen !== 'dashboard' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]" data-tour="tour-overview">
            <x-welcome-banner :heading="__('Welcome back, :name!', ['name' => mb_strtoupper(Auth::user()->surname_first_name)])">
                <span x-html="state.summary"></span>
                <x-badge class="ms-1" ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />

                <x-slot:action>
                    <span class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm sm:self-auto">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M10 4v12M4 10h12" />
                        </svg>
                        {{ __('Post scholarship') }}
                    </span>
                </x-slot:action>
            </x-welcome-banner>

            {{-- The student figures are sample numbers; the scholarship figures follow the tour. --}}
            <dl class="mt-6 grid gap-6 md:grid-cols-3">
                <div class="rounded-lg border border-seal-tint bg-seal-wash p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-seal-tint/60 text-seal" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7l-4-4Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v4h4M9 12h6M9 16h6" />
                        </svg>
                    </span>
                    <dt class="mt-4 font-semibold text-graphite">{{ __('Total scholarships') }}</dt>
                    <dd class="mt-1 text-figure font-semibold tabular-nums text-ink" x-text="state.stats.total"></dd>
                    <dd class="mt-2 text-slate">
                        <span class="tabular-nums" x-text="@js(__(':count open')).replace(':count', state.stats.open)"></span>
                        <span aria-hidden="true">&middot;</span>
                        <span class="tabular-nums" x-text="@js(__(':count closed')).replace(':count', state.stats.closed)"></span>
                        <span aria-hidden="true">&middot;</span>
                        <span class="tabular-nums" x-text="@js(__(':count ended')).replace(':count', state.stats.ended)"></span>
                    </dd>
                </div>

                <div class="rounded-lg border border-blue-200 bg-blue-50 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <circle cx="9" cy="8" r="3" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 19a5.5 5.5 0 0 0-2.5-4.6" />
                        </svg>
                    </span>
                    <dt class="mt-4 font-semibold text-graphite">{{ __('Students with Active status') }}</dt>
                    <dd class="mt-1 text-figure font-semibold tabular-nums text-ink">248</dd>
                    <dd class="mt-2 text-slate">{{ trans_choice('of :count registered student|of :count registered students', 256, ['count' => 256]) }}</dd>
                </div>

                <div class="rounded-lg border border-gold/40 bg-gold-wash/60 p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gold/60 text-ink" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" d="M9.5 7v10M14.5 7v10" />
                        </svg>
                    </span>
                    <dt class="mt-4 font-semibold text-graphite">{{ __('Students on leave of absence') }}</dt>
                    <dd class="mt-1 text-figure font-semibold tabular-nums text-ink">8</dd>
                    <dd class="mt-2 text-slate">{{ __('Free tuition and applying paused') }}</dd>
                </div>
            </dl>

            <section class="mt-10 overflow-hidden rounded-lg border border-rule bg-white">
                <div class="flex items-center justify-between gap-4 px-5 py-4">
                    <h2 class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v16h16M7 15l4-4 3 3 5-5M16 9h3v3" />
                        </svg>
                        {{ __('Recent scholarship postings') }}
                    </h2>
                    <span class="shrink-0 font-semibold text-seal">{{ __('View all') }}</span>
                </div>

                <div tabindex="0" role="region" aria-label="{{ __('Recent scholarship postings') }}" class="relative overflow-x-auto focus-visible:-outline-offset-2">
                    <table class="w-full min-w-[48rem]">
                        <thead>
                            <tr class="bg-linen-sunk text-left text-graphite">
                                <th scope="col" class="py-2.5 pl-5 pr-4 font-medium">{{ __('Scholarship') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Type') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Deadline') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Status') }}</th>
                                <th scope="col" class="py-2.5 pl-4 pr-5 font-medium">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            <template x-for="posting in state.postings" :key="posting.key">
                                <tr>
                                    <td class="py-3 pl-5 pr-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-seal-tint/60 text-seal" aria-hidden="true">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 10h16M12 3 4 7v3h16V7l-8-4ZM6 10v8M10 10v8M14 10v8M18 10v8" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-ink [overflow-wrap:anywhere]" x-text="posting.title"></p>
                                                <p class="max-w-[24rem] truncate text-slate" x-text="posting.description"></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-graphite" x-text="posting.type"></td>
                                    <td class="whitespace-nowrap px-4 py-3 tabular-nums text-graphite" x-text="posting.deadline"></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-sheet px-2 py-0.5 font-annot text-annot uppercase transition-colors duration-150 ease-in-out"
                                              :class="posting.status === 'open' ? 'bg-grant-wash text-grant-deep' : 'bg-linen-sunk text-graphite'"
                                              x-text="posting.status === 'open' ? @js(__('Open')) : @js(__('Closed'))"></span>
                                    </td>
                                    <td class="whitespace-nowrap py-3 pl-4 pr-5">
                                        <span class="inline-flex items-center rounded-md border border-rule bg-white px-4 py-1.5 font-semibold text-seal">{{ __('View') }}</span>
                                    </td>
                                </tr>
                            </template>
                            <tr :class="{ 'hidden': state.postings.length > 0 }">
                                <td colspan="5" class="px-5 py-8 text-center text-slate">{{ __('Scholarships you post will be listed here.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Mirrors the Post Scholarship form. --}}
    <div :class="{ 'hidden': screen !== 'post' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <span class="font-medium text-graphite underline underline-offset-4">{{ __('Back to scholarships') }}</span>

            <h1 class="mt-6 text-lead font-semibold leading-tight text-ink">{{ __('Post Scholarship') }}</h1>

            <div class="mt-8 space-y-10" data-tour="tour-post">
                <div class="space-y-5">
                    <div>
                        <x-input-label for="tour-title" :value="__('Title')" />
                        <x-text-input id="tour-title" type="text" class="mt-1 block w-full" x-model="state.post.title" readonly tabindex="-1"
                                      ::class="focus === 'post.title' && 'border-seal ring-1 ring-seal'" />
                    </div>

                    <div>
                        <x-input-label for="tour-description" :value="__('Description')" />
                        <textarea id="tour-description" rows="4" readonly tabindex="-1" x-model="state.post.description"
                                  :class="focus === 'post.description' && 'border-seal ring-1 ring-seal'"
 class="mt-1 block w-full rounded-sheet border-rule-strong shadow-sm"></textarea>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <x-input-label for="tour-slots" :value="__('Available Slots')" />
                            <x-text-input id="tour-slots" type="number" class="mt-1 block w-full tabular-nums" x-model="state.post.slots" readonly tabindex="-1"
                                          ::class="focus === 'post.slots' && 'border-seal ring-1 ring-seal'" />
                        </div>

                        <div>
                            <x-input-label for="tour-deadline" :value="__('Application Deadline')" />
                            <x-text-input id="tour-deadline" type="date" class="mt-1 block w-full tabular-nums" x-model="state.post.deadline" readonly tabindex="-1" />
                        </div>
                    </div>
                </div>

                <div class="border-t border-rule pt-8">
                    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-3">
                        <div>
                            <h2 class="text-lead font-semibold text-ink">{{ __('Required Documents') }}</h2>
                            <p class="mt-1  text-slate">{{ __('Documents applicants must upload, e.g. 2x2 Picture.') }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-2 rounded-sheet border border-rule-strong bg-white px-4 py-2  font-semibold text-graphite shadow-sm transition duration-150 ease-in-out"
                              :class="pressed === 'add-document' && 'bg-linen-sunk ring-2 ring-seal ring-offset-2'">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" d="M10 4.5v11M4.5 10h11" />
                            </svg>
                            {{ __('Add document') }}
                        </span>
                    </div>

                    <div class="mt-4">
                        <div :class="{ 'hidden': ! state.post.hasDocument }" class="rounded-sheet border border-rule p-4">
                            <x-text-input type="text" class="block w-full" x-model="state.post.document" readonly tabindex="-1"
                                          :placeholder="__('Document name (e.g. 2x2 Picture)')"
                                          ::class="focus === 'post.document' && 'border-seal ring-1 ring-seal'" />
                        </div>
                        <p :class="{ 'hidden': state.post.hasDocument }" class="border-y border-rule py-6  text-slate">{{ __('No documents required yet.') }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <span class="font-medium text-graphite">{{ __('Cancel') }}</span>
                    <span class="inline-flex items-center justify-center rounded-sheet px-4 py-2.5  font-semibold text-white shadow-sm transition duration-150 ease-in-out"
                          :class="pressed === 'post' ? 'bg-seal-deep ring-2 ring-gold ring-offset-2' : 'bg-seal'">
                        {{ __('Post Scholarship') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Mirrors a scholarship's review page: the banner, then the pass list and its applicants as panels, with what OSAS posted beside them. --}}
    <div :class="{ 'hidden': screen !== 'scholarship' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <span class="font-medium text-graphite underline underline-offset-4">{{ __('Back to scholarships') }}</span>

            <div class="mt-4">
                <x-welcome-banner :heading="$title">
                    <x-badge class="me-1" color="green">{{ __('Open') }}</x-badge>
                    {{ $government }}
                    <span class="tabular-nums"><span aria-hidden="true">&middot;</span> <span x-text="state.remaining"></span> / 2 {{ __('slots remaining') }}</span>
                    <span class="tabular-nums"><span aria-hidden="true">&middot;</span> {{ __('Deadline') }}: {{ $deadline->format('M j, Y') }}</span>
                    <x-badge class="ms-1" ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />

                    <x-slot:action>
                        <span class="inline-flex shrink-0 items-center self-start rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite shadow-seat sm:self-auto">{{ __('Edit') }}</span>
                    </x-slot:action>
                </x-welcome-banner>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem] xl:grid-cols-[minmax(0,1fr)_26rem] lg:items-start">
                <div class="min-w-0">
                    <div data-tour="tour-decide">
                        <section :class="{ 'hidden': ! state.results }" class="mb-6 rounded-lg border border-rule bg-linen-sunk p-5">
                            <h2 class="text-lead font-semibold text-ink">{{ __('Pass List Results') }}</h2>
                            <p class="mt-1 tabular-nums text-graphite" x-text="state.results"></p>
                        </section>

                        <section class="rounded-lg border border-rule bg-white px-5 py-4">
                            <h2 class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                                <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0-4 4m4-4 4 4M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3" />
                                </svg>
                                {{ __('Upload Pass List') }}
                            </h2>

                            <template x-if="state.applicants.some((applicant) => applicant.status === 'pending')">
                                <div>
                                    <p class="mt-1 max-w-prose text-slate"
                                       x-text="@js($passListHint).replace(':count', state.applicants.filter((applicant) => applicant.status === 'pending').length)"></p>

                                    <div class="mt-4 flex flex-col gap-3 rounded-sheet border border-rule p-4 sm:flex-row sm:items-center">
                                        <span class="flex min-w-0 flex-1 items-center gap-4 text-graphite">
                                            <span class="shrink-0 rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite"
                                                  :class="pressed === 'choose-file' && 'bg-linen-sunk ring-2 ring-seal ring-offset-2'">{{ __('Choose file') }}</span>
                                            <span class="truncate" x-text="state.passList || @js(__('No file chosen'))"></span>
                                        </span>
                                        <span class="inline-flex shrink-0 items-center justify-center rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite shadow-sm transition duration-150 ease-in-out"
                                              :class="pressed === 'apply' && 'bg-linen-sunk ring-2 ring-seal ring-offset-2'">{{ __('Apply Decisions') }}</span>
                                    </div>
                                </div>
                            </template>
                            <p :class="{ 'hidden': state.applicants.some((applicant) => applicant.status === 'pending') }" class="mt-1 text-slate">
                                {{ __('There are no pending applicants to decide on right now.') }}
                            </p>
                        </section>
                    </div>

                    <section class="mt-6 overflow-hidden rounded-lg border border-rule bg-white" data-tour="tour-applicants">
                        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 px-5 py-4">
                            <h2 class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                                <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <circle cx="9" cy="8" r="3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 19a5.5 5.5 0 0 0-2.5-4.6" />
                                </svg>
                                {{ __('Applicants') }} <span class="font-normal tabular-nums text-slate">(<span x-text="state.applicants.length"></span>)</span>
                            </h2>
                            <span :class="{ 'hidden': state.applicants.length === 0 }" class="font-semibold text-seal underline underline-offset-4">{{ __('Print report (PDF)') }}</span>
                        </div>

                        <p :class="{ 'hidden': state.applicants.length > 0 }" class="border-t border-rule px-5 py-8 text-center text-slate">{{ __('No applications yet.') }}</p>

                        <ul :class="{ 'hidden': state.applicants.length === 0 }" class="divide-y divide-rule border-t border-rule">
                            <template x-for="applicant in state.applicants" :key="applicant.number">
                                <li class="px-5 py-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-ink">
                                                <span class="uppercase" x-text="applicant.name"></span>
                                                <span class="ms-1 font-normal tabular-nums text-slate" x-text="'(' + applicant.number + ')'"></span>
                                            </p>
                                            <p class="mt-1 text-slate" x-text="applicant.course"></p>
                                        </div>
                                        <span class="inline-flex shrink-0 items-center rounded-sheet px-2 py-0.5 font-annot text-annot uppercase transition-colors duration-150 ease-in-out"
                                              :class="state.badges[applicant.status]"
                                              x-text="state.labels[applicant.status]"></span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-sheet border border-rule px-2.5 py-1 font-medium text-graphite">2x2 Picture</span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </section>
                </div>

                <aside class="overflow-hidden rounded-lg border border-rule bg-white">
                    <h2 class="flex items-center gap-2.5 px-5 py-4 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5M12 8h.01" />
                        </svg>
                        {{ __('About this scholarship') }}
                    </h2>
                    <p class="max-w-prose border-t border-rule px-5 py-4 text-justify leading-relaxed text-graphite">{{ $description }}</p>

                    <h3 class="border-t border-rule bg-linen-sunk px-5 py-2.5 font-semibold text-ink">{{ __('Required Documents') }}</h3>
                    <ul class="divide-y divide-rule border-t border-rule px-5">
                        <li class="py-3 font-medium text-ink">2x2 Picture</li>
                    </ul>
                </aside>
            </div>
        </div>
    </div>

    {{-- Mirrors the Scholarships page: the banner, then one card per scholarship. --}}
    <div :class="{ 'hidden': screen !== 'scholarships' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Manage Scholarships')">
                <span class="font-semibold tabular-nums text-ink" x-text="state.cards.length"></span>
                {{ __('scholarships posted.') }}

                <x-slot:action>
                    <span class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm sm:self-auto">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" d="M10 4v12M4 10h12" />
                        </svg>
                        {{ __('Post Scholarship') }}
                    </span>
                </x-slot:action>
            </x-welcome-banner>

            <ul class="mt-8 grid gap-4 grid-cols-[repeat(auto-fill,minmax(min(20rem,100%),1fr))]" role="list" data-tour="tour-cards">
                <template x-for="card in state.cards" :key="card.key">
                    <li>
                        <div class="flex h-full flex-col rounded-sheet border bg-white p-5 transition-colors duration-150 ease-in-out"
                             :class="pressed === 'card-' + card.key ? 'border-seal' : 'border-rule'">
                            <div class="flex items-start justify-between gap-3">
                                <p class="min-w-0 text-lead font-semibold leading-snug [overflow-wrap:anywhere]" :class="pressed === 'card-' + card.key ? 'text-seal' : 'text-ink'" x-text="card.title"></p>
                                <x-badge class="shrink-0" color="green">{{ __('Open') }}</x-badge>
                            </div>
                            <p class="mt-1 text-slate" x-text="card.type"></p>

                            <div class="mt-auto pt-5">
                                <dl class="grid grid-cols-2 gap-3 border-t border-rule pt-4">
                                    <div class="min-w-0">
                                        <dt class="text-slate">{{ __('Slots left') }}</dt>
                                        <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink" x-text="card.left"></dd>
                                    </div>
                                    <div class="min-w-0">
                                        <dt class="text-slate">{{ __('Applicants') }}</dt>
                                        <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink" x-text="card.applicants"></dd>
                                    </div>
                                </dl>

                                <p class="mt-4 font-semibold tabular-nums text-ink">{{ __('Closes') }} <span x-text="card.closes"></span></p>
                            </div>

                            <div class="mt-5 flex items-center justify-between gap-4">
                                <span class="font-semibold text-seal">{{ __('View scholarship') }} &rarr;</span>
                                <span class="font-medium text-graphite">{{ __('Edit') }}</span>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    {{-- Mirrors the Fund Allocation list: the banner, then a card for each scholarship students have passed. --}}
    <div :class="{ 'hidden': screen !== 'fundList' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <x-welcome-banner :heading="__('Fund Allocation')">
                {!! $count(1) !!} {{ __('scholarship still needs its total funds.') }}
            </x-welcome-banner>

            <ul class="mt-8 grid gap-4 grid-cols-[repeat(auto-fill,minmax(min(20rem,100%),1fr))]" role="list" data-tour="tour-fund-list">
                <li>
                    <div class="flex h-full flex-col rounded-sheet border bg-white p-5 transition-colors duration-150 ease-in-out"
                         :class="pressed === 'open-fund' ? 'border-seal' : 'border-rule'">
                        <div class="flex items-start justify-between gap-3">
                            <p class="min-w-0 text-lead font-semibold leading-snug [overflow-wrap:anywhere]" :class="pressed === 'open-fund' ? 'text-seal' : 'text-ink'">{{ $title }}</p>
                            <x-badge color="gold" class="shrink-0">{{ __('Fund not set') }}</x-badge>
                        </div>
                        <p class="mt-1 text-slate">{{ $government }} <span aria-hidden="true">&middot;</span> {{ __('Open') }}</p>

                        <div class="mt-auto pt-5">
                            <dl class="grid grid-cols-2 gap-3 border-t border-rule pt-4">
                                <div class="min-w-0">
                                    <dt class="text-slate">{{ __('Total funds') }}</dt>
                                    <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink">{{ __('Not set') }}</dd>
                                </div>
                                <div class="min-w-0">
                                    <dt class="text-slate">{{ __('Per student') }}</dt>
                                    <dd class="mt-0.5 text-lead font-semibold tabular-nums text-ink"><span aria-hidden="true">&mdash;</span><span class="sr-only">{{ __('No share until the fund is set') }}</span></dd>
                                </div>
                            </dl>

                            <p class="mt-4 font-semibold tabular-nums text-ink">{{ trans_choice(':count student passed|:count students passed', 2, ['count' => 2]) }}</p>
                        </div>

                        <span class="mt-5 font-semibold text-seal">{{ __('Open fund allocation') }} &rarr;</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    {{-- Mirrors one scholarship's fund allocation: the total funds, divided live by the students who passed, then their shares. --}}
    <div :class="{ 'hidden': screen !== 'fund' }" x-cloak class="px-6 py-10 lg:px-10"
         x-data="{ per() { const centavos = Math.round(parseFloat(state.fund.total) * 100); return centavos >= 100 ? '₱' + (Math.floor(centavos / 2) / 100).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : null } }">
        <div class="mx-auto max-w-[100rem]">
            <span class="font-medium text-graphite underline underline-offset-4">{{ __('Back to fund allocation') }}</span>

            <div class="mt-4">
                <x-welcome-banner :heading="$title">
                    <x-badge class="me-2" color="green">{{ __('Open') }}</x-badge>
                    {{ $government }}

                    <x-slot:action>
                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            <span class="inline-flex items-center rounded-sheet border border-rule-strong bg-white px-4 py-2 font-semibold text-graphite shadow-sm">{{ __('View scholarship') }}</span>
                            <span class="inline-flex items-center justify-center rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm">{{ __('Download Excel sheet') }}</span>
                        </div>
                    </x-slot:action>
                </x-welcome-banner>
            </div>

            <div data-tour="tour-fund">
                <div :class="{ 'hidden': ! state.fund.saved }" class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3 text-grant-deep">
                    {{ trans_choice('Total funds saved. The :count student who passed is allocated :share.|Total funds saved. Each of the :count students who passed is allocated :share.', 2, ['count' => 2, 'share' => $share]) }}
                </div>

                <div class="mt-6 rounded-sheet border border-rule bg-white p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div class="sm:w-64">
                            <x-input-label for="tour-total-funds" :value="__('Total funds (₱)')" />
                            <x-text-input id="tour-total-funds" type="text" class="mt-1 block w-full tabular-nums" x-model="state.fund.total" readonly tabindex="-1"
                                          ::class="focus === 'fund.total' && 'border-seal ring-1 ring-seal'" />
                        </div>

                        <p class="min-w-0 text-slate sm:pb-2.5">
                            {{ trans_choice('÷ :count student who passed =|÷ :count students who passed =', 2, ['count' => 2]) }}
                            <span class="font-semibold tabular-nums text-ink" x-text="per() ?? '—'"></span>
                            {{ __('per student') }}
                        </p>

                        <span class="inline-flex items-center justify-center rounded-sheet px-4 py-2.5 font-semibold text-white shadow-sm transition duration-150 ease-in-out sm:ms-auto"
                              :class="pressed === 'set-funds' ? 'bg-seal-deep ring-2 ring-gold ring-offset-2' : 'bg-seal'"
                              x-text="state.fund.saved ? @js(__('Update total funds')) : @js(__('Set total funds'))"></span>
                    </div>

                    <p class="mt-3 max-w-prose text-slate">{{ __('Divided equally among the students who passed, rounded down to the centavo.') }}</p>
                </div>

                <dl class="mt-6 grid gap-px overflow-hidden rounded-sheet border border-rule bg-rule sm:grid-cols-3">
                    <div class="bg-white px-4 py-4">
                        <dt class="font-annot text-annot uppercase text-slate">{{ __('Total funds') }}</dt>
                        <dd class="mt-1 text-lead font-semibold tabular-nums text-ink" x-text="state.fund.saved ? '₱50,000.00' : @js(__('Not set'))"></dd>
                    </div>
                    <div class="bg-white px-4 py-4">
                        <dt class="font-annot text-annot uppercase text-slate">{{ __('Per student') }}</dt>
                        <dd class="mt-1 text-lead font-semibold tabular-nums text-ink" x-text="state.fund.saved ? @js($share) : '—'"></dd>
                        <dd class="text-slate">{{ trans_choice(':count student passed|:count students passed', 2, ['count' => 2]) }}</dd>
                    </div>
                    <div class="bg-white px-4 py-4">
                        <dt class="font-annot text-annot uppercase text-slate">{{ __('Allocated') }}</dt>
                        <dd class="mt-1 text-lead font-semibold tabular-nums text-ink" x-text="state.fund.saved ? '₱50,000.00' : '—'"></dd>
                    </div>
                </dl>

                <section class="mt-8">
                    <h2 class="text-lead font-semibold text-ink">{{ __('Students who passed') }} <span class="font-normal tabular-nums text-slate">(2)</span></h2>

                    <div tabindex="0" role="region" aria-label="{{ __('Students who passed') }}" class="relative mt-3 overflow-x-auto focus-visible:-outline-offset-2">
                        <table class="w-full min-w-[44rem]">
                            <thead>
                                <tr class="whitespace-nowrap font-annot text-annot uppercase text-slate [&>th]:shadow-[inset_0_-1px_0_theme(colors.rule.DEFAULT)]">
                                    <th scope="col" class="py-2 pr-4 text-left font-medium">{{ __('Name') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Student no.') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('Course') }}</th>
                                    <th scope="col" class="py-2 pl-4 text-right font-medium">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($passed as $student)
                                    <tr class="border-b border-rule">
                                        <td class="py-3 pr-4 font-medium uppercase text-ink">{{ $student['name'] }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate">{{ $student['number'] }}</td>
                                        <td class="px-4 py-3 text-slate">{{ $student['course'] }}</td>
                                        <td class="whitespace-nowrap py-3 pl-4 text-right tabular-nums text-ink" x-text="state.fund.saved ? @js($share) : '—'"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- Mirrors Search Student: the banner, the filters in one row, then the registered students. --}}
    <div :class="{ 'hidden': screen !== 'roster' }" x-cloak class="px-6 py-10 lg:px-10"
         x-data="{ found() { const term = state.search.toLowerCase(); return @js($roster).filter((student) => (student.name + ' ' + student.email + ' ' + student.number).toLowerCase().includes(term)) } }">
        <div class="mx-auto max-w-[100rem]" data-tour="tour-roster">
            <x-welcome-banner :heading="__('Search Student')">
                {{ __('Find registered students by name, email or student number.') }}
            </x-welcome-banner>

            <div class="mt-6 rounded-lg border border-rule bg-linen-sunk p-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1.25fr)_auto] lg:items-end">
                    <div>
                        <x-input-label for="tour-roster-search" :value="__('Search student')" />
                        <x-text-input id="tour-roster-search" type="text" class="mt-1 block w-full" x-model="state.search" readonly tabindex="-1" :placeholder="__('Email, name or student no.')"
                                      ::class="focus === 'search' && 'border-seal ring-1 ring-seal'" />
                    </div>
                    <div>
                        <x-input-label for="tour-roster-course" :value="__('Course')" />
                        <x-text-input id="tour-roster-course" type="text" class="mt-1 block w-full" readonly tabindex="-1" :placeholder="__('Any course, e.g. Nursing')" />
                    </div>
                    <div>
                        <x-input-label for="tour-roster-year" :value="__('Year level')" />
                        <x-text-input id="tour-roster-year" type="text" class="mt-1 block w-full" readonly tabindex="-1" :value="__('All year levels')" />
                    </div>
                    <div>
                        <x-input-label for="tour-roster-sort" :value="__('Sort by')" />
                        <x-text-input id="tour-roster-sort" type="text" class="mt-1 block w-full" readonly tabindex="-1" :value="__('Surname: A–Z')" />
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <span class="inline-flex items-center rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm">{{ __('Search') }}</span>
                    </div>
                </div>
            </div>

            <section class="mt-6 overflow-hidden rounded-lg border border-rule bg-white">
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-5 py-4">
                    <h2 class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <circle cx="9" cy="8" r="3" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 19a5.5 5.5 0 0 0-2.5-4.6" />
                        </svg>
                        {{ __('Registered students') }}
                    </h2>
                    <p class="text-graphite"><span class="font-semibold tabular-nums text-ink" x-text="found().length"></span> <span x-text="found().length === 1 ? @js(__('student found')) : @js(__('students found'))"></span></p>
                </div>

                <div tabindex="0" role="region" aria-label="{{ __('Registered students') }}" class="relative overflow-x-auto focus-visible:-outline-offset-2">
                    <table class="w-full min-w-[52rem]">
                        <thead>
                            <tr class="whitespace-nowrap bg-linen-sunk text-left text-graphite">
                                <th scope="col" class="py-2.5 pl-5 pr-4 font-medium">{{ __('Name') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Email') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Student no.') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Course') }}</th>
                                <th scope="col" class="py-2.5 pl-4 pr-5 font-medium">{{ __('Year level') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            <template x-for="student in found()" :key="student.key">
                                <tr>
                                    <td class="py-3 pl-5 pr-4 font-semibold uppercase text-ink" x-text="student.name"></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-graphite" x-text="student.email"></td>
                                    <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate" x-text="student.number"></td>
                                    <td class="px-4 py-3 text-slate" x-text="student.course"></td>
                                    <td class="whitespace-nowrap py-3 pl-4 pr-5 text-slate" x-text="student.year"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Mirrors Edit Student Data: the banner, the search, then the students, each with its Edit button. --}}
    <div :class="{ 'hidden': screen !== 'students' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]" data-tour="tour-students">
            <x-welcome-banner :heading="__('Edit Student Data')">
                {{ __('Find a student to correct their details or mark them on leave of absence.') }}
            </x-welcome-banner>

            <div class="mt-6 rounded-lg border border-rule bg-linen-sunk p-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="sm:w-full sm:max-w-md">
                        <x-input-label for="tour-students-search" :value="__('Search student')" />
                        <x-text-input id="tour-students-search" type="text" class="mt-1 block w-full" readonly tabindex="-1" :placeholder="__('Email, name or student no.')" />
                    </div>
                    <span class="inline-flex items-center self-start rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm sm:self-auto">{{ __('Search') }}</span>
                </div>
            </div>

            <section class="mt-6 overflow-hidden rounded-lg border border-rule bg-white">
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-5 py-4">
                    <h2 class="flex items-center gap-2.5 text-lead font-semibold text-ink">
                        <svg class="h-5 w-5 shrink-0 text-seal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <circle cx="9" cy="8" r="3" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 5.5a3 3 0 0 1 0 5.5M17.5 19a5.5 5.5 0 0 0-2.5-4.6" />
                        </svg>
                        {{ __('Registered students') }}
                    </h2>
                    <p class="text-graphite">{!! $count(count($roster)) !!} {{ trans_choice('student found|students found', count($roster)) }}</p>
                </div>

                <div tabindex="0" role="region" aria-label="{{ __('Registered students') }}" class="relative overflow-x-auto focus-visible:-outline-offset-2">
                    <table class="w-full min-w-[52rem]">
                        <thead>
                            <tr class="whitespace-nowrap bg-linen-sunk text-left text-graphite">
                                <th scope="col" class="py-2.5 pl-5 pr-4 font-medium">{{ __('Name') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Student no.') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Course') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Year level') }}</th>
                                <th scope="col" class="px-4 py-2.5 font-medium">{{ __('Status') }}</th>
                                <th scope="col" class="py-2.5 pl-4 pr-5 text-right font-medium"><span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rule">
                            @foreach ($roster as $student)
                                <tr>
                                    <td class="py-3 pl-5 pr-4">
                                        <p class="font-semibold uppercase text-ink">{{ $student['name'] }}</p>
                                        <p class="whitespace-nowrap text-slate">{{ $student['email'] }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate">{{ $student['number'] }}</td>
                                    <td class="px-4 py-3 text-slate">{{ $student['course'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate">{{ $student['year'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate">
                                        @if ($student['loa'])
                                            <x-badge>{{ __('LOA') }}</x-badge>
                                        @else
                                            {{ $active }}
                                        @endif
                                    </td>
                                    <td class="py-3 pl-4 pr-5 text-right">
                                        <span class="inline-flex items-center rounded-sheet border border-rule-strong bg-white px-3 py-1.5 font-semibold text-seal shadow-sm transition duration-150 ease-in-out"
                                              :class="pressed === @js('edit-'.$student['key']) && 'bg-linen-sunk ring-2 ring-seal ring-offset-2'">{{ __('Edit') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Mirrors one student's edit form: the banner headed by their name, then one sectioned card with Save Changes at its foot. --}}
    <div :class="{ 'hidden': screen !== 'student' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <span class="font-medium text-graphite underline underline-offset-4">{{ __('Back to students') }}</span>

            <div class="mt-4">
                <x-welcome-banner heading="DELA CRUZ, JUAN">
                    {{ __('Correct this student\'s details. Their scholarship applications use what is saved here.') }}
                </x-welcome-banner>
            </div>

            <div :class="{ 'hidden': ! state.studentSaved }" class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3 text-grant-deep">
                {{ __('Student details saved.') }}
            </div>

            <div class="mt-6 overflow-hidden rounded-lg border border-rule bg-white" data-tour="tour-student">
                @foreach ([
                    [__('Name'), [[__('Last name'), 'Dela Cruz', ''], [__('First name'), 'Juan', ''], [__('Middle name (optional)'), '', '']], 'sm:grid-cols-3'],
                    [__('Enrollment'), [[__('CvSU email'), 'juan.delacruz@cvsu.edu.ph', 'sm:col-span-2'], [__('Student number'), '202201234', ''], [__('Year level'), __('Year :level', ['level' => 3]), ''], [__('Course'), 'BS Information Technology', 'sm:col-span-2'], [__('Program length'), __(':years years', ['years' => 4]), 'sm:col-span-2']], 'sm:grid-cols-2 xl:grid-cols-4'],
                ] as [$section, $fields, $grid])
                    <section class="grid gap-x-8 gap-y-4 px-5 py-6 sm:px-6 lg:grid-cols-[10rem_minmax(0,1fr)] {{ $loop->first ? '' : 'border-t border-rule' }}">
                        <h2 class="text-lead font-semibold text-ink">{{ $section }}</h2>
                        <div class="grid gap-x-4 gap-y-5 {{ $grid }}">
                            @foreach ($fields as [$label, $value, $span])
                                <div class="{{ $span }}">
                                    <span class="block font-annot text-annot uppercase text-graphite">{{ $label }}</span>
                                    <span class="mt-1 block min-h-[2.625rem] rounded-sheet border border-rule-strong px-3 py-2 text-ink {{ $loop->parent->index === 1 && $loop->first ? 'bg-linen-sunk text-slate' : 'bg-white' }}">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <section class="grid gap-x-8 gap-y-4 border-t border-rule px-5 py-6 sm:px-6 lg:grid-cols-[10rem_minmax(0,1fr)]">
                    <h2 class="text-lead font-semibold text-ink">{{ __('Student status') }}</h2>
                    <div>
                        <span class="block font-annot text-annot uppercase text-graphite">{{ __('Status') }}</span>
                        <span class="mt-1 flex items-center justify-between rounded-sheet border bg-white px-3 py-2 text-ink transition duration-150 ease-in-out sm:max-w-md"
                              :class="pressed === 'status' ? 'border-seal ring-1 ring-seal' : 'border-rule-strong'">
                            <span x-text="state.studentStatus"></span>
                            <svg class="h-4 w-4 text-slate" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 8 4 4 4-4" /></svg>
                        </span>
                        <p class="mt-1 text-slate">{{ __('While on leave of absence, a student\'s free tuition under RA 10931 is paused and they cannot apply to scholarships. Saving them as on leave voids their applications still waiting for a decision, freeing those slots for other students; setting them back to Active does not restore them. Scholarships already approved for them are kept.') }}</p>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 border-t border-rule bg-linen-sunk/60 px-5 py-4 sm:px-6">
                    <span class="font-medium text-slate">{{ __('Cancel') }}</span>
                    <span class="inline-flex items-center justify-center rounded-sheet px-4 py-2.5 font-semibold text-white shadow-sm transition duration-150 ease-in-out"
                          :class="pressed === 'save-student' ? 'bg-seal-deep ring-2 ring-gold ring-offset-2' : 'bg-seal'">{{ __('Save Changes') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Mirrors the staff profile: one card of rows with one Save, and Delete Account beneath. --}}
    <div :class="{ 'hidden': screen !== 'profile' }" x-cloak class="px-6 py-6 lg:px-10">
        <div class="mx-auto max-w-[88rem]">
            <h1 class="text-lead font-semibold text-ink">{{ __('Profile') }}</h1>

            <div class="mt-4" data-tour="tour-profile">
                <div class="rounded-lg border border-rule bg-white">
                    @foreach ([
                        [__('Profile information'), __('Your name and the email you sign in with.'), [[__('Last Name'), $admin->last_name ?? '', ''], [__('First Name'), $admin->first_name ?? '', ''], [__('Middle Name'), $admin->middle_name ?? '', ''], [__('Email'), $admin->email, 'sm:col-span-2']]],
                        [__('Password'), __('Leave blank to keep your current password.'), [[__('Current Password'), '', ''], [__('New Password'), '', ''], [__('Confirm Password'), '', '']]],
                    ] as [$section, $hint, $fields])
                        <section class="grid gap-x-8 gap-y-3 p-5 lg:grid-cols-[14rem_1fr] {{ $loop->first ? '' : 'border-t border-rule' }}">
                            <header>
                                <h2 class="font-semibold text-ink">{{ $section }}</h2>
                                <p class="mt-0.5 text-slate">{{ $hint }}</p>
                            </header>
                            <div class="grid gap-3 sm:grid-cols-3">
                                @foreach ($fields as [$label, $value, $span])
                                    <div class="{{ $span }}">
                                        <span class="block font-annot text-annot uppercase text-graphite">{{ $label }}</span>
                                        <span class="mt-1 block min-h-[2.625rem] rounded-sheet border border-rule-strong bg-white px-3 py-2 text-ink">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    <div class="flex items-center justify-end gap-4 border-t border-rule px-5 py-3">
                        <span class="inline-flex items-center rounded-sheet bg-seal px-4 py-2.5 font-semibold text-white shadow-sm">{{ __('Save') }}</span>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 rounded-lg border border-oxide/30 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-ink">{{ __('Delete Account') }}</h2>
                        <p class="mt-0.5 text-graphite">{{ __('Permanently deletes your account and all of its data. This cannot be undone.') }}</p>
                    </div>
                    <span class="inline-flex shrink-0 items-center justify-center self-start rounded-sheet bg-oxide px-4 py-2.5 font-semibold text-white shadow-sm sm:self-auto">{{ __('Delete Account') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<x-tutorial :steps="$steps" />
