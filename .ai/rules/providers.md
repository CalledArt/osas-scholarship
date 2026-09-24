---
paths:
  - 'app/Providers/**'
---

# Providers

## Eloquent loading: autoload in production, strict everywhere else
AppServiceProvider::boot() sets `Model::automaticallyEagerLoadRelationships($this->app->isProduction())` and `Model::preventLazyLoading(! $this->app->isProduction())`. Do not enable both at once: `getRelationValue()` calls `attemptToAutoloadRelation()` before it checks `preventsLazyLoading`, so with autoloading on the lazy-loading guard is unreachable and never throws. Split by environment, a forgotten `with()` fails loudly in dev and tests while production absorbs it as one batched query instead of one round trip per row (~240ms each against Supabase — see controllers.md).

Also framework behaviour, not a bug: a single-row fetch (`find()`, `first()`) never throws a lazy-loading violation. `Builder::hydrate()` only sets `preventsLazyLoading` on models when the result had more than one row, since one row cannot be an N+1.
