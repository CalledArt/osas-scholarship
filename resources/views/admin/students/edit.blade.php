{{--
    One student's details as OSAS corrects them, drawn like the other admin
    pages (asked 2026-10-25): the dashboards' banner headed by the student's
    name at their width, then one card whose sections (Name, Enrollment,
    Student status) put their title on the left from lg and spread their
    fields across the rest, with the save bar at the card's foot.
--}}
<x-app-layout :title="$student->surname_first_name" :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-[100rem]">
            <a href="{{ route('admin.students.index') }}" class="rounded font-medium text-graphite underline underline-offset-4 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Back to students') }}</a>

            <div class="mt-4">
                <x-welcome-banner :heading="mb_strtoupper($student->surname_first_name)">
                    {{ __('Correct this student\'s details. Their scholarship applications use what is saved here.') }}
                </x-welcome-banner>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-sheet border border-grant/30 bg-grant-wash px-4 py-3 text-grant-deep" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 overflow-hidden rounded-lg border border-rule bg-white">
                @csrf
                @method('PUT')

                <section aria-labelledby="name-heading" class="grid gap-x-8 gap-y-4 px-5 py-6 sm:px-6 lg:grid-cols-[10rem_minmax(0,1fr)]">
                    <h2 id="name-heading" class="text-lead font-semibold text-ink">{{ __('Name') }}</h2>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="last_name" :value="__('Last name')" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $student->last_name)" required maxlength="255" />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="first_name" :value="__('First name')" />
                            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $student->first_name)" required maxlength="255" />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="middle_name" :value="__('Middle name (optional)')" />
                            <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name', $student->middle_name)" maxlength="255" />
                            <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section aria-labelledby="enrollment-heading" class="grid gap-x-8 gap-y-4 border-t border-rule px-5 py-6 sm:px-6 lg:grid-cols-[10rem_minmax(0,1fr)]">
                    <h2 id="enrollment-heading" class="text-lead font-semibold text-ink">{{ __('Enrollment') }}</h2>

                    <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="sm:col-span-2">
                            <x-input-label for="email" :value="__('CvSU email')" />
                            <x-text-input id="email" type="email" class="mt-1 block w-full bg-linen-sunk text-slate" :value="$student->email" disabled />
                            <p class="mt-1  text-slate">{{ __('The student signs in with this Google account, so it cannot be changed here.') }}</p>
                        </div>

                        <div>
                            <x-input-label for="student_number" :value="__('Student number')" />
                            <x-text-input id="student_number" name="student_number" type="text" class="mt-1 block w-full" :value="old('student_number', $student->student_number)" required inputmode="numeric" pattern="[0-9]{9}" maxlength="9" x-data x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '')" title="{{ __('9 digits, numbers only') }}" placeholder="202212345" />
                            <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="year_level" :value="__('Year level')" />
                            <select id="year_level" name="year_level" class="mt-1 block w-full rounded-sheet border-rule-strong shadow-sm focus:border-seal focus:ring-seal" required>
                                <option value="">{{ __('Select year level') }}</option>
                                @foreach (range(1, 6) as $year)
                                    <option value="{{ $year }}" @selected((string) old('year_level', $student->year_level) === (string) $year)>{{ __('Year :year', ['year' => $year]) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="course" :value="__('Course')" />
                            @php($selectedCourse = old('course', $student->course))
                            <select id="course" name="course" class="mt-1 block w-full rounded-sheet border-rule-strong shadow-sm focus:border-seal focus:ring-seal" required>
                                <option value="">{{ __('Select course') }}</option>
                                @if ($student->course !== null && ! in_array($student->course, \App\Support\CvsuPrograms::all(), true))
                                    <option value="{{ $student->course }}" @selected($selectedCourse === $student->course)>{{ __(':course (not in the program list)', ['course' => $student->course]) }}</option>
                                @endif
                                @foreach (\App\Support\CvsuPrograms::BY_COLLEGE as $college => $programs)
                                    <optgroup label="{{ $college }}">
                                        @foreach ($programs as $program)
                                            <option value="{{ $program }}" @selected($selectedCourse === $program)>{{ $program }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('course')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="program_years" :value="__('Program length')" />
                            <select id="program_years" name="program_years" aria-describedby="program_years-hint" class="mt-1 block w-full rounded-sheet border-rule-strong shadow-sm focus:border-seal focus:ring-seal" required>
                                <option value="">{{ __('Select program length') }}</option>
                                @foreach (range(2, 6) as $years)
                                    <option value="{{ $years }}" @selected((string) old('program_years', $student->program_years) === (string) $years)>{{ __(':years years', ['years' => $years]) }}</option>
                                @endforeach
                            </select>
                            <p id="program_years-hint" class="mt-1  text-slate">
                                {{ __('RA 10931 covers the program’s length plus one year.') }}
                                @if ($student->yearOfStudy() !== null && $student->freeTertiaryEducationYears() !== null)
                                    {{ __('This student is in year :year of :covered.', ['year' => $student->yearOfStudy(), 'covered' => $student->freeTertiaryEducationYears()]) }}
                                    @if (! $student->isWithinFreeTertiaryEducationYears())
                                        <span class="font-semibold text-ink">{{ __('Their free tuition has ended.') }}</span>
                                    @elseif ($student->isOnLeaveOfAbsence())
                                        <span class="font-semibold text-ink">{{ __('Their free tuition is paused while they are on leave.') }}</span>
                                    @endif
                                @endif
                            </p>
                            <x-input-error :messages="$errors->get('program_years')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section aria-labelledby="status-heading" class="grid gap-x-8 gap-y-4 border-t border-rule px-5 py-6 sm:px-6 lg:grid-cols-[10rem_minmax(0,1fr)]">
                    <h2 id="status-heading" class="text-lead font-semibold text-ink">{{ __('Student status') }}</h2>

                    <div>
                        <x-input-label for="student_status" :value="__('Status')" />
                        <select id="student_status" name="student_status" aria-describedby="student_status-hint" class="mt-1 block w-full rounded-sheet border-rule-strong shadow-sm focus:border-seal focus:ring-seal sm:max-w-md" required>
                            @foreach (\App\Enums\StudentStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('student_status', $student->student_status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <p id="student_status-hint" class="mt-1 text-slate">
                            {{ __('While on leave of absence, a student\'s free tuition under RA 10931 is paused and they cannot apply to scholarships. Saving them as on leave voids their applications still waiting for a decision, freeing those slots for other students; setting them back to Active does not restore them. Scholarships already approved for them are kept.') }}
                        </p>
                        <x-input-error :messages="$errors->get('student_status')" class="mt-2" />
                    </div>
                </section>

                {{-- Personal details (date of birth, contact number, address) are the student's own: OSAS neither sees nor edits them here. --}}
                <div class="flex items-center justify-end gap-3 border-t border-rule bg-linen-sunk/60 px-5 py-4 sm:px-6">
                    <a href="{{ route('admin.students.index') }}" class="rounded font-medium text-slate hover:text-graphite focus:outline-none focus-visible:ring-2 focus-visible:ring-seal focus-visible:ring-offset-2">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
