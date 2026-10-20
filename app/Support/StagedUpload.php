<?php

namespace App\Support;

use App\Models\ScholarshipRequiredDocument;
use App\Models\User;
use finfo;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * A document a student sent straight to storage before submitting their application.
 *
 * Vercel turns away request bodies over 4.5 MB, so applicants' files travel
 * from the browser to Supabase Storage through a presigned PUT, under a
 * staging prefix, and the application form carries only this upload's token.
 * The token is encrypted and authenticated with the app key, so it cannot be
 * pointed at another student's file or another document. A presigned PUT
 * enforces neither size nor type, so the stored object itself is checked
 * before an application accepts it; uploads that are never submitted are
 * removed by uploads:prune-staged.
 */
class StagedUpload
{
    public const DISK = 'supabase';

    public const PREFIX = 'scholarship-applications/staging';

    /**
     * Matches the max:5120 (kilobytes) the regular file upload is held to.
     */
    public const MAX_BYTES = 5 * 1024 * 1024;

    /**
     * The file types an applicant may send, keyed by the MIME type their content reveals.
     *
     * @var array<string, string>
     */
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/pdf' => 'pdf',
    ];

    /**
     * How long a student has, after uploading, to submit the application.
     */
    private const TOKEN_LIFETIME_MINUTES = 120;

    /**
     * How long the browser has to start sending the file once it has the upload link.
     */
    private const UPLOAD_URL_LIFETIME_MINUTES = 15;

    public function __construct(
        public readonly string $path,
        public readonly int $userId,
        public readonly int $requiredDocumentId,
        public readonly string $originalName,
        public readonly int $expiresAt,
    ) {}

    /**
     * Reserve a staging path for one student's copy of one required document.
     */
    public static function begin(User $user, ScholarshipRequiredDocument $document, string $originalName): self
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        return new self(
            path: self::PREFIX."/{$user->id}/".Str::random(40).".{$extension}",
            userId: $user->id,
            requiredDocumentId: $document->id,
            originalName: $originalName,
            expiresAt: now()->addMinutes(self::TOKEN_LIFETIME_MINUTES)->getTimestamp(),
        );
    }

    /**
     * Read an upload back from the token the application form carried, or null when it was tampered with.
     */
    public static function fromToken(string $token): ?self
    {
        try {
            $data = Crypt::decrypt($token);
        } catch (DecryptException) {
            return null;
        }

        if (! is_array($data) || ! isset($data['path'], $data['user'], $data['document'], $data['name'], $data['expires'])) {
            return null;
        }

        return new self($data['path'], $data['user'], $data['document'], $data['name'], $data['expires']);
    }

    public function token(): string
    {
        return Crypt::encrypt([
            'path' => $this->path,
            'user' => $this->userId,
            'document' => $this->requiredDocumentId,
            'name' => $this->originalName,
            'expires' => $this->expiresAt,
        ]);
    }

    /**
     * The presigned PUT the browser sends the file to, and the headers it must send with it.
     *
     * Host is dropped from the headers: browsers refuse to set it, and the URL already implies it.
     *
     * @return array{url: string, headers: array<string, mixed>}
     */
    public function uploadUrl(): array
    {
        ['url' => $url, 'headers' => $headers] = Storage::disk(self::DISK)->temporaryUploadUrl(
            $this->path,
            now()->addMinutes(self::UPLOAD_URL_LIFETIME_MINUTES),
        );

        return ['url' => $url, 'headers' => Arr::except($headers, ['Host', 'host'])];
    }

    /**
     * Whether this upload was made by the given student for the given document and can still be submitted.
     */
    public function isFor(User $user, ScholarshipRequiredDocument $document): bool
    {
        return $this->userId === $user->id
            && $this->requiredDocumentId === $document->id
            && $this->expiresAt > now()->getTimestamp()
            && str_starts_with($this->path, self::PREFIX."/{$user->id}/");
    }

    /**
     * The stored file's real type as an extension, or null when it is missing, empty,
     * over the size limit, or not a JPG, PNG or PDF.
     *
     * The type is read from the file's first bytes, as the mimes rule does for an
     * ordinary upload, never from the name the browser gave it.
     */
    public function verifiedExtension(): ?string
    {
        $disk = Storage::disk(self::DISK);

        try {
            $size = $disk->size($this->path);
        } catch (Throwable) {
            return null;
        }

        if ($size < 1 || $size > self::MAX_BYTES) {
            return null;
        }

        $stream = $disk->readStream($this->path);

        if (! is_resource($stream)) {
            return null;
        }

        $head = (string) fread($stream, 2048);
        fclose($stream);

        return self::TYPES[(new finfo(FILEINFO_MIME_TYPE))->buffer($head)] ?? null;
    }

    /**
     * Move the file out of staging into the application's own folder and return its new path.
     */
    public function moveInto(string $directory, string $extension): string
    {
        $destination = $directory.'/'.Str::random(40).".{$extension}";

        if (! Storage::disk(self::DISK)->move($this->path, $destination)) {
            throw new RuntimeException("Could not move the staged upload {$this->path}.");
        }

        return $destination;
    }
}
