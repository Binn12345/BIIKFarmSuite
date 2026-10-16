# BIIK FarmSuite

BIIK FarmSuite is a Laravel application for organizing pig-farm operations. It connects animal and pen records with breeding, farrowing, piglet, health, feed, inventory, customer, supplier, purchase, sale, expense, and reporting workflows. The public landing page describes the product; farm records are available only to authenticated, authorized users.

## Project overview and features

- Laravel authentication with registration, email verification, password recovery, rate-limited login, active-account checks, and CSRF-protected session logout.
- Protected administrator access and role/permission-based farm-user access, powered by Spatie Laravel Permission and the FarmSuite Permission Catalog.
- Administrator User Management with search, role and status filters, read-only details, create/edit dialogs, optional profile pictures, role assignment, account activation, and invitation resend.
- Individual profile management for contact details and profile-picture upload, replacement, and removal.
- JPEG, PNG, and WebP profile pictures are content-validated (2 MB maximum) and stored in a MySQL `LONGBLOB` column. Authenticated picture delivery is private and limited to the account owner or an authorized user administrator. Ordinary user queries omit the BLOB.
- Account invitations send a password-reset setup link after the user and role have been persisted. No shared or plaintext password is created or emailed. Mail failures are logged without deleting the account and are shown to the administrator; an authorized administrator can resend the invitation.
- Animal and pen management with identification, breed/category choices, acquisition information, capacity checks, details, assignment, and history-preserving archive.
- Piglet, breeding, farrowing, health/veterinary, feed, mortality, inventory, customer, supplier, purchase, sale, and expense records with searchable paginated pages, validation, details, and authorized actions.
- Transactional sale completion/cancellation, payment recording, purchase receiving, inventory adjustment, and feed consumption. Inventory changes are journaled in stock movements and guarded against negative stock and duplicate receipt/sale events.
- Database-backed operational and financial reports, date filters, and CSV export.
- Responsive, permission-filtered sidebar with active-route highlighting, keyboard-operable groups, and a mobile drawer. The account dropdown contains My Profile, Settings, and POST-based Sign out; the sidebar only links to implemented pages.
- Personal appearance settings with Soft Neutral, Clean White, and Cool Gray backgrounds and Blue, Teal, or Purple accents. Preferences persist on the user's account and apply across authenticated pages.
- Role-specific dashboards: administrators see permission-gated persisted-data metrics, recent records, a monthly completed-sales line chart, and an animal-category comparison; farm users see a concise welcome, authorized summaries, and recent records rather than another module-link grid.
- English/Filipino translation resources and legacy locale preference support remain available, but no custom language selector is displayed. Users may use browser-native page translation when their browser offers it; availability and activation depend on the browser.
- Audit trail for supported account, authorization, authentication, and farm operations.

### Data model boundaries

Animals and pens have dedicated relational tables. Other operational records currently use a shared `farm_records` table for common typed fields and validated module-specific JSON data. Categories and breeds are selected from persisted lookup records; the existing animal schema stores their selected names as strings rather than foreign keys. The design supports the current operational screens but is not a fully normalized ERP.

Current workflow limits:

- A purchase has one inventory item; a sale has one eligible animal or inventory item. Separate multi-line invoice entities are not implemented.
- Farrowing records are the litter/outcome records; there is no separate litter table.
- Piglet weight observations and weaning dates can be included in the piglet record data, but dedicated longitudinal weight, transfer, and weaning ledgers are not implemented as separate tables or workflows.
- Pregnancy outcome information is stored with breeding records; there is no separate pregnancy-monitoring entity.
- Feeding stock and consumption are recorded, but no generated feeding-schedule engine is available.
- Expense attachments, profile-picture history, refund reconciliation for paid-sale cancellation, and configurable System Settings pages are not implemented.

## System process

