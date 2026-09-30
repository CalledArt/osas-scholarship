---
paths:
  - bootstrap/app.php
---

# Bootstrap

## Behind Vercel, trust proxies
Vercel terminates TLS and forwards plain HTTP into the container, so `trustProxies(at: '*')` in bootstrap/app.php is load-bearing: without it Laravel thinks the request is http://, emits http asset URLs the browser blocks as mixed content, and drops the secure session cookie. Nothing reaches the container except through Vercel, so the forwarded headers can be taken at face value.

## Production sets APP_DEBUG=false outside .env
Dockerfile.vercel sets `APP_ENV=production` and `APP_DEBUG=false` as image defaults rather than relying on a .env file, which never reaches the image. That is deliberate: with debug on, Laravel's error page renders the environment, including the Supabase DB_URL and its password, to anyone who triggers an error on a public URL.

## Vercel .env.local can shadow Laravel development configuration
Vercel CLI may create .env.local containing only its OIDC token. With APP_ENV=local, the default php artisan serve child selects that file and loses APP_KEY and other .env settings, returning HTTP 500. Start the local server with php artisan serve --no-reload to preserve the complete environment loaded by the parent. Do not rotate APP_KEY to fix this configuration-selection issue.
