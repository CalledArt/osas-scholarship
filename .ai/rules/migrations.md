---
paths:
  - 'database/migrations/**'
---

# Migrations

## Public schema is closed to Supabase's Data API
The app never uses Supabase's REST/Data API; Laravel connects as `postgres` (bypasses RLS) and Storage goes through S3 keys. Migration 2026_10_04 restrict_public_schema_from_the_data_api enabled RLS on every public table with no policies, revoked all anon/authenticated grants, removed them from postgres's default privileges, and added the `ensure_rls` event trigger (public.rls_auto_enable) so new tables get RLS automatically. Do not add RLS policies or GRANTs to anon/authenticated, and do not drop the trigger — that is what keeps tables from showing as "Unrestricted" and readable with the public anon key. The migration no-ops on SQLite/non-Supabase Postgres.
