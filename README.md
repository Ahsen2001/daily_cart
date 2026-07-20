# DailyCart

DailyCart is a Laravel 12 multi-vendor grocery delivery platform with customer, vendor, rider, administrator, REST API, and Flutter mobile experiences. Prices are stored and displayed in LKR.

## Requirements

- PHP 8.2 or newer with `mbstring`, `openssl`, `pdo_mysql`, and `fileinfo`
- Composer 2
- MySQL 8
- Node.js 22 and npm
- Flutter stable with Dart 3.10 or newer for `dailycart_mobile`

## Local installation

```bash
git clone <repository-url> dailycart
cd dailycart
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

Create a MySQL database and update `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`, then initialize the application:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

For day-to-day development, either run each process separately or use the bundled command:

```bash
composer dev
```

That starts the Laravel server, queue listener, application log viewer, and Vite development server. The application is available at the `APP_URL` configured in `.env`.

## Seed data

`php artisan migrate --seed` loads roles, the development super-admin, catalog categories, and sample customer/vendor/rider/admin accounts. The sample credentials are defined in `database/seeders/SuperAdminSeeder.php` and `database/seeders/UserCredentialsSeeder.php`.

Seeded accounts are for local development only. Do not run `UserCredentialsSeeder` in production, and change every seeded password in any shared environment.

To rebuild a disposable local database:

```bash
php artisan migrate:fresh --seed
```

## Queues

Email, OTP, push, SMS, and post-checkout notification work is queued after the database transaction commits. Local development uses the database queue configured by `QUEUE_CONNECTION=database`.

Run a worker with:

```bash
php artisan queue:work --tries=3 --timeout=90
```

Production should keep `queue:work` alive with Supervisor or a comparable process manager. Example Supervisor program:

```ini
[program:dailycart-worker]
command=php /var/www/dailycart/artisan queue:work --sleep=3 --tries=3 --timeout=90
directory=/var/www/dailycart
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/dailycart-worker.log
```

Restart workers after each deployment:

```bash
php artisan queue:restart
```

## Scheduler

The scheduler processes subscriptions daily and prunes expired Sanctum tokens. Run it locally with:

```bash
php artisan schedule:work
```

Production requires one cron entry:

```cron
* * * * * cd /var/www/dailycart && php artisan schedule:run >> /dev/null 2>&1
```

Review the active tasks with `php artisan schedule:list`.

## PayHere sandbox

Create a PayHere sandbox merchant and set these values in `.env`:

```dotenv
PAYHERE_MODE=sandbox
PAYHERE_SANDBOX=true
PAYHERE_MERCHANT_ID=
PAYHERE_MERCHANT_SECRET=
PAYHERE_APP_ID=
PAYHERE_APP_SECRET=
PAYHERE_CURRENCY=LKR
PAYHERE_NOTIFY_URL=https://your-public-host/payment/payhere/notify
```

PayHere must reach `PAYHERE_NOTIFY_URL`, so localhost requires an HTTPS tunnel. The notification endpoint is intentionally CSRF-exempt and validates the PayHere signature. Never commit merchant credentials. Switch `PAYHERE_MODE=live` only after replacing every sandbox credential and confirming the production callback URL.

Cash on delivery, card, bank transfer, and wallet are accepted checkout methods. Wallet payments settle immediately during checkout; card sandbox processing uses PayHere.

## Flutter mobile configuration

The mobile client is in `dailycart_mobile`:

```bash
cd dailycart_mobile
cp .env.example .env
flutter pub get
flutter run --flavor customer -t lib/main_customer.dart
```

Configure `dailycart_mobile/.env`:

```dotenv
API_BASE_URL=http://10.0.2.2:8000/api/v1
TESTING_API_BASE_URL=https://your-staging-host/api/v1
PAYHERE_RETURN_URL=http://10.0.2.2:8000/customer/payments
GOOGLE_MAPS_API_KEY=
```

- Android emulator: use `10.0.2.2` for the host machine.
- iOS simulator: use `127.0.0.1` when Laravel runs locally.
- Physical device: use the computer's LAN address and allow the port through the firewall.
- Production builds must use HTTPS API and return URLs.

Do not run `flutter create .` over this project. The checked-in Android flavors
and iOS schemes are part of the production app identity configuration.

## API contract

The frozen Laravel v1 contract is documented in
[`docs/api/v1/README.md`](docs/api/v1/README.md). Its exact route surface is
stored in [`docs/api/v1/route-contract.json`](docs/api/v1/route-contract.json)
and enforced by `ApiV1RouteContractTest`.

Flutter calls that are missing or inconsistent with Laravel are tracked in
[`docs/api/v1/mobile-gap-matrix.md`](docs/api/v1/mobile-gap-matrix.md).

Run the route freeze test with:

```bash
php artisan test --filter=ApiV1RouteContractTest
```

## Testing and code quality

The test suite expects a MySQL database named `dailycart_test` by default, as configured in `phpunit.xml`.

```bash
composer test
composer format:check
npm run build
composer audit --locked
npm audit --audit-level=high
```

Apply PHP formatting automatically with:

```bash
composer format
```

GitHub Actions runs PHPUnit, Pint, the Vite production build, Composer audit, and npm audit for every push and pull request.

## Backups

Database backups are streamed, encrypted, and retention-limited. Configure a durable private disk and a separate encryption secret:

```dotenv
BACKUP_DISK=s3
BACKUP_DIRECTORY=backups
BACKUP_RETENTION_COUNT=10
BACKUP_RETENTION_DAYS=30
BACKUP_ENCRYPTION_KEY=base64:replace-with-a-dedicated-random-secret
```

Do not expose the backup disk publicly. Store `BACKUP_ENCRYPTION_KEY` in the deployment secret manager and retain it for as long as its backups must remain restorable.

## Production deployment

1. Provision PHP, MySQL, a web server, a queue process manager, and cron.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, the canonical HTTPS `APP_URL`, production database credentials, mail/SMS/push credentials, PayHere live credentials, and backup storage secrets.
3. Install and build without development dependencies:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan queue:restart
```

4. Point the web server document root at `public/` and grant the web user write access only to `storage/` and `bootstrap/cache/`.
5. Start queue workers, enable the scheduler cron entry, and verify `/up`, payment callbacks, mail delivery, and backup creation.

Use a maintenance window or atomic release directory for schema changes that are not backward compatible. Roll back application code only when its database migrations remain compatible; database restoration requires the matching encrypted backup key.
