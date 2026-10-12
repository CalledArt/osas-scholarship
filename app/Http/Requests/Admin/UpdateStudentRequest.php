<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\CvsuPrograms;
use App\Support\PersonName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Only student records are edited here; staff accounts are not.
     */
    public function authorize(): bool
    {
        return $this->route('student')->isStudent();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The email is left out, so any email sent is ignored: it belongs to the
     * student's verified Google identity. A course saved before the program
     * list existed stays accepted, so correcting other details doesn't force
     * OSAS to recode it.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_number' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($this->route('student'))],
            'course' => ['required', 'string', Rule::in(array_filter([...CvsuPrograms::all(), $this->route('student')->course]))],
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
                $validator->errors()->add('last_name', __('The full name must not exceed 255 characters.'));
            }
        }];
    }
}
