# PixelPulse

[![CI](https://github.com/KrutiiDmytro/pixelpulse/actions/workflows/ci.yml/badge.svg)](https://github.com/KrutiiDmytro/pixelpulse/actions/workflows/ci.yml)

A gaming blog built on Laravel 12 — public site, admin panel, REST API and role-based access.
Written as a learning project with the emphasis on code quality: tests, static analysis and CI.

## Features

**Public site**
- Article feed with a featured post, plus category and tag pages
- Search across titles and body text
- Comments, including from readers who are not signed in
- Light and dark themes with a toggle

**Admin and CRUD**
- Manage posts, categories, tags and comments
- Roles and permissions via `spatie/laravel-permission`
- Cover image uploads to the `public` disk

**API**
- REST endpoints under `/api/v1`
- JSON or XML responses, selected by the `Accept` header
- Token auth through Laravel Sanctum
- OpenAPI documentation at `/api/documentation`

## Stack

| | |
|---|---|
| PHP | 8.2 |
| Framework | Laravel 12 |
| Database | MySQL 8 (tests run against it too) |
| Frontend | Vite 7, Bootstrap 5.3, custom CSS theme |
| Authentication | Laravel Breeze, Sanctum for the API |
| Roles | spatie/laravel-permission |
| API docs | L5 Swagger (`zircote/swagger-php`) |
| Admin template | jeroennoten/laravel-adminlte |
| Tests | PHPUnit |
| Code style | PHPCS, Laravel Pint, PHP-CS-Fixer |
| CI | GitHub Actions, SonarCloud |

## Getting started

Requires PHP 8.2+, Composer, Node.js 20.19+ (Vite 7 needs it) and MySQL 8.

Create two schemas up front — the second one is what the test suite uses:

```sql
CREATE DATABASE pixelpulse      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE pixelpulse_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
git clone https://github.com/KrutiiDmytro/pixelpulse.git
cd pixelpulse

composer install
npm install

cp .env.example .env
php artisan key:generate

# Put your own DB_USERNAME and DB_PASSWORD in .env before this step
php artisan migrate --seed

# Cover images live in storage/app/public and are not served without this symlink
php artisan storage:link
```

Then run two processes in separate terminals:

```bash
npm run dev        # Vite with hot reload on :5173
php artisan serve  # the app on :8000
```

To build the frontend once instead of running the dev server, use `npm run build`.

## Tests

```bash
php artisan test                                        # everything
php artisan test tests/Feature/PostControllerTest.php   # a single file

# With coverage (needs Xdebug)
XDEBUG_MODE=coverage php artisan test --coverage-html reports/coverage
```

Tests run against the `pixelpulse_test` schema on MySQL, not an in-memory SQLite
database. Running them on a different engine than the app uses would hide exactly the
class of bugs — strict mode, collation, date handling — that MySQL is there to catch.

## Code quality

```bash
./vendor/bin/phpcs              # check against the standard
./vendor/bin/pint               # format using the Laravel preset
./vendor/bin/pint --test        # check without writing changes
./vendor/bin/php-cs-fixer fix   # apply fixes
```

### Current metrics

Measured locally on 2026-08-16 — the CI badge above always reflects the current state.

| Metric | Value |
|---|---|
| Tests | 580 passed, 1925 assertions |
| Line coverage | 99.2% (1271 of 1281) |
| Method coverage | 96.8% (184 of 190) |
| Class coverage | 93.9% (46 of 49) |
| PHPCS | 0 errors, 0 warnings |
| Laravel Pint | PASS, 166 files |

## CI

`.github/workflows/ci.yml` runs on every push to `master` and on every pull request:

1. **tests** — PHPUnit with Xdebug coverage; the report is uploaded as an artifact
2. **quality** — PHPCS and Laravel Pint
3. **sonar** — SonarCloud analysis (skipped until `SONAR_TOKEN` is added to Secrets)

Note that PHPCS exits non-zero on warnings as well as errors, so a line over the
120-character limit is enough to fail the build.

## Project layout

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # admin panel
│   │   ├── Api/V1/         # REST API
│   │   └── Auth/           # authentication
│   ├── Requests/           # form validation
│   ├── Resources/          # API response transformation
│   ├── Traits/             # FormatsResponse — JSON/XML negotiation
│   └── Middleware/
├── Models/                 # Eloquent models
├── Services/               # business logic (Post, Category, Tag, Comment)
├── Policies/               # authorization
└── View/                   # Blade components

database/
├── migrations/
└── seeders/
    └── assets/posts/       # cover images kept under version control

resources/
├── css/app.css             # the PixelPulse theme, layered over Bootstrap
└── views/
    ├── components/         # post-card, category-badge and friends
    ├── public/             # public pages
    └── admin/              # admin panel

routes/
├── web.php
└── api.php
```

## API

The response format follows the `Accept` header and defaults to JSON.

```bash
# List posts
curl http://localhost:8000/api/v1/posts

# The same, as XML
curl -H "Accept: application/xml" http://localhost:8000/api/v1/posts

# Create a post
curl -X POST http://localhost:8000/api/v1/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"title":"Title","content":"Body","category_id":1}'
```

The full endpoint list lives at `/api/documentation`.

## Content and images

The demo articles paraphrase real coverage from gaming publications in our own words and
link to the original. There are no verbatim quotes — that text belongs to its authors.

Cover images come from Wikimedia Commons. Authors and licences are listed in
[IMAGE-CREDITS.md](IMAGE-CREDITS.md). Several of them are CC BY or CC BY-SA, which
**require visible attribution** wherever the image is published.

## Author

**Dmytro Krutyi** — [GitHub](https://github.com/KrutiiDmytro)

## Licence

MIT
