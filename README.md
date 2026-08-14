# Task-13: Laravel Blog — Якість коду та тестування

[![CI](https://github.com/KrutiiDmytro/task-13/actions/workflows/ci.yml/badge.svg)](https://github.com/KrutiiDmytro/task-13/actions/workflows/ci.yml)

## 📋 Опис проекту

Laravel-застосунок блогу з повною інтеграцією інструментів аналізу коду та тестування.

## ✨ Основні можливості

- 📝 **CRUD операції** для постів, категорій, тегів і коментарів
- 🔐 **Аутентифікація та авторизація** користувачів
- 📊 **API endpoints** з підтримкою JSON та XML
- 🧪 **Повне тестування** (PHPUnit)
- 📈 **Аналіз якості коду** (SonarCloud, PHPCS)
- 🔄 **CI/CD pipeline** (GitHub Actions)

## 🛠️ Технологічний стек

- **PHP**: 8.2
- **Framework**: Laravel 12
- **Database**: SQLite/MySQL
- **Testing**: PHPUnit
- **Code Quality**: 
  - SonarCloud
  - PHPCS
  - Laravel Pint
  - PHP-CS-Fixer
- **CI/CD**: GitHub Actions

## 📊 Метрики якості коду

| Метрика | Значення |
|---------|----------|
| **Тести** | 580 passed (1921 assertions) |
| **Coverage (statements)** | 99.2% |
| **Coverage (methods)** | 97.3% |
| **PHPCS** | 🟢 0 порушень |
| **Laravel Pint** | 🟢 PASS (165 файлів) |

## 🚀 Швидкий старт

### Вимоги
- PHP 8.2+
- Composer
- Node.js 22+ & npm

### Встановлення

```bash
# Клонуємо репозиторій
git clone https://github.com/KrutiiDmytro/task-13.git
cd task-13

# Встановлення залежностей
composer install
npm install

# Налаштування середовища
cp .env.example .env
php artisan key:generate

# Міграція БД
php artisan migrate

# Запуск Vite
npm run dev

# Запуск застосунку
php artisan serve
```

## 🧪 Тестування

```bash
# Запуск усіх тестів
php artisan test

# Запуск тестів з покриттям
XDEBUG_MODE=coverage php artisan test --coverage-html reports/coverage

# Конкретний тест
php artisan test tests/Feature/PostControllerTest.php
```

## 📝 Перевірка якості коду

```bash
# PHPCS — перевірка стандартів
./vendor/bin/phpcs

# PHP-CS-Fixer — автоматичне виправлення
./vendor/bin/php-cs-fixer fix

# Laravel Pint
./vendor/bin/pint
```

## 🔄 CI/CD Pipeline

GitHub Actions (`.github/workflows/ci.yml`) запускається на push у `master` і на кожен pull request:

1. **tests** — PHPUnit з coverage (xdebug), звіт вивантажується як артефакт
2. **quality** — PHPCS і Laravel Pint
3. **sonar** — аналіз SonarCloud (пропускається, поки не додано `SONAR_TOKEN` у Secrets)

✅ **[Статус pipeline](https://github.com/KrutiiDmytro/task-13/actions)**

## 📂 Структура проекту
app/
├── Http/
│ ├── Controllers/
│ │ ├── Api/ # API контролери
│ │ ├── Admin/ # Адмін контролери
│ │ └── PostController.php
│ ├── Traits/
│ │ └── FormatsResponse.php
│ └── Middleware/
├── Services/ # Бізнес-логіка
├── Models/ # Eloquent моделі
├── Collections/ # Користувацькі колекції
├── Generators/ # Генератори даних
└── Policies/ # Авторизація
tests/
├── Feature/ # Інтеграційні тести
├── Unit/ # Юніт-тести
└── TestCase.php
database/
├── migrations/ # Міграції БД
└── seeders/ # Seeder'и
routes/
├── api.php # API маршрути
└── web.php # Веб маршрути

## 🔧 Ключові реалізовані можливості

### ✅ Розширені функції PHP

- **Namespaces & PSR-4** — організація коду
- **Interfaces & Traits** — контракти та повторне використання коду
- **Iterators & Generators** — ефективна обробка даних
- **Abstract Classes** — загальна функціональність
- **Magic Methods** — гнучкий доступ до властивостей
- **Type Declarations** — сувора типізація

### ✅ Оптимізація та рефакторинг

- **Зменшення cyclomatic complexity** у `PostService` та `FormatsResponse`
- **DRY принцип** — базовий `BaseApiController`
- **Query optimization** — eager loading з `->with()`
- **Code duplication** — усунено через `sonar-project.properties`

### ✅ Безпека

- **Password hashing** — використання `bcrypt` у Laravel
- **Input validation** — перевірка та очищення всіх даних
- **Authorization policies** — `PostPolicy` для доступу
- **CSRF protection** — вбудована в Laravel

### ✅ Тестування

- **98%+ code coverage** — майже всі функції покриті
- **Mocks & Stubs** — ізоляція компонентів
- **Feature & Unit tests** — обидва типи тестів
- **Test factories** — швидке створення тестових даних

## 📝 API Documentation

API підтримує:
- **JSON** (за замовчуванням)
- **XML** (через `Accept: application/xml` header)

### Приклади запитів

```bash
# Отримати всі пости
GET /api/v1/posts

# Створити пост
POST /api/v1/posts
Content-Type: application/json

{
  "title": "Новий пост",
  "content": "Зміст...",
  "category_id": 1
}

# Отримати у XML
GET /api/v1/posts
Accept: application/xml
```

## 📈 Що було покращено

| Завдання | Статус |
|--------|--------|
| PHPCS + PHP-CS-Fixer | ✅ |
| GitHub Actions pipeline | ✅ |
| SonarCloud інтеграція | ✅ |
| Code quality improvements | ✅ |
| Test coverage 90%+ | ✅ (99.2%) |
| Ітератори та генератори | ✅ |
| Документація коду | ✅ |

## 🤝 Автор

**Дмитро Крутий**
- GitHub: [KrutiiDmytro](https://github.com/KrutiiDmytro)

## 📄 Ліцензія

MIT License

---

## 🔗 Корисні посилання

- 📦 [GitHub Repository](https://github.com/KrutiiDmytro/task-13)
- 🔄 [GitHub Actions](https://github.com/KrutiiDmytro/task-13/actions)
- 📝 [Laravel Documentation](https://laravel.com/docs)
- 🧪 [PHPUnit Documentation](https://phpunit.de)

---

**Останнє оновлення:** 2026-08-14
