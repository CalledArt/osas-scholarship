<x-app-layout :sheet="true">
    <div class="px-6 py-10 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <a href="{{ route('admin.students.index') }}" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-cvsu-green focus-visible:ring-offset-2">{{ __('Back to students') }}</a>

            <h1 class="mt-4 text-xl font-semibold leading-tight text-gray-900">{{ $student->name }}</h1>

            <p class="mt-2 max-w-2xl text-lg leading-relaxed text-gray-600">
                {{ __('Correct this student\'s details. Their scholarship applications use what is saved here.') }}
            </p>

            @if (session('status'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-6 space-y-8">
                @csrf
                @method('PUT')

                <section class="space-y-5">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Name') }}</h2>

                    <div class="grid gap-4 sm:grid-cols-3">
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

                        <div>
                            <x-input-label for="last_name" :value="__('Last name')" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $student->last_name)" required maxlength="255" />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                        </div>
                    </div>
                </section>

                <section class="space-y-5 border-t border-gray-200 pt-8">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Enrollment') }}</h2>

                    <div>
                        <x-input-label for="email" :value="__('CvSU email')" />
                        <x-text-input id="email" type="email" class="mt-1 block w-full bg-gray-50 text-gray-500" :value="$student->email" disabled />
                        <p class="mt-1 text-xs text-gray-500">{{ __('The student signs in with this Google account, so it cannot be changed here.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="student_number" :value="__('Student number')" />
                        <x-text-input id="student_number" name="student_number" type="text" class="mt-1 block w-full" :value="old('student_number', $student->student_number)" required maxlength="255" placeholder="202212345" />
                        <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-2">
                            <x-input-label for="course" :value="__('Course')" />
                            @php($selectedCourse = old('course', $student->course))
                            <select id="course" name="course" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
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

                        <div>
                            <x-input-label for="year_level" :value="__('Year level')" />
                            <select id="year_level" name="year_level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                                <option value="">{{ __('Select year level') }}</option>
                                @foreach (range(1, 6) as $year)
                                    <option value="{{ $year }}" @selected((string) old('year_level', $student->year_level) === (string) $year)>{{ __('Year :year', ['year' => $year]) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="program_years" :value="__('Program length')" />
                        <select id="program_years" name="program_years" aria-describedby="program_years-hint" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green sm:w-1/3" required>
                            <option value="">{{ __('Select program length') }}</option>
                            @foreach (range(2, 6) as $years)
                                <option value="{{ $years }}" @selected((string) old('program_years', $student->program_years) === (string) $years)>{{ __(':years years', ['years' => $years]) }}</option>
                            @endforeach
                        </select>
                        <p id="program_years-hint" class="mt-1 text-sm text-gray-500">
                            {{ __('RA 10931 covers the program’s length plus one year.') }}
                            @if ($student->yearOfStudy() !== null && $student->freeTertiaryEducationYears() !== null)
                                {{ __('This student is in year :year of :covered.', ['year' => $student->yearOfStudy(), 'covered' => $student->freeTertiaryEducationYears()]) }}
                                @unless ($student->hasFreeTertiaryEducation())
                                    <span class="font-semibold text-gray-900">{{ __('Their free tuition has ended.') }}</span>
                                @endunless
                            @endif
                        </p>
                        <x-input-error :messages="$errors->get('program_years')" class="mt-2" />
                    </div>
                </section>

                <section class="space-y-5 border-t border-gray-200 pt-8">
                    <h2 class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Personal details') }}</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="date_of_birth" :value="__('Date of birth')" />
                            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $student->date_of_birth?->format('Y-m-d'))" :max="now()->subDay()->toDateString()" required />
                            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="contact_number" :value="__('Contact number')" />
                            <x-text-input id="contact_number" name="contact_number" type="tel" class="mt-1 block w-full" :value="old('contact_number', $student->contact_number)" required maxlength="255" />
                            <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="address" :value="__('Address')" />
                        <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $student->address)" required maxlength="255" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                    <a href="{{ route('admin.students.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
