# BIIK FarmSuite

BIIK FarmSuite is a Laravel application for organizing pig-farm records. The current release provides the public product site, verified account registration, login and password recovery, role-based portals, and authorization foundations for future farm modules.

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
- User activation and non-admin role management.
- Granular backend permission checks for animal, breeding, piglet, sales, expense, and report module routes.
- No farm-record modules have been implemented yet.

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

The migrations add Spatie role/permission tables and an `is_active` flag to users. They do not remove or rewrite existing user data. Review your database backup and environment before applying migrations in a deployed environment.

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
- Admin pages and Livewire mutations enforce authorization on the server. Module routes use backend permission middleware in addition to dashboard navigation filtering.
- Inactive accounts cannot authenticate or access the protected workspace. Administrator accounts cannot be deactivated through user management, and an administrator cannot deactivate their own account there.
- Farm records, device location, contacts, camera, and notification permissions are not collected or exposed by this phase.
- Keep `.env`, SMTP credentials, and application keys private. Use HTTPS and secure cookie settings in production.

## Not yet implemented

Actual animal, breeding, piglet, sales, and expense management; branded email templates; audit logging and audit-log review; and optional device permissions are separate future phases. The current protected module pages are authorization placeholders only.
