<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * The only email domain allowed to register.
     *
     * Registration is open to anyone who reaches the page, so the school
     * domain is what keeps the roll to actual CvSU students — it is the
     * identity check standing in for a verification email.
     */
    public const ALLOWED_EMAIL_DOMAIN = '@cvsu.edu.ph';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'ends_with:'.self::ALLOWED_EMAIL_DOMAIN,
                Rule::unique(User::class),
            ],
            'student_number' => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'course' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.ends_with' => 'Please register with your CvSU student email ('.self::ALLOWED_EMAIL_DOMAIN.').',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
        }
    }
}
