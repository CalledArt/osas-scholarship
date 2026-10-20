<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScholarshipStatus;
use App\Enums\ScholarshipType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateScholarshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('scholarship'));
    }

    /**
     * Prepare the data for validation.
     *
     * An unticked checkbox sends nothing, so the course limit is read as a
     * boolean up front; otherwise unticking it could never switch it off.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['stem_courses_only' => $this->boolean('stem_courses_only')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', new Enum(ScholarshipType::class)],
            'stem_courses_only' => ['boolean'],
            'slots' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', new Enum(ScholarshipStatus::class)],
        ];
    }
}
