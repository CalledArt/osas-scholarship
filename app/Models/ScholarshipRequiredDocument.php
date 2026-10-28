<?php

namespace App\Models;

use Database\Factories\ScholarshipRequiredDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Fillable(['name', 'description', 'sample_disk', 'sample_path', 'sample_original_name'])]
class ScholarshipRequiredDocument extends Model
{
    /** @use HasFactory<ScholarshipRequiredDocumentFactory> */
    use HasFactory;

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    /**
     * Whether OSAS has posted an example of this document for applicants.
     */
    public function hasSample(): bool
    {
        return $this->sample_path !== null;
    }

    /**
     * Store an example of this document and save the model pointing at it.
     *
     * On an unsaved document this is its single insert. A sample it replaces
     * is deleted from storage only once the new one is saved.
     */
    public function attachSample(UploadedFile $file): void
    {
        $replacedSample = $this->hasSample() ? clone $this : null;

        $this->fill([
            'sample_disk' => 'supabase',
            'sample_path' => $file->store("scholarship-samples/{$this->scholarship_id}", 'supabase'),
            'sample_original_name' => $file->getClientOriginalName(),
        ])->save();

        $replacedSample?->deleteSampleFile();
    }

    /**
     * Take down this document's sample and delete its file.
     */
    public function removeSample(): void
    {
        $removedSample = clone $this;

        $this->update([
            'sample_disk' => null,
            'sample_path' => null,
            'sample_original_name' => null,
        ]);

        $removedSample->deleteSampleFile();
    }

    /**
     * Delete the sample's file from storage, leaving the columns untouched.
     */
    public function deleteSampleFile(): void
    {
        if ($this->hasSample()) {
            Storage::disk($this->sample_disk)->delete($this->sample_path);
        }
    }

    /**
     * Whether the sample is a picture rather than a PDF, which decides how it is previewed.
     */
    public function sampleIsImage(): bool
    {
        return in_array(strtolower(pathinfo($this->sample_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true);
    }

    /**
     * Whether the sample's file is still in storage, asked before streaming it
     * (see ScholarshipApplicationDocument::isStored()).
     */
    public function sampleIsStored(): bool
    {
        return $this->hasSample() && Storage::disk($this->sample_disk)->exists($this->sample_path);
    }

    /**
     * Stream the sample: "inline" to preview it in the browser, "attachment" to save a copy.
     */
    public function sampleResponse(string $disposition = 'inline'): StreamedResponse
    {
        return Storage::disk($this->sample_disk)->response($this->sample_path, $this->sample_original_name, [], $disposition);
    }
}
