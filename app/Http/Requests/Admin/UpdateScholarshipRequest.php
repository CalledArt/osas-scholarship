<?php

namespace App\Http\Requests\Admin;

use App\Enums\ScholarshipStatus;
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'slots' => ['required', 'integer', 'min:1'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', new Enum(ScholarshipStatus::class)],
        ];
    }
}
