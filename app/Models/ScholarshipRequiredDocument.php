<?php

namespace App\Models;

use Database\Factories\ScholarshipRequiredDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'description'])]
class ScholarshipRequiredDocument extends Model
{
    /** @use HasFactory<ScholarshipRequiredDocumentFactory> */
    use HasFactory;

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}
