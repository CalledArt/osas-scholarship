<?php

namespace App\Http\Requests;

use App\Models\ScholarshipApplication;
use App\Rules\StagedDocumentUpload;
use App\Support\StagedUpload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScholarshipApplicationRequest extends FormRequest
{
    /**
     * The staged uploads that passed validation, with their real extensions, keyed by required document.
     *
     * @var array<int, array{0: StagedUpload, 1: string}>
     */
    private array $acceptedUploads = [];

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
     * Only documents are accepted here. Applicant identity (name, age,
     * course, etc.) is never read from user input — it is pulled directly
     * from the authenticated user record so it cannot be spoofed.
     *
     * Each document arrives one of two ways: as the token of a file the
     * browser already sent straight to storage (uploads.*), which is how the
     * form normally works, or as an ordinary file upload (documents.*), which
     * is what a browser without JavaScript sends.
     *
     * The student must also certify that the documents are genuine. Every
     * submitted application has been certified, so its submitted_at doubles
     * as the time of certification.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = ['certification' => ['accepted']];

        foreach ($this->route('scholarship')->requiredDocuments as $document) {
            $rules["documents.{$document->id}"] = ["required_without:uploads.{$document->id}", 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
            $rules["uploads.{$document->id}"] = [
                "required_without:documents.{$document->id}",
                'string',
                new StagedDocumentUpload(
                    $this->user(),
                    $document,
                    function (StagedUpload $upload, string $extension) use ($document): void {
                        $this->acceptedUploads[$document->id] = [$upload, $extension];
                    },
                ),
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'certification.accepted' => __('Certify that your documents are true and authentic before submitting.'),
        ];
    }

    /**
     * The staged upload accepted for a required document, with its real extension, if one was sent.
     *
     * @return array{0: StagedUpload, 1: string}|null
     */
    public function acceptedUpload(int $requiredDocumentId): ?array
    {
        return $this->acceptedUploads[$requiredDocumentId] ?? null;
    }
}
