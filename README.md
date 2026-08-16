# PixelPulse

[![CI](https://github.com/KrutiiDmytro/pixelpulse/actions/workflows/ci.yml/badge.svg)](https://github.com/KrutiiDmytro/pixelpulse/actions/workflows/ci.yml)

Блог про ігри на Laravel 12 — з публічною частиною, адмінкою, REST API та ролями.
Написаний як навчальний проєкт із наголосом на якість коду: тести, статичний аналіз і CI.

## Можливості

**Публічна частина**
- Стрічка статей із виділеним матеріалом, сторінки категорій і тегів
- Пошук по заголовках і тексту
- Коментарі, зокрема від незареєстрованих читачів
- Світла й темна теми з перемикачем

**Адмінка та CRUD**
- Керування статтями, категоріями, тегами й коментарями
- Ролі та дозволи через `spatie/laravel-permission`
- Завантаження обкладинок на диск `public`

**API**
- REST-ендпоїнти під `/api/v1`
- Відповіді у JSON або XML — формат обирається заголовком `Accept`
- Токени через Laravel Sanctum
- Документація OpenAPI на `/api/documentation`

## Стек

| | |
|---|---|
| PHP | 8.2 |
| Framework | Laravel 12 |
| БД | SQLite (за замовчуванням) або MySQL |
| Фронтенд | Vite 7, Bootstrap 5.3, власна CSS-тема |
| Автентифікація | Laravel Breeze, Sanctum для API |
| Ролі | spatie/laravel-permission |
| API-документація | L5 Swagger (`zircote/swagger-php`) |
| Адмін-шаблон | jeroennoten/laravel-adminlte |
| Тести | PHPUnit |
| Стиль коду | PHPCS, Laravel Pint, PHP-CS-Fixer |
| CI | GitHub Actions, SonarCloud |

## Швидкий старт

Потрібні PHP 8.2+, Composer і Node.js 20.19+ (вимога Vite 7).

```bash
git clone https://github.com/KrutiiDmytro/pixelpulse.git
cd pixelpulse

composer install
npm install

cp .env.example .env
php artisan key:generate

# База даних і демонстраційні дані
php artisan migrate --seed

# Обкладинки статей лежать у storage/app/public — без цього симлінка вони не віддаються
php artisan storage:link
```

Далі два процеси в окремих терміналах:

```bash
npm run dev        # Vite з hot reload на :5173
php artisan serve  # застосунок на :8000
```

Якщо збираєте фронтенд один раз замість дев-сервера — `npm run build`.

## Тести

```bash
php artisan test                                    # усі тести
php artisan test tests/Feature/PostControllerTest.php   # окремий файл

# З покриттям (потрібен Xdebug)
XDEBUG_MODE=coverage php artisan test --coverage-html reports/coverage
```

## Якість коду

```bash
./vendor/bin/phpcs              # перевірка стандартів
./vendor/bin/pint               # форматування за пресетом Laravel
./vendor/bin/pint --test        # перевірка без змін
./vendor/bin/php-cs-fixer fix   # автовиправлення
```

### Поточні метрики

Заміряно локально 16.08.2026 — актуальний стан завжди показує значок CI угорі.

| Метрика | Значення |
|---|---|
| Тести | 580 пройдено, 1925 перевірок |
| Покриття рядків | 99.2% (1271 з 1281) |
| Покриття методів | 96.8% (184 з 190) |
| Покриття класів | 93.9% (46 з 49) |
| PHPCS | 0 помилок, 1 попередження |
| Laravel Pint | PASS, 166 файлів |

## CI

`.github/workflows/ci.yml` запускається на push у `master` і на кожен pull request:

1. **tests** — PHPUnit із покриттям через Xdebug, звіт вивантажується як артефакт
2. **quality** — PHPCS і Laravel Pint
3. **sonar** — аналіз SonarCloud (пропускається, поки в Secrets немає `SONAR_TOKEN`)

## Структура

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # адмін-панель
│   │   ├── Api/V1/         # REST API
│   │   └── Auth/           # автентифікація
│   ├── Requests/           # валідація форм
│   ├── Resources/          # трансформація відповідей API
│   ├── Traits/             # FormatsResponse — вибір JSON/XML
│   └── Middleware/
├── Models/                 # Eloquent-моделі
├── Services/               # бізнес-логіка (Post, Category, Tag, Comment)
├── Policies/               # авторизація
└── View/                   # компоненти Blade

database/
├── migrations/
└── seeders/
    └── assets/posts/       # обкладинки статей під версійним контролем

resources/
├── css/app.css             # тема PixelPulse поверх Bootstrap
└── views/
    ├── components/         # post-card, category-badge тощо
    ├── public/             # публічні сторінки
    └── admin/              # адмінка

routes/
├── web.php
└── api.php
```

## API

Формат відповіді залежить від заголовка `Accept` — за замовчуванням JSON.

```bash
# Список статей
curl http://localhost:8000/api/v1/posts

# Те саме у XML
curl -H "Accept: application/xml" http://localhost:8000/api/v1/posts

# Створення статті
curl -X POST http://localhost:8000/api/v1/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{"title":"Заголовок","content":"Текст","category_id":1}'
```

Повний перелік ендпоїнтів — на `/api/documentation`.

## Контент і зображення

Демонстраційні статті переказують реальні публікації ігрових видань своїми словами
й посилаються на першоджерело. Дослівних цитат немає — чужі тексти захищені авторським правом.

Обкладинки взяті з Wikimedia Commons. Автори й ліцензії перелічені в
[IMAGE-CREDITS.md](IMAGE-CREDITS.md). Частина зображень поширюється під CC BY та CC BY-SA,
які **вимагають видимого зазначення авторства** там, де зображення опубліковане.

## Автор

**Дмитро Крутий** — [GitHub](https://github.com/KrutiiDmytro)

## Ліцензія

MIT
