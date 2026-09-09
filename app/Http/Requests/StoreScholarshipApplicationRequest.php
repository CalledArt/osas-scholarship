<?php

namespace App\Http\Requests;

use App\Models\ScholarshipApplication;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScholarshipApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [ScholarshipApplication::class, $this->route('scholarship')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Only document uploads are accepted here. Applicant identity (name, age,
     * course, etc.) is never read from user input — it is pulled directly
     * from the authenticated user record so it cannot be spoofed.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->route('scholarship')->requiredDocuments as $document) {
            $rules["documents.{$document->id}"] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        return $rules;
    }
}