1. An operator registers and verifies an account, then provisions the first administrator through the protected CLI command below. Administrator access is not assignable in the user interface.
2. The administrator signs in, configures non-admin roles and module permissions, and creates farm-user accounts in the User Management dialog.
3. FarmSuite persists the account, role, and optional validated profile-picture BLOB. It generates a password-reset token and sends an invitation after the account transaction commits.
4. The invited user sets a personal password using the expiring link, signs in, and completes email verification when required.
5. The shared dashboard and responsive sidebar show only modules that the account is allowed to access. Routes and Livewire mutations independently authorize sensitive operations.
6. Authorized farm users register animals and pens, record breeding and farrowing, and relate piglet records to sows and farrowing outcomes.
7. Users record health, feed stock and consumption, mortality, and inventory movements. Purchases update inventory only when received; sales change stock or animal status only when completed.
8. Users maintain customer and supplier records, purchases, sales/payments, and expenses. Reports aggregate persisted records and distinguish sales revenue, purchases, and expenses; revenue is not labelled as profit.
9. Users manage their own permitted profile details, picture, and appearance preferences, then sign out through the protected POST endpoint.

## Technology stack

- PHP 8.3.2 CLI and Laravel Framework 12.70.0
- Livewire 3.8.10
- Blade templates
- Tailwind CSS 4.3.3 with Vite 7.3.7
- MySQL (InnoDB) for deployments; SQLite in-memory for feature tests
- Spatie Laravel Permission 6.25.0
- PHPUnit 11.5.57 and Laravel Pint
- Node.js 23.1.0 and npm 11.6.2 for the verified frontend build

The development environment used for verification also had PHPUnit 11, Node/npm as declared in the project lockfile, and Laravel Pint. Composer and npm lockfiles pin all other dependency versions.

## Installation

Requirements: PHP with PDO MySQL and SQLite, Composer 2, Node.js/npm, and MySQL 8 or a compatible server.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
```

Create a MySQL database, then configure `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`. Configure mail before sending invitations. Apply additive migrations and seed roles/permissions:

```powershell
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
npm run build
php artisan serve
```

The application is then available at `http://127.0.0.1:8000`. Do not use `migrate:fresh`, `db:wipe`, or destructive resets on a database that contains operational data. The FarmSuite migrations are additive and preserve farm records on rollback.

## Settings and dashboard analytics

The account dropdown links to My Profile and the authenticated user's Settings page, then submits Sign out through the CSRF-protected POST logout route. Settings save only `theme_background` (`soft-neutral`, `clean-white`, `cool-gray`) and `theme_accent` (`blue`, `teal`, `purple`) on the signed-in user's record. The shared layout applies allow-listed CSS custom-property palettes to navigation, cards, forms, tables, and charts and updates the active page immediately after save. These preferences do not modify roles, permissions, or farm data.

The administrator dashboard calculates total/active animals, active animals assigned to pens, piglet records, active customers, items below stored reorder levels, completed sales revenue, received purchases, recorded expenses, and period mortality when the current administrator has the corresponding module permissions. Sales charts include completed sales only. Purchase totals include received purchases; expenses and revenue remain separate metrics and neither is described as profit. SVG/CSS charts use grouped persisted records without adding a chart framework. Basic users receive their permission-filtered summaries and recent authorized records, without administrator metrics or duplicate module cards.

Provision an administrator only after a normal account has registered and verified its email:

```powershell
php artisan farmsuite:provision-admin verified-user@example.test
```

The command prompts for confirmation and does not create a user or set a password. The `admin` role is protected and cannot be assigned through User Management.

## Demo data

Demo data is deliberately not part of the default `DatabaseSeeder`. Run it explicitly in a local or testing environment after migrations:

```powershell
php artisan db:seed --class=DemoFarmDataSeeder
```

Demo seeding is rejected outside `local`/`testing` unless `FARMSUITE_ALLOW_DEMO_SEEDING=true` is explicitly configured for a dedicated demo environment. Do not enable it in production. Demo records use deterministic `DEMO-` identifiers and `demo_batch=farmsuite-2026-01` metadata. The seeder uses `firstOrCreate`, wraps generation in a transaction, does not delete unrelated records, and does not create users, credentials, roles, or permissions.

The seeder prints an entity/sub-module count table with a minimum target, actual demo count, and relationship check. It creates valid animal/pen assignments; breeding records tied to female sows and male boars; farrowing/litter outcomes tied to the same sow and breeding record; seven live piglet records for each litter's seven born-alive piglets; health records tied to animals; feed stock and consumption with stock movements; mortality events tied to animals whose status is updated; customer/supplier-linked sales and purchases with inventory movements; sale payments; and expenses.

Representative expected counts on a fresh demo dataset:

