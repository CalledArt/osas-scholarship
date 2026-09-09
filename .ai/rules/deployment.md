---
paths:
  - Dockerfile.vercel
  - vercel.json
  - Caddyfile
  - .dockerignore
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
