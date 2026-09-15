<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Create your account') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Register with your CvSU student email to apply for scholarships.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ loading: false, showPassword: false }" @submit="loading = true" class="space-y-5">
        @csrf

        <!-- Name -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <x-input-label for="first_name" :value="__('First name')" />
                <x-text-input id="first_name" class="mt-1 block w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="middle_name" :value="__('Middle name')" />
                <x-text-input id="middle_name" class="mt-1 block w-full" type="text" name="middle_name" :value="old('middle_name')" autocomplete="additional-name" />
                <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last name')" />
                <x-text-input id="last_name" class="mt-1 block w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('CvSU email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@cvsu.edu.ph" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Student number -->
        <div>
            <x-input-label for="student_number" :value="__('Student number')" />
            <x-text-input id="student_number" class="mt-1 block w-full" type="text" name="student_number" :value="old('student_number')" required placeholder="202200976" />
            <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
        </div>

        <!-- Course & year level -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <x-input-label for="course" :value="__('Course')" />
                <x-text-input id="course" class="mt-1 block w-full" type="text" name="course" :value="old('course')" placeholder="BS Computer Science" />
                <x-input-error :messages="$errors->get('course')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="year_level" :value="__('Year level')" />
                <select id="year_level" name="year_level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-cvsu-green focus:ring-cvsu-green">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (range(1, 6) as $level)
                        <option value="{{ $level }}" @selected(old('year_level') == $level)>{{ $level }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('year_level')" class="mt-2" />
            </div>
        </div>

        <!-- Contact number & date of birth -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="contact_number" :value="__('Contact number')" />
                <x-text-input id="contact_number" class="mt-1 block w-full" type="text" name="contact_number" :value="old('contact_number')" autocomplete="tel" placeholder="09XXXXXXXXX" />
                <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="date_of_birth" :value="__('Date of birth')" />
                <x-text-input id="date_of_birth" class="mt-1 block w-full" type="date" name="date_of_birth" :value="old('date_of_birth')" autocomplete="bday" />
                <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
            </div>
        </div>

        <!-- Address -->
        <div>
            <x-input-label for="address" :value="__('Address')" />
            <x-text-input id="address" class="mt-1 block w-full" type="text" name="address" :value="old('address')" autocomplete="street-address" />
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <div class="relative mt-1">
                    <x-text-input id="password" class="block w-full pr-10"
                                    type="password"
                                    x-bind:type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    required autocomplete="new-password" />
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" tabindex="-1">
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.774 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="mt-1 block w-full"
                                type="password"
                                x-bind:type="showPassword ? 'text' : 'password'"
                                name="password_confirmation"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <button type="submit" x-bind:disabled="loading" class="flex w-full items-center justify-center gap-2 rounded-lg bg-cvsu-green px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cvsu-green-dark focus:outline-none focus:ring-2 focus:ring-cvsu-gold focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
            <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span x-text="loading ? '{{ __('Creating account...') }}' : '{{ __('Create account') }}'"></span>
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-gray-500">
        {{ __('Already have an account?') }}
        <a class="font-medium text-cvsu-green hover:text-cvsu-green-dark" href="{{ route('login') }}">{{ __('Sign in') }}</a>
    </p>
</x-guest-layout>
