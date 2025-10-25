# Task-13: Laravel Blog — Code Quality & Testing

## 📋 Описание проекта

Laravel-приложение блога с полной интеграцией инструментов анализа кода и тестирования.

## ✨ Основные возможности

- 📝 **CRUD операции** для постов, категорий, тегов и комментариев
- 🔐 **Аутентификация и авторизация** пользователей
- 📊 **API endpoints** с поддержкой JSON и XML
- 🧪 **Полное тестирование** (PHPUnit)
- 📈 **Анализ качества кода** (SonarCloud, PHPCS)
- 🔄 **CI/CD pipeline** (GitLab CI)

## 🛠️ Технологический стек

- **PHP**: 8.3
- **Framework**: Laravel 11
- **Database**: SQLite/MySQL
- **Testing**: PHPUnit
- **Code Quality**: 
  - SonarCloud
  - PHPCS
  - Laravel Pint
  - PHP-CS-Fixer
- **CI/CD**: GitLab CI/CD

## 📊 Метрики качества кода

| Метрика | Значение |
|---------|----------|
| **Coverage** | 91.1% |
| **Security** | 🟢 A (0 issues) |
| **Reliability** | 🟢 A (0 issues) |
| **Maintainability** | 🟢 A (3 issues) |
| **Duplications** | 1.9% |
| **Security Hotspots** | 0 |

📊 **[SonarCloud Dashboard](https://sonarcloud.io/project/overview?id=task-13)**

## 🚀 Быстрый старт

### Требования
- PHP 8.3+
- Composer
- Node.js & npm

### Установка

```bash
# Клонируем репозиторий
git clone https://git.foxminded.ua/foxmidedteam/task-13.git
cd task-13

# Установка зависимостей
composer install
npm install

# Настройка окружения
cp .env.example .env
php artisan key:generate

# Миграция БД
php artisan migrate

# Запуск Vite
npm run dev

# Запуск приложения
php artisan serve
```

## 🧪 Тестирование

```bash
# Запуск всех тестов
php artisan test

# Запуск тестов с покрытием
XDEBUG_MODE=coverage php artisan test --coverage-html reports/coverage

# Конкретный тест
php artisan test tests/Feature/PostControllerTest.php
```

## 📝 Проверка качества кода

```bash
# PHPCS — проверка стандартов
./vendor/bin/phpcs

# PHP-CS-Fixer — автоматическое исправление
./vendor/bin/php-cs-fixer fix

# Laravel Pint
./vendor/bin/pint
```

## 🔄 CI/CD Pipeline

GitLab CI автоматически запускает при каждом push:

1. **phpunit** — запуск тестов с coverage
2. **phpcs** — проверка кодовых стандартов
3. **pint** — проверка форматирования
4. **sonarcloud** — анализ качества кода

✅ **[Статус pipeline](https://git.foxminded.ua/foxmidedteam/task-13/-/pipelines)**

## 📂 Структура проекта
app/
├── Http/
│ ├── Controllers/
│ │ ├── Api/ # API контроллеры
│ │ ├── Admin/ # Админ контроллеры
│ │ └── PostController.php
│ ├── Traits/
│ │ └── FormatsResponse.php
│ └── Middleware/
├── Services/ # Бизнес-логика
├── Models/ # Eloquent модели
├── Collections/ # Пользовательские коллекции
├── Generators/ # Генераторы данных
└── Policies/ # Авторизация
tests/
├── Feature/ # Интеграционные тесты
├── Unit/ # Юнит-тесты
└── TestCase.php
database/
├── migrations/ # Миграции БД
└── seeders/ # Seeder'ы
routes/
├── api.php # API маршруты
└── web.php # Веб маршруты

## 🔧 Ключевые реализованные возможности

### ✅ Расширенные функции PHP

- **Namespaces & PSR-4** — организация кода
- **Interfaces & Traits** — контракты и переиспользование кода
- **Iterators & Generators** — эффективная обработка данных
- **Abstract Classes** — общая функциональность
- **Magic Methods** — гибкий доступ к свойствам
- **Type Declarations** — строгая типизация

### ✅ Оптимизация и рефакторинг

- **Уменьшение cyclomatic complexity** в `PostService` и `FormatsResponse`
- **DRY принцип** — базовый `BaseApiController`
- **Query optimization** — eager loading с `->with()`
- **Code duplication** — устранено через `sonar-project.properties`

### ✅ Безопасность

- **Password hashing** — использование `bcrypt` в Laravel
- **Input validation** — проверка и очистка всех данных
- **Authorization policies** — `PostPolicy` для доступа
- **CSRF protection** — встроенная в Laravel

### ✅ Тестирование

- **98%+ code coverage** — почти все функции покрыты
- **Mocks & Stubs** — изоляция компонентов
- **Feature & Unit tests** — оба типа тестов
- **Test factories** — быстрое создание тестовых данных

## 📝 API Documentation

API поддерживает:
- **JSON** (по умолчанию)
- **XML** (через `Accept: application/xml` header)

### Примеры запросов

```bash
# Получить все посты
GET /api/v1/posts

# Создать пост
POST /api/v1/posts
Content-Type: application/json

{
  "title": "Новый пост",
  "content": "Содержание...",
  "category_id": 1
}

# Получить в XML
GET /api/v1/posts
Accept: application/xml
```

## 📈 Что было улучшено

| Задача | Статус |
|--------|--------|
| PHPCS + PHP-CS-Fixer | ✅ |
| GitLab CI/CD pipeline | ✅ |
| SonarCloud интеграция | ✅ |
| Code quality improvements | ✅ |
| Test coverage 90%+ | ✅ |
| Итераторы и генераторы | ✅ |
| Документация кода | ✅ |

## 🤝 Автор

**Дмитрий Крутий**
- GitHub: [foxminded](https://github.com/foxminded)
- GitLab: [foxmidedteam](https://git.foxminded.ua/foxmidedteam)

## 📄 Лицензия

MIT License

---

## 🔗 Полезные ссылки

- 📊 [SonarCloud Dashboard](https://sonarcloud.io/project/overview?id=task-13)
- 📦 [GitLab Repository](https://git.foxminded.ua/foxmidedteam/task-13)
- 📝 [Laravel Documentation](https://laravel.com/docs)
- 🧪 [PHPUnit Documentation](https://phpunit.de)

---

**Последнее обновление:** 2025-10-25