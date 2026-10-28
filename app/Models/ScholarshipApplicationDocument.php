<?php

namespace App\Models;

use Database\Factories\ScholarshipApplicationDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Fillable(['scholarship_required_document_id', 'disk', 'path', 'original_name'])]
class ScholarshipApplicationDocument extends Model
{
    /** @use HasFactory<ScholarshipApplicationDocumentFactory> */
    use HasFactory;

    public function application(): BelongsTo
    {
        return $this->belongsTo(ScholarshipApplication::class, 'scholarship_application_id');
    }

    public function requiredDocument(): BelongsTo
    {
        return $this->belongsTo(ScholarshipRequiredDocument::class, 'scholarship_required_document_id');
    }

    /**
     * Whether the uploaded file is still in storage. Laravel only finds out a
     * file is gone once the download has started streaming, too late to send
     * anything but a broken response, so the controller asks first.
     */
    public function isStored(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function download(): StreamedResponse
    {
        return Storage::disk($this->disk)->download($this->path, $this->original_name);
    }
}
