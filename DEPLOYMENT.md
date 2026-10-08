# Sushako deployment runbook

The application database is MySQL. Redis is used for cache and queues. The
application, queue worker and scheduler must run from the same release
directory and use the same environment file.

Before a release:

1. Confirm `APP_ENV=production`, `APP_DEBUG=false`, MySQL connectivity, Redis
   connectivity, and a real mail transport. The checked-in local environment
   intentionally keeps mail logging until production SMTP credentials are
   supplied.
2. Create a `mysqldump --single-transaction --routines --triggers` backup,
   record its SHA-256, and restore it into an isolated database before any
   production migration.
3. Run `php artisan migrate --force --no-interaction`; never run
   `DatabaseSeeder`, `migrate:fresh`, `db:wipe`, `TRUNCATE`, or `DROP` against
   the marketplace database.
4. Run `php artisan config:cache`, `route:cache`, and `view:cache`.
5. Start/restart the `queue-worker` and `scheduler` services and confirm failed
   jobs, logs, `/health`, `/robots.txt`, `/sitemap.xml`, and `/sw.js`.

Rollback is application-only: restore the previous image/release and stop
new workers if needed. Do not roll back production schema by dropping tables
or reversing data migrations. Financial and historical records are append-only
and must be reconciled forward.

Cloudflare Tunnel should continue to point only at the Nginx service. MySQL
and Redis remain on the private Docker network and are not published.
