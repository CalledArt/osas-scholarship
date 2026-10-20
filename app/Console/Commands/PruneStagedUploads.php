<?php

namespace App\Console\Commands;

use App\Support\StagedUpload;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\StorageAttributes;

/**
 * Deletes documents students sent straight to storage but never submitted.
 *
 * A submitted application moves its files out of staging, so whatever is
 * still there after a day was abandoned: a student who changed a file, left
 * the form, or ran past the token's lifetime. Nothing on Vercel runs the
 * scheduler, so run this by hand or from a cron that can reach artisan.
 */
#[Signature('uploads:prune-staged {--hours=24 : Delete staged files older than this many hours}')]
#[Description('Delete staged application uploads that were never submitted')]
class PruneStagedUploads extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subHours((int) $this->option('hours'))->getTimestamp();
        $disk = Storage::disk(StagedUpload::DISK);

        $abandoned = $disk->listContents(StagedUpload::PREFIX, true)
            ->filter(fn (StorageAttributes $item): bool => $item->isFile() && $item->lastModified() !== null && $item->lastModified() < $cutoff)
            ->map(fn (StorageAttributes $item): string => $item->path())
            ->toArray();

        if ($abandoned !== []) {
            $disk->delete($abandoned);
        }

        $this->components->info(trans_choice('Deleted :count abandoned upload.|Deleted :count abandoned uploads.', count($abandoned), ['count' => count($abandoned)]));

        return self::SUCCESS;
    }
}
