---
paths:
  - config/queue.php
  - config/filesystems.php
---

# Config

## No database queue: jobs tables were dropped
Nothing dispatches jobs and Vercel runs no worker, so QUEUE_CONNECTION is sync everywhere (.env, .env.example, Dockerfile.vercel) and the jobs, job_batches and failed_jobs tables were dropped (2026_10_04 drop_unused_queue_tables). The sync queue never writes failed_jobs — only queue:work does. If queued work is ever added, recreate the tables by rolling back or re-adding that migration's down() schema, and set up a worker first. Keep sessions/cache/cache_locks: production uses the database session and cache drivers.

## The supabase disk needs retain_visibility => false
Supabase Storage's S3 API has no ACLs: GetObjectAcl returns a non-XML body the AWS SDK cannot parse. Flysystem's copy/move reads the source's visibility first unless retain_visibility is false, so without it every move/copy on the disk fails (StagedUpload::moveInto threw "Could not move the staged upload", seen 2026-10-21). Disk 'throw' => false hides the cause; build the disk with throw => true to see it. Also never call visibility()/setVisibility() on this disk. Storage::fake() skips S3, so StagedUploadTest drives the real S3 adapter with a stub handler.
