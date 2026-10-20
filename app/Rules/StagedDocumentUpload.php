<?php

namespace App\Rules;

use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use App\Support\StagedUpload;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Accepts the token of a document the student already sent straight to storage.
 *
 * Passes only when the token is the student's own upload for this document,
 * has not expired, and the stored file really is a JPG, PNG or PDF within
 * the size limit. Each accepted upload is handed to $accepted with its real
 * extension, so the controller does not read the file from storage again.
 */
class StagedDocumentUpload implements ValidationRule
{
    /**
     * @param  Closure(StagedUpload, string): void  $accepted
     */
    public function __construct(
        private User $student,
        private ScholarshipRequiredDocument $document,
        private Closure $accepted,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $upload = is_string($value) ? StagedUpload::fromToken($value) : null;

        if ($upload === null || ! $upload->isFor($this->student, $this->document)) {
            $fail(__('This upload has expired. Choose the file again.'));

            return;
        }

        $extension = $upload->verifiedExtension();

        if ($extension === null) {
            $fail(__('The uploaded file must be a JPG, PNG or PDF of at most 5 MB.'));

            return;
        }

        ($this->accepted)($upload, $extension);
    }
}
