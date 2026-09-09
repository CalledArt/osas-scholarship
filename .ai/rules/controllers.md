---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Every database round trip costs ~240ms — count queries, not milliseconds
The database is hosted on Supabase in ap-northeast-1 and reached through the pooler. A warm `select 1` measures ~240ms; opening a connection costs ~950ms. Page time is therefore (number of round trips) x 240ms, and almost nothing else — a DB-free page renders in 15ms.

So the only optimisation that moves the needle is removing round trips. Prefer one joined query over two eager loads, and reuse a collection you already loaded instead of re-querying it (the admin dashboard resolves recent applications' scholarships from the register it already holds).

Measured: admin dashboard went 1.93s -> 0.75s by dropping 2 queries, enabling DB_PERSISTENT, and unsetting the pgsql `charset`/`search_path` config keys (each of those made Laravel's connector run an extra SET statement per connection — one full round trip).

Caching is not the lever here: at 240ms per query the file cache already removes ~99.6% of the cost, so Redis would buy under 1ms over it and is not worth a dependency.
