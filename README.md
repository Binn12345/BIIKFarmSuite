# BIIK FarmSuite

BIIK FarmSuite is a Laravel application for organizing pig-farm records. It provides the public product site, verified account registration, login and password recovery, role-based portals, animal and pen records, operational records, stock and sales workflows, and database-backed reports.

## Intended users and access

- **Visitors** can view the landing page and register or sign in.
- **Basic users** receive the `basic-user` role on registration. They can access their verified, active account dashboard and only the farm modules granted to their roles.
- **Administrators** can search users, activate or deactivate non-admin accounts, assign and revoke non-admin roles, and configure module permissions. Administrator access is provisioned by an operator from the command line after email verification; it is never selectable during registration or assignable in the user interface.

## Current features

- Responsive BIIK FarmSuite landing page with configurable contact details.
- Registration with password confirmation and strength validation.
- Laravel email verification links and password-reset notifications.
- Session-based login with rate limiting, active-account checks, and secure logout.
- Role-guarded admin and basic-user dashboards.
- A shared, responsive dashboard with permission-filtered cards and database-backed summaries.
- Database-backed animal registration, searchable/paginated animal records, details, acquisition and status fields, updates, and history-preserving archive.
- Database-backed pen management with searchable/paginated records, details, capacity/location/status management, safe deletion, and capacity enforcement for animal assignment.
- Database-backed piglet, litter/farrowing, breeding, health, feeding, mortality, inventory, customer, supplier, purchase, sales, and expense record workflows with search, pagination, details, validation, and protected archival/finalization.
- Transactional animal sales, purchase receiving, inventory adjustments, and feed consumption with stock movement history and sale payment records.
- Date-range Farm Reports with animal, piglet, breeding, farrowing, health, mortality, feed, inventory, sales, purchase, and expense summaries and CSV export.
- A personal profile page for editing the authenticated user's name, email, phone, and address; changed email requires reverification.
- Database-backed role search, editing, membership review, and grouped permission matrix.
- A centralized, idempotently seeded permission catalog for farm modules, business records, reports, users, roles, permissions, and audit logs.
- User activation, non-admin role management, and effective-permission review.
- Granular backend permission checks for farm module routes and sensitive Livewire actions.
- Admin-only, searchable and filterable audit trail for authentication, registration, role changes, and account activation.
- Farm records are shared across authorized users; creator and updater IDs are recorded where supported.

## Requirements

- PHP 8.2 or later with Laravel-required extensions, including PDO MySQL and SQLite for tests.
- Composer 2.
- Node.js and npm.
- MySQL 8.0 or a compatible MySQL server.

## Installation

Create a MySQL database named `biikfarmsuite`, then from the project directory run:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
npm run build
```

Configure `.env` with the database host, database name, username, and password for your local MySQL instance. Then apply the additive migrations and seed the standard roles and permissions:

```powershell
php artisan migrate
php artisan db:seed
```

The additive migrations add Spatie role/permission tables, an `is_active` flag to users, append-only audit log storage, animal/pen fields, profile contact fields, shared farm-record storage, stock movements, and sale payments. They preserve existing farm records; do not use `migrate:fresh` or `db:wipe` against a database containing farm data.

Start the development services with:

```powershell
composer run dev
```

The Laravel development server is available at `http://127.0.0.1:8000`.

## Email configuration

Laravel's mail configuration reads SMTP settings from environment variables. For a deployment, configure the SMTP transport and credentials in `.env`, for example:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

Use values from your mail provider; for implicit TLS, use the scheme and port specified by the provider (commonly `smtps` on port 465). Do not commit SMTP credentials. Local development may use `MAIL_MAILER=log` to inspect generated verification and reset notifications in the application log. Branded notification templates and additional account-security email events are planned for a later phase.

Optional public contact information is configured with `FARMSUITE_CONTACT_EMAIL`, `FARMSUITE_CONTACT_PHONE`, and `FARMSUITE_CONTACT_ADDRESS`. Unset values are not displayed.

## Provision the first administrator safely

1. Register a normal account through `/register`.
2. Verify the account using the email link.
3. After migrations and role seeding, an authorized operator runs:

   ```powershell
   php artisan farmsuite:provision-admin verified-user@example.com
   ```

4. Confirm the exact verified account when prompted.

The command refuses missing, unverified, or inactive accounts and does not set a password or create a user. There are no hardcoded production admin credentials. Administrators can grant non-admin roles and permissions through the portal; granting administrator access remains an operator-only CLI task.

Standard roles (`admin`, `basic-user`, `farm-supervisor`, `farm-staff`, and `sales-staff`) are seeded only if absent. The `admin` role represents protected Super Admin access; it is excluded from the role editor and user assignment controls. Do not remove or demote the last active administrator. The user-management UI prevents deactivating any administrator, and the admin role cannot be edited or deleted through the UI.

Open `/admin/roles` to search and manage non-admin roles; `/admin/permissions` to review the permission catalog; and `/admin/audit-logs` to review security and access events. Permission changes are validated against the server-side catalog.

Both administrators and basic users land on `/dashboard`. The dashboard lists implemented pages only when the user has the corresponding view permission. Farm modules use `/modules/{module}`; the profile is `/profile`. User Management, Roles and Permissions, Permission Catalog, and Audit Trail are available to administrators. `/admin/dashboard` remains as a compatibility redirect to the shared dashboard.

## Tests and quality checks

Run the test suite:

```powershell
php artisan test
```

Optional checks used during development:

```powershell
vendor/bin/pint --test
npm run build
composer audit
```

Feature tests use an in-memory SQLite database and do not migrate or modify the configured MySQL database.

## Security and privacy

- Passwords are hashed by Laravel's configured hasher and are never stored in plaintext.
- Verification links use Laravel's signed URL mechanism; password resets use Laravel's password broker and expiring reset tokens.
- Registration assigns the basic-user role on the server; role and permission values are not accepted from the registration form.
- Admin pages and Livewire mutations enforce authorization on the server. The protected `admin` role is treated as Super Admin by Laravel's authorization gate, so it retains access if its explicit permission grants are stale or incomplete. Module routes use backend permission middleware in addition to dashboard navigation filtering; non-admin users remain restricted to their assigned permissions.
- Inactive accounts cannot authenticate or access the protected workspace. Administrator accounts cannot be deactivated through user management, and an administrator cannot deactivate their own account there.
- Audit metadata is allowlisted by call sites and recursively strips keys containing password, token, session, secret, authorization, or credential. Audit rows have no update timestamp and the Eloquent model rejects ordinary updates and deletes.
- No user-specific permission overrides are enabled. Users inherit effective permissions from their roles.
- Shared farm records use one database table/model with typed common fields and validated module-specific data; animal and pen records use their own relational tables. Creator/updater attribution is recorded where supported.
- Farm records, device location, contacts, camera, and notification permissions are not collected or exposed by this phase.
- Keep `.env`, SMTP credentials, and application keys private. Use HTTPS and secure cookie settings in production.

## Scope notes

Operational modules share a typed `farm_records` table and reusable Livewire CRUD interface. Animal and Pen Management have dedicated models and pages. This is an initial operational workflow, not a fully normalized ERP: sales support one animal or inventory item per sale record, purchases support one inventory item per purchase record, and stock/payments are separately journaled.

Specialized workflows not yet implemented include longitudinal piglet weight and transfer records, generated feeding schedules, multi-line invoices, profile photo uploads, expense attachments, and reconciliation/refund handling for cancelling a sale with recorded payments. Categories and breeds are managed as records but are not yet normalized foreign-key selectors on animals. Plan these extensions before relying on this release for those workflows.
