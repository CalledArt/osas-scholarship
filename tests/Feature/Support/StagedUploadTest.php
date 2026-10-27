<?php

namespace Tests\Feature\Support;

use App\Support\StagedUpload;
use Aws\CommandInterface;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StagedUploadTest extends TestCase
{
    /**
     * Supabase Storage's S3 API has no ACLs, so a move that first reads the
     * source's visibility (Flysystem's default) fails before anything is copied.
     */
    public function test_moving_a_staged_upload_succeeds_on_storage_without_acl_support(): void
    {
        $sentCommands = [];
        Storage::set(StagedUpload::DISK, Storage::build([
            ...config('filesystems.disks.'.StagedUpload::DISK),
            'key' => 'test-key',
            'secret' => 'test-secret',
            'region' => 'ap-northeast-1',
            'bucket' => 'documents',
            'endpoint' => 'https://storage.test/storage/v1/s3',
            'handler' => function (CommandInterface $command) use (&$sentCommands): PromiseInterface {
                $sentCommands[] = $command->getName();

                return match ($command->getName()) {
                    'GetObjectAcl' => Create::rejectionFor(new S3Exception('Error parsing response for GetObjectAcl', $command)),
                    'HeadObject' => Create::promiseFor(new Result(['ContentLength' => 1024])),
                    default => Create::promiseFor(new Result),
                };
            },
        ]));
        $upload = new StagedUpload(
            path: StagedUpload::PREFIX.'/21/grades.png',
            userId: 21,
            requiredDocumentId: 3,
            originalName: 'grades.png',
            expiresAt: now()->addHour()->getTimestamp(),
        );

        $path = $upload->moveInto('scholarship-applications/9', 'png');

        $this->assertMatchesRegularExpression('#^scholarship-applications/9/\w{40}\.png$#', $path);
        $this->assertContains('CopyObject', $sentCommands);
        $this->assertContains('DeleteObject', $sentCommands);
        $this->assertNotContains('GetObjectAcl', $sentCommands);
    }
}
