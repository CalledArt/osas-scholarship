<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Complete your registration') }}</h2>
        <p class="mt-2 text-sm text-gray-500">{{ __('Your CvSU Google account is verified. Enter your student details to create your scholarship account.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <x-input-error :messages="$errors->get('registration')" />

        <div>
            <x-input-label for="email" :value="__('Verified CvSU email')" />
            <x-text-input id="email" type="email" class="mt-1 block w-full bg-gray-50 text-gray-500" :value="$email" readonly />
            <p class="mt-1 text-xs text-gray-500">{{ __('This email comes from Google and cannot be changed here.') }}</p>
        </div>

        <div>
            <x-input-label for="first_name" :value="__('First name')" />
            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $nameParts['first_name'])" required autofocus autocomplete="given-name" maxlength="255" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="middle_name" :value="__('Middle name (optional)')" />
            <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full" :value="old('middle_name', $nameParts['middle_name'])" autocomplete="additional-name" maxlength="255" />
            <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="last_name" :value="__('Last name')" />
            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $nameParts['last_name'])" required autocomplete="family-name" maxlength="255" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="date_of_birth" :value="__('Date of birth')" />
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth')" :max="now()->subDay()->toDateString()" required autocomplete="bday" />
            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="student_number" :value="__('Student number')" />
            <x-text-input id="student_number" name="student_number" type="text" class="mt-1 block w-full" :value="old('student_number')" required maxlength="255" placeholder="202212345" />
            <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="course" :value="__('Course / program')" />
            <select id="course" name="course" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                <option value="">{{ __('Select your course') }}</option>
                @foreach (\App\Support\CvsuPrograms::BY_COLLEGE as $college => $programs)
                    <optgroup label="{{ $college }}">
                        @foreach ($programs as $program)
                            <option value="{{ $program }}" @selected(old('course') === $program)>{{ $program }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('course')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="year_level" :value="__('Year level')" />
            <select id="year_level" name="year_level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                <option value="">{{ __('Select your year level') }}</option>
                @foreach (range(1, 6) as $year)
                    <option value="{{ $year }}" @selected((string) old('year_level') === (string) $year)>{{ __('Year :year', ['year' => $year]) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="program_years" :value="__('Program length')" />
            <select id="program_years" name="program_years" aria-describedby="program_years-hint" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green" required>
                <option value="">{{ __('Select your program’s length') }}</option>
                @foreach (range(2, 6) as $years)
                    <option value="{{ $years }}" @selected((string) old('program_years') === (string) $years)>{{ __(':years years', ['years' => $years]) }}</option>
                @endforeach
            </select>
            <p id="program_years-hint" class="mt-1 text-sm text-gray-500">{{ __('How many years your course takes to finish, such as 4 for most bachelor’s programs.') }}</p>
            <x-input-error :messages="$errors->get('program_years')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="contact_number" :value="__('Contact number')" />
            <x-text-input id="contact_number" name="contact_number" type="tel" class="mt-1 block w-full" :value="old('contact_number')" required autocomplete="tel" maxlength="255" />
            <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="address" :value="__('Address')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" required autocomplete="street-address" maxlength="255" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">{{ __('Create student account') }}</x-primary-button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-medium text-cvsu-green underline hover:text-cvsu-green-dark">{{ __('Back to sign in') }}</a>
        </p>
    </form>
</x-guest-layout>
