<?php

namespace App\Http\Requests\Admin;

use App\Models\Scholarship;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScholarshipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Scholarship::class);
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
            'deadline' => ['nullable', 'date', 'after:today'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*.name' => ['required', 'string', 'max:255'],
            'required_documents.*.description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
