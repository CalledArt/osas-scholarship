<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\CvsuPrograms;
use App\Support\PersonName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StudentRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

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
            'student_number' => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'course' => ['required', 'string', Rule::in(CvsuPrograms::all())],
            'year_level' => ['required', 'integer', 'between:1,6'],
            'program_years' => ['required', 'integer', 'between:2,6'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'contact_number' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'program_years' => 'program length',
        ];
    }

    /**
     * Keep the assembled name within the existing users.name column limit.
     *
     * @return array<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['first_name', 'middle_name', 'last_name'])) {
                return;
            }

            $fullName = PersonName::assemble(
                $this->input('first_name'),
                $this->input('middle_name'),
                $this->input('last_name'),
            );

            if (mb_strlen($fullName) > 255) {
                $validator->errors()->add('last_name', __('Your full name must not exceed 255 characters.'));
            }
        }];
    }
}
