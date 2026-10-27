<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * A student's email is left out entirely, so any email sent is ignored:
     * it belongs to their verified Google identity. Letting a student rewrite
     * it could let them claim a classmate's email and block registration.
     *
     * A student also keeps their own personal details (date of birth, contact
     * number and address) here, since OSAS can neither see nor edit them.
     * Each may be left out of a request, but never cleared.
     *
     * Staff change their password on the same form, under its one Save:
     * left blank it stays as it is; filled in, it needs the current one.
     * Students sign in with Google, so a password they send is ignored.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ];

        if ($this->user()->isStudent()) {
            return [
                ...$rules,
                'date_of_birth' => ['sometimes', 'required', 'date_format:Y-m-d', 'before:today'],
                'contact_number' => ['sometimes', 'required', 'string', 'digits:11'],
                'address' => ['sometimes', 'required', 'string', 'max:255'],
            ];
        }

        return [
            ...$rules,
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', Password::defaults(), 'confirmed'],
        ];
    }
}
