---
paths:
  - Dockerfile.vercel
  - vercel.json
  - Caddyfile
  - .dockerignore
  - '.env*'
---

# Vercel deployment

## Never cache config in the image
`php artisan config:cache`, and the `optimize` bundle that includes it, resolve `env()` at the moment they run. In a Dockerfile that is build time, so an empty `APP_KEY` and `DB_URL` get frozen into the image and the runtime values Vercel injects are never read. Only `event:cache` and `view:cache` are safe to run at build.

## The Vercel guide's array session and cache drivers break this app
The KB Dockerfile ships `SESSION_DRIVER=array` and `CACHE_STORE=array`. Every route here is behind `auth`, and an array session is discarded when the request ends — nobody can stay logged in and every POST fails CSRF with a 419. Use `database` for both; the `sessions` and `cache` tables already exist in the first migration.

## The asset build stage needs vendor/
`tailwind.config.js` scans `vendor/laravel/framework/.../Pagination/resources/views` for classes. Tailwind emits nothing for files it cannot see, so a Node stage without `vendor/` produces a build that succeeds and a paginator that renders unstyled. `.dockerignore` keeps `vendor/` out of the context deliberately, so the Node stage takes it from the composer stage with `COPY --from=vendor`.

## Deploy in hnd1, the region Supabase is in
Vercel Functions default to `iad1` (Washington DC) and Supabase is in `ap-northeast-1`. Per the round-trip cost documented in controllers.md, page time here is round trips x latency and almost nothing else, so the default region puts the Pacific in the middle of every query. `hnd1` is the same AWS region as the database. If `regions` in vercel.json is ever rejected, set it in Project Settings > Functions instead — do not just drop it.

## Migrations do not run at container start
Vercel starts instances concurrently, so an entrypoint migration races itself. Run `php artisan migrate --force` from a workstation against the session-mode port (`:5432`), separately from the deploy.

## Vercel uses the transaction pooler (6543), never DB_PERSISTENT
Session mode (:5432) caps clients at pool_size 15. With DB_PERSISTENT every PHP thread of every Vercel instance (frozen ones included) pins a slot forever; once 15 are held, requests hang or 500 with `EMAXCONNSESSION max clients reached in session mode` — seen in production 2026-09-24 as "login does nothing, works on the second click". Production DB_URL points at :6543 with DB_EMULATE_PREPARES=true and DB_PERSISTENT unset. The workstation-only ~950ms connect cost that motivated DB_PERSISTENT (controllers.md) does not apply in hnd1, next to the database. Migrations still go through :5432 from a workstation.

## Restart `composer run dev` fully after editing .env
`php artisan dev` loads .env once at startup, and its putenv makes those values real environment variables that the child `artisan serve` inherits. Laravel's dotenv never overwrites a variable already in the environment. So after an .env edit, the "Environment modified. Restarting server" message still leaves the web server on the OLD values. Seen 2026-10-06: emails kept going to the log after MAIL_MAILER was switched to smtp. Stop the whole dev command with Ctrl+C and start it again; `php artisan tinker` and other fresh processes are unaffected.

## Vercel rejects request bodies over 4.5 MB
Measured 2026-10-17: a 6 MB POST is refused with 413 FUNCTION_PAYLOAD_TOO_LARGE before Laravel runs; 3 MB gets through. So never post files through the app where their total can pass ~4.5 MB. Applicant documents go browser → Supabase Storage by presigned PUT (App\Support\StagedUpload). Admin sample uploads (validated to 5 MB) and Post Scholarship with several samples still post through the app and will fail above the limit until they move to the same flow.
