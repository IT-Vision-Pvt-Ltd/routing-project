# Service Routing Portal (PHP + MySQL)

A lightweight, mobile‑friendly portal to ingest daily CSV exports (Jobs, Clients, Line Items), 
generate two operational reports, and print route planning sheets.

## Quick Start

1. Create a MySQL database and import `schema.sql`, then `seed_users.sql`.
2. Copy the `service-routing-portal` folder to your server (e.g., `/var/www/html/portal`).
3. Configure database credentials and optional API keys in `app/config.php`.
4. Ensure `public/` is web‑root or make a vhost root that points to `public/`.
5. Login with: **admin@example.com** / **Admin@123**. Change password immediately.
6. Import CSVs from **Dashboard → Import** for `clients.csv`, `jobs.csv`, `line_items.csv`.

## CSV Ingestion

- Use the **Import** page to upload daily CSVs (or call ETL scripts).
- Upserts are idempotent, keyed by primary keys from your ERP:
  - Clients: `id`
  - Jobs: `id` (and `account_reference_number` UNIQUE)
  - Line Items: `id` (with `job_id` FK → Jobs)

All parse/validation errors are recorded in `import_logs`.

## Reports

- **Output #1 (Jobs + Client details)**: `Jobs` filtered by stages **Approved / Scheduled / Completed**,
  joined with Client contact & billing info. See **Reports → Jobs (Output 1)**.
- **Output #2 (Job → Line Items)**: Lookup a job by `account_reference_number` (job number) 
  to list all line items for the corresponding `job_id`. See **Reports → Line Items (Output 2)**.
- Both reports are printable and exportable to CSV via `/public/api/export.php?view=output1|output2`.

## Route Planning

- Go to **Route Planner**, pick a date and optional sales rep(s).
- The app will try to build per‑rep routes using a nearest‑neighbor heuristic.
- Geocoding: store lat/lng on `clients` (columns provided). 
  - If `GOOGLE_MAPS_API_KEY` is set in `config.php`, the app can geocode missing client addresses server‑side.
  - Otherwise, routes will be ordered by city/name as a fallback.

> For production routing, plug in Mapbox/Google/OSRM if desired. The code is modular.

## Security

- Sessions with secure cookies; password hashing via `password_hash()` (BCRYPT).
- Basic RBAC: Admin (can import), Manager/Dispatcher (full read), Sales (limited), Read‑Only.
- Update `.htaccess` and PHP settings per your hosting. Always run over HTTPS in production.

## Files

- `schema.sql` – tables, indexes, and views (`v_output_1`, `v_output_2`).
- `seed_users.sql` – creates an admin user (change password on first login).
- `app/` – DB connection, auth guard, helpers.
- `etl/` – CLI importers for batch jobs.
- `public/` – UI (login, dashboard, imports, reports, route planner).

## Notes

- This project mirrors the uploaded SR-OUTPUT-SCREENS.xlsx (Output‑1, Output‑2, Route Planner) and the 
  specification in the project brief. Keep CSV columns consistent with your ERP export.
- For large imports, consider running ETL with CLI (ingest_*.php) via cron and placing files in an uploads directory.
