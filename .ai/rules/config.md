---
paths:
  - config/queue.php
---

# Config

## No database queue: jobs tables were dropped
Nothing dispatches jobs and Vercel runs no worker, so QUEUE_CONNECTION is sync everywhere (.env, .env.example, Dockerfile.vercel) and the jobs, job_batches and failed_jobs tables were dropped (2026_10_04 drop_unused_queue_tables). The sync queue never writes failed_jobs — only queue:work does. If queued work is ever added, recreate the tables by rolling back or re-adding that migration's down() schema, and set up a worker first. Keep sessions/cache/cache_locks: production uses the database session and cache drivers.
