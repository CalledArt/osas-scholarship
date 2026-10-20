<?php

namespace App\Http\Requests;

use App\Models\ScholarshipApplication;
use App\Support\StagedUpload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScholarshipApplicationUploadRequest extends FormRequest
{
    /**
     * Only a student who could apply right now may upload documents for it.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [ScholarshipApplication::class, $this->route('scholarship')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The name and size are what the browser reports before sending the file.
     * They only decide whether an upload link is issued; the stored file is
     * checked again for real when the application is submitted.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'required_document_id' => ['required', 'integer', Rule::in($this->route('scholarship')->requiredDocuments->modelKeys())],
            'name' => ['required', 'string', 'max:255', 'regex:/\.(jpe?g|png|pdf)$/i'],
            'size' => ['required', 'integer', 'min:1', 'max:'.StagedUpload::MAX_BYTES],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => __('Choose a JPG, PNG or PDF file.'),
            'size.max' => __('This file is larger than 5 MB.'),
        ];
    }
}