| Entity / sub-module | Minimum | Demo count |
|---|---:|---:|
| Pens | 30 | 30 |
| Animals | 30 | 90 (30 sows, 30 boars, 30 mortality-history animals) |
| Piglet registry records | 30 | 210 (7 living piglets for each of 30 farrowings) |
| Breeding records | 30 | 30 |
| Farrowing/litter outcome records | 30 | 30 |
| Health records | 30 | 30 |
| Feed stock records | 30 | 30 |
| Feed consumption records | 30 | 30 |
| Mortality events | 30 | 30 |
| Inventory items | 30 | 30 |
| Customers | 30 | 30 |
| Suppliers | 30 | 30 |
| Received purchases | 30 | 30 |
| Completed sales | 30 | 30 |
| Sale payment records | 30 | 30 |
| Farm expenses | 30 | 30 |
| Stock movements | 30 | 150 (opening, receipt, sale, and feed-use events) |
| Breed lookup values | Useful set | 6 |
| Animal-category lookup values | Useful set | 6 |

The seeder's console table is the authoritative actual-count report for the target database. Pregnancy outcome, litter, weight/weaning observations, and invoice line values use the current record data model rather than separate entities; there are no separate pregnancy-monitoring, litter, weight-history, purchase-line, or sale-line tables to count.

## Email setup

Local development defaults to `MAIL_MAILER=log`, which writes rendered mail to the application log. For real invitations, configure values from your mail provider in `.env`; do not commit provider credentials:

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

Use the TLS scheme and port provided by the mail service. The account invitation is sent synchronously after persistence, so no queue worker is required for this feature. Delivery exceptions leave the user intact and produce a visible resend path. Password-reset token expiry is controlled by Laravel's `config/auth.php` settings. Confirm SMTP delivery in staging before onboarding users.

## Localization

English is the default interface language. The existing `en` and `fil` translation resources and locale middleware remain for compatibility with saved preferences, but FarmSuite does not render a custom language selector or translation button. Browser-native translation may be offered by supported browsers; FarmSuite does not activate or guarantee it.

Application translation resources live in `lang/en/biik.php` and `lang/fil/biik.php`, with Filipino authentication and validation strings in `lang/fil/auth.php` and `lang/fil/validation.php`. Add maintained strings under the corresponding locale folder and reference them with Laravel's `__()`/`trans()` helpers. Do not translate route names, module keys, or permission codes.

## Tests and quality checks

Run all tests:

```powershell
php artisan test
```

Useful focused checks and build commands:

```powershell
php artisan test --filter=DemoFarmDataSeederTest
php artisan test --filter=UserManagementEnhancementTest
php artisan test --filter=FarmManagementTest
vendor\bin\pint --test
npm run build
```

Feature tests use SQLite and do not modify the configured MySQL data. Demo seeder verification asserts minimum entity counts, connected relationships, repeatability, and preservation of unrelated records.

## Troubleshooting

- **Database connection/migration errors:** verify the database exists and `.env` DB settings are correct, then run `php artisan config:clear` and `php artisan migrate:status`. Migrations preserve operational records; never repair schema by wiping the database.
- **Invitation not received:** set a real SMTP mailer and sender, check Laravel logs, and use the authorized Resend invitation action. Password setup links expire; resending generates a new token.
- **Mail configuration changes do not apply:** run `php artisan config:clear` locally or rebuild the configuration cache after updating production environment variables.
- **User cannot access a module:** verify the account is active and verified, inspect its roles and the permission catalog, then grant the required non-admin permission. Direct routes and Livewire actions still enforce authorization.
- **Assets look unstyled or stale:** run `npm ci` and `npm run build`; during development run `npm run dev`.
- **Demo seed reports an incomplete relationship:** check the demo-batch identifiers in `animals`, `pens`, `farm_records`, `stock_movements`, and `sale_payments`. Re-running the seeder is safe for the same demo identifiers.
- **Demo seeding is blocked:** it is enabled by default only in `local` and `testing`. For a dedicated demo deployment only, explicitly set `FARMSUITE_ALLOW_DEMO_SEEDING=true`, clear cached config, and unset it afterward.

## Security and data handling

Passwords are hashed with Laravel's hasher. The application does not email default passwords. The privileged admin role cannot be assigned from registration, user editing, or the role editor. Profile image requests require authentication and authorization, return private non-cacheable content, and set `X-Content-Type-Options: nosniff`. Audit metadata avoids passwords, tokens, sessions, and image contents.
