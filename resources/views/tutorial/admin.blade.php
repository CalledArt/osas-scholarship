@php
    $title = 'DOST-SEI Merit Scholarship';
    $deadline = now()->addDays(9);
    $description = 'For second-year science and engineering students with a GWA of 1.75 or better.';
    $badges = [
        'pending' => 'bg-cvsu-gold/20 text-cvsu-green-dark',
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];
    $labels = collect(\App\Enums\ApplicationStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
    $applicants = [
        ['name' => 'Andrea Villanueva', 'number' => '22-01877', 'course' => 'BS Civil Engineering', 'status' => 'pending'],
        ['name' => 'Carlo Mendoza', 'number' => '22-02310', 'course' => 'BS Computer Science', 'status' => 'pending'],
        ['name' => 'Juan Dela Cruz', 'number' => '22-01234', 'course' => 'BS Information Technology', 'status' => 'pending'],
    ];
    $passListHint = __('Upload a CSV of who passed — a "student_number" column, a "name" column, or both (student number is matched first since it\'s unique; name is the fallback). A header-less single column of names also works. Every other pending applicant (:count) will be rejected automatically.');
    $count = fn (int $number): string => '<span class="font-semibold tabular-nums text-gray-900">'.$number.'</span>';
    $queueRow = [
        'key' => 'sample',
        'title' => $title,
        'availability' => __('Closes in :count days', ['count' => 9]),
        'filled' => __(':filled of :slots slots filled', ['filled' => 0, 'slots' => 2]),
        'pending' => 3,
        'group' => 'open',
    ];
    $otherRow = [
        'key' => 'other',
        'title' => 'CHED Tulong Dunong Program',
        'availability' => __('Closes in :count days', ['count' => 23]),
        'filled' => __(':filled of :slots slots filled', ['filled' => 1, 'slots' => 10]),
        'pending' => 5,
        'group' => 'open',
    ];

    $base = [
        'badges' => $badges,
        'labels' => $labels,
        'clock' => null,
        'summary' => e(__('No scholarships have been posted yet.')),
        'queue' => [],
        'post' => ['title' => '', 'description' => '', 'slots' => '', 'deadline' => '', 'document' => '', 'hasDocument' => false],
        'applicants' => [],
        'remaining' => 2,
        'passList' => '',
        'results' => '',
    ];

    $caughtUp = [
        'summary' => e(__("You're all caught up. No applications need a decision.")),
        'queue' => [],
    ];

    $steps = [
        [
            'screen' => 'dashboard',
            'targets' => [],
            'location' => null,
            'title' => __('Welcome to the scholarship office'),
            'body' => __('This practice tour runs a sample scholarship from posting to a decision, on screens like the real ones. Nothing here is saved or sent to students.'),
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
            'targets' => ['tour-queue'],
            'location' => ['target' => 'nav-dashboard', 'trail' => [__('Dashboard')]],
            'title' => __('See what is ready to decide'),
            'body' => __('Your dashboard lists every scholarship with applicants waiting. Once a scholarship stops taking applications, it moves to Ready for a pass list. Press Skip ahead to jump to this one\'s deadline.'),
            'state' => [
                'summary' => $count(2).' '.e(__('scholarships with applicants are still taking applications.')),
                'queue' => [$queueRow, $otherRow],
            ],
            'actions' => [
                ['pause', __('Skip ahead :count days', ['count' => 9])],
                ['set', 'clock', __(':count days later', ['count' => 9])],
                ['wait', 500],
                ['set', 'queue.0.availability', __('Closed today')],
                ['set', 'queue.0.group', 'ready'],
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
            'screen' => 'dashboard',
            'targets' => ['nav-roster'],
            'location' => ['target' => 'nav-roster', 'trail' => [__('Search Student')]],
            'title' => __('Search Student'),
            'body' => __('Find registered students by name, email or student number. Filter by year level or course, and sort by surname, student number or year level.'),
            'state' => $caughtUp,
            'actions' => [],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['nav-students'],
            'location' => ['target' => 'nav-students', 'trail' => [__('Edit Student Data')]],
            'title' => __('Edit Student Data'),
            'body' => __('Correct a student\'s name, student number, course, year level or program length. Their sign-in email and personal details cannot be changed.'),
            'state' => $caughtUp,
            'actions' => [],
        ],
        [
            'screen' => 'dashboard',
            'targets' => ['nav-tutorial'],
            'location' => ['target' => 'nav-tutorial', 'trail' => [__('Tutorial')]],
            'title' => __('Replay this tour'),
            'body' => __('Open Tutorial in the menu whenever you want to run this practice again.'),
            'state' => $caughtUp,
            'actions' => [],
        ],
    ];
@endphp

<div x-data="tutorialStage(@js(['base' => $base]))"
     @tutorial-step.window="perform($event.detail)"
     @tutorial-resume.window="proceed()"
     role="region"
     aria-label="{{ __('Sample screens') }}">

    {{-- Mirrors the admin dashboard's decision queue. --}}
    <div :class="{ 'hidden': screen !== 'dashboard' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ __('Dashboard') }}</h1>
                        <x-badge ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />
                    </div>
                    <p class="mt-2 text-base text-gray-600" x-html="state.summary"></p>
                </div>

                <span class="inline-flex shrink-0 items-center justify-center rounded-lg bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm">
                    {{ __('Post scholarship') }}
                </span>
            </div>

            <div :class="{ 'hidden': state.queue.length === 0 }" class="mt-10 space-y-10" data-tour="tour-queue">
                @foreach (['ready' => [__('Ready for a pass list'), __('Closed to new applicants. Longest wait first.')], 'open' => [__('Still taking applications'), __('A pass list rejects every applicant not on it, so upload one after the scholarship closes.')]] as $group => [$heading, $hint])
                    <section :class="{ 'hidden': ! state.queue.some((row) => row.group === @js($group)) }">
                        <h2 class="text-base font-semibold text-gray-900">{{ $heading }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>

                        <ul class="-mx-3 mt-3 divide-y divide-gray-100 border-y border-gray-200">
                            <template x-for="row in state.queue.filter((row) => row.group === @js($group))" :key="row.key">
                                <li class="flex items-center gap-4 px-3 py-4">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-gray-900 [overflow-wrap:anywhere]" x-text="row.title"></p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            <span x-text="row.availability"></span>
                                            <span aria-hidden="true">&middot;</span>
                                            <span class="tabular-nums" x-text="row.filled"></span>
                                        </p>
                                    </div>

                                    <p class="shrink-0 text-right text-sm text-gray-500">
                                        <span class="block text-lg font-semibold leading-6 tabular-nums text-gray-900" x-text="row.pending"></span>
                                        {{ __('pending') }}
                                    </p>

                                    <span class="hidden shrink-0 text-sm font-semibold text-cvsu-green sm:inline">{{ __('Review') }} &rarr;</span>
                                </li>
                            </template>
                        </ul>
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Mirrors the Post Scholarship form. --}}
    <div :class="{ 'hidden': screen !== 'post' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <span class="text-sm font-medium text-gray-600 underline underline-offset-4">{{ __('Back to scholarships') }}</span>

            <h1 class="mt-6 text-xl font-semibold leading-tight text-gray-900">{{ __('Post Scholarship') }}</h1>

            <div class="mt-8 space-y-10" data-tour="tour-post">
                <div class="space-y-5">
                    <div>
                        <x-input-label for="tour-title" :value="__('Title')" />
                        <x-text-input id="tour-title" type="text" class="mt-1 block w-full" x-model="state.post.title" readonly tabindex="-1"
                                      ::class="focus === 'post.title' && 'border-cvsu-green ring-1 ring-cvsu-green'" />
                    </div>

                    <div>
                        <x-input-label for="tour-description" :value="__('Description')" />
                        <textarea id="tour-description" rows="4" readonly tabindex="-1" x-model="state.post.description"
                                  :class="focus === 'post.description' && 'border-cvsu-green ring-1 ring-cvsu-green'"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2 sm:gap-4">
                        <div>
                            <x-input-label for="tour-slots" :value="__('Available Slots')" />
                            <x-text-input id="tour-slots" type="number" class="mt-1 block w-full tabular-nums" x-model="state.post.slots" readonly tabindex="-1"
                                          ::class="focus === 'post.slots' && 'border-cvsu-green ring-1 ring-cvsu-green'" />
                        </div>

                        <div>
                            <x-input-label for="tour-deadline" :value="__('Application Deadline')" />
                            <x-text-input id="tour-deadline" type="date" class="mt-1 block w-full tabular-nums" x-model="state.post.deadline" readonly tabindex="-1" />
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-8">
                    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">{{ __('Required Documents') }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Documents applicants must upload, e.g. 2x2 Picture.') }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition duration-150 ease-in-out"
                              :class="pressed === 'add-document' && 'bg-gray-50 ring-2 ring-cvsu-green ring-offset-2'">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" d="M10 4.5v11M4.5 10h11" />
                            </svg>
                            {{ __('Add document') }}
                        </span>
                    </div>

                    <div class="mt-4">
                        <div :class="{ 'hidden': ! state.post.hasDocument }" class="rounded-lg border border-gray-200 p-4">
                            <x-text-input type="text" class="block w-full" x-model="state.post.document" readonly tabindex="-1"
                                          :placeholder="__('Document name (e.g. 2x2 Picture)')"
                                          ::class="focus === 'post.document' && 'border-cvsu-green ring-1 ring-cvsu-green'" />
                        </div>
                        <p :class="{ 'hidden': state.post.hasDocument }" class="border-y border-gray-200 py-6 text-sm text-gray-500">{{ __('No documents required yet.') }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <span class="text-sm font-medium text-gray-600">{{ __('Cancel') }}</span>
                    <span class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-150 ease-in-out"
                          :class="pressed === 'post' ? 'bg-cvsu-green-dark ring-2 ring-cvsu-gold ring-offset-2' : 'bg-cvsu-green'">
                        {{ __('Post Scholarship') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Mirrors a scholarship's review page: the pass list and its applicants, with what OSAS posted beside them. --}}
    <div :class="{ 'hidden': screen !== 'scholarship' }" x-cloak class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-5xl">
            <span class="text-sm font-medium text-gray-600 underline underline-offset-4">{{ __('Back to scholarships') }}</span>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
                <div class="flex min-w-0 flex-wrap items-center gap-3">
                    <h1 class="text-xl font-semibold leading-tight text-gray-900">{{ $title }}</h1>
                    <x-badge ::class="{ 'hidden': ! state.clock }" x-text="state.clock" />
                </div>
                <span class="inline-flex shrink-0 items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm">{{ __('Edit') }}</span>
            </div>

            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                <x-badge color="green">{{ __('Open') }}</x-badge>
                <span class="tabular-nums"><span x-text="state.remaining"></span> / 2 {{ __('slots remaining') }}</span>
                <span class="tabular-nums"><span aria-hidden="true">&middot;</span> {{ __('Deadline') }}: {{ $deadline->format('M j, Y') }}</span>
            </div>

            <div class="mt-10 lg:grid lg:grid-cols-[minmax(0,1fr)_18rem] lg:gap-x-12">
                <div class="min-w-0">
                    <div data-tour="tour-decide">
                        <section :class="{ 'hidden': ! state.results }" class="mb-10 rounded-lg border border-gray-200 bg-gray-50 p-5 text-sm">
                            <h2 class="text-base font-semibold text-gray-900">{{ __('Pass List Results') }}</h2>
                            <p class="mt-1 tabular-nums text-gray-700" x-text="state.results"></p>
                        </section>

                        <section>
                            <h2 class="text-base font-semibold text-gray-900">{{ __('Upload Pass List') }}</h2>

                            <template x-if="state.applicants.some((applicant) => applicant.status === 'pending')">
                                <div>
                                    <p class="mt-1 max-w-prose text-sm text-gray-500"
                                       x-text="@js($passListHint).replace(':count', state.applicants.filter((applicant) => applicant.status === 'pending').length)"></p>

                                    <div class="mt-4 flex flex-col gap-3 rounded-lg border border-gray-200 p-4 sm:flex-row sm:items-center">
                                        <span class="flex min-w-0 flex-1 items-center gap-4 text-sm text-gray-600">
                                            <span class="shrink-0 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700"
                                                  :class="pressed === 'choose-file' && 'bg-gray-50 ring-2 ring-cvsu-green ring-offset-2'">{{ __('Choose file') }}</span>
                                            <span class="truncate" x-text="state.passList || @js(__('No file chosen'))"></span>
                                        </span>
                                        <span class="inline-flex shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition duration-150 ease-in-out"
                                              :class="pressed === 'apply' && 'bg-gray-50 ring-2 ring-cvsu-green ring-offset-2'">{{ __('Apply Decisions') }}</span>
                                    </div>
                                </div>
                            </template>
                            <p :class="{ 'hidden': state.applicants.some((applicant) => applicant.status === 'pending') }" class="mt-1 text-sm text-gray-500">
                                {{ __('There are no pending applicants to decide on right now.') }}
                            </p>
                        </section>
                    </div>

                    <section class="mt-12" data-tour="tour-applicants">
                        <h2 class="text-base font-semibold text-gray-900">
                            {{ __('Applicants') }} <span class="font-normal tabular-nums text-gray-500">(<span x-text="state.applicants.length"></span>)</span>
                        </h2>

                        <p :class="{ 'hidden': state.applicants.length > 0 }" class="mt-3 border-y border-gray-200 py-6 text-sm text-gray-500">{{ __('No applications yet.') }}</p>

                        <ul :class="{ 'hidden': state.applicants.length === 0 }" class="mt-3 divide-y divide-gray-100 border-y border-gray-200">
                            <template x-for="applicant in state.applicants" :key="applicant.number">
                                <li class="py-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900">
                                                <span x-text="applicant.name"></span>
                                                <span class="ms-1 font-normal tabular-nums text-gray-500" x-text="'(' + applicant.number + ')'"></span>
                                            </p>
                                            <p class="mt-1 text-sm text-gray-500" x-text="applicant.course"></p>
                                        </div>
                                        <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-medium transition-colors duration-150 ease-in-out"
                                              :class="state.badges[applicant.status]"
                                              x-text="state.labels[applicant.status]"></span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600">2x2 Picture</span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </section>
                </div>

                <aside class="mt-12 lg:mt-0">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('About this scholarship') }}</h2>
                    <p class="mt-3 max-w-prose text-sm leading-relaxed text-gray-700">{{ $description }}</p>

                    <h3 class="mt-8 text-sm font-semibold text-gray-900">{{ __('Required Documents') }}</h3>
                    <ul class="mt-2 divide-y divide-gray-100 border-y border-gray-200">
                        <li class="py-3 text-sm font-medium text-gray-900">2x2 Picture</li>
                    </ul>
                </aside>
            </div>
        </div>
    </div>
</div>

<x-tutorial :steps="$steps" />
