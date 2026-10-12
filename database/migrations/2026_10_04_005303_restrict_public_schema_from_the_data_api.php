<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Close the public schema to Supabase's Data API.
     *
     * Supabase grants its public API roles (anon, authenticated) full access to
     * every table in `public`, so anyone holding the project's anon key could
     * read and write these tables over REST. The app never uses that API: it
     * connects as `postgres`, which owns the tables and bypasses RLS. Enabling
     * RLS with no policies and revoking the grants therefore shuts the API out
     * without changing anything Laravel sees.
     */
    public function up(): void
    {
        if (! $this->isSupabase()) {
            return;
        }

        DB::unprepared(<<<'SQL'
            do $$
            declare
              t record;
            begin
              for t in select tablename from pg_tables where schemaname = 'public' and not rowsecurity loop
                execute format('alter table public.%I enable row level security', t.tablename);
              end loop;
            end
            $$;

            revoke all on all tables in schema public from anon, authenticated;
            revoke all on all sequences in schema public from anon, authenticated;
            revoke all on all functions in schema public from anon, authenticated;

            alter default privileges for role postgres in schema public revoke all on tables from anon, authenticated;
            alter default privileges for role postgres in schema public revoke all on sequences from anon, authenticated;
            alter default privileges for role postgres in schema public revoke all on functions from anon, authenticated;
            alter default privileges for role postgres in schema public revoke execute on functions from public;

            create or replace function public.rls_auto_enable()
            returns event_trigger
            language plpgsql
            set search_path = pg_catalog
            as $fn$
            declare
              cmd record;
            begin
              for cmd in
                select * from pg_event_trigger_ddl_commands()
                where command_tag in ('CREATE TABLE', 'CREATE TABLE AS', 'SELECT INTO')
                  and object_type in ('table', 'partitioned table')
                  and schema_name = 'public'
              loop
                execute format('alter table if exists %s enable row level security', cmd.object_identity);
              end loop;
            end;
            $fn$;

            revoke all on function public.rls_auto_enable() from public, anon, authenticated;

            drop event trigger if exists ensure_rls;
            create event trigger ensure_rls on ddl_command_end
              when tag in ('CREATE TABLE', 'CREATE TABLE AS', 'SELECT INTO')
              execute function public.rls_auto_enable();
            SQL);
    }

    /**
     * Remove the auto-enable trigger only.
     *
     * RLS and the revoked grants are deliberately left in place: re-opening the
     * tables to the public API key is never wanted, even on a rollback.
     */
    public function down(): void
    {
        if (! $this->isSupabase()) {
            return;
        }

        DB::unprepared(<<<'SQL'
            drop event trigger if exists ensure_rls;
            drop function if exists public.rls_auto_enable();
            SQL);
    }

    /**
     * Only a Supabase Postgres has the Data API roles this migration restricts.
     */
    private function isSupabase(): bool
    {
        return DB::getDriverName() === 'pgsql'
            && DB::scalar("select count(*) from pg_roles where rolname in ('anon', 'authenticated')") === 2;
    }
};
