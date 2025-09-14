# Блог на Laravel с REST API

Современная блог-платформа на Laravel 12 с админ-панелью, системой прав доступа, REST API и полным покрытием тестами.

## 🚀 Возможности

### Для всех пользователей
- ✅ **Просмотр постов** — читайте статьи без регистрации
- ✅ **Создание постов** — неавторизованные пользователи также могут создавать посты (гостевой автор)
- ✅ **Комментирование** — оставляйте комментарии к постам
- ✅ **Поиск и фильтрация** — фильтр по категориям, тегам, поиск по названию
- ✅ **Современный дизайн** — приятный адаптивный интерфейс

### Для авторизованных пользователей
- ✅ **Редактирование постов** — изменяйте собственные посты
- ✅ **Удаление постов** — удаляйте собственные посты
- ✅ **Редактирование комментариев** — изменяйте собственные комментарии
- ✅ **Личный кабинет** — управляйте профилем
- ✅ **Dashboard** — панель пользователя

### Для администраторов
- ✅ **Админ-панель** — полное управление контентом (AdminLTE)
- ✅ **Управление пользователями** — создание, редактирование, удаление
- ✅ **Модерация комментариев** — просмотр/удаление любых комментариев
- ✅ **Управление категориями/тегами** — создание и редактирование
- ✅ **Управление постами** — редактирование/удаление любых постов
- ✅ **Статистика** — количество постов, пользователей, комментариев
- ✅ **Быстрый доступ** — кнопка "Админ-панель" в навигации

### REST API
- ✅ **API для постов** — CRUD операции с постами
- ✅ **API для комментариев** — создание и получение комментариев
- ✅ **API для категорий** — управление категориями
- ✅ **API документация** — Swagger/OpenAPI документация
- ✅ **JSON/XML ответы** — поддержка разных форматов данных
- ✅ **API Resources** — трансформация данных для API

## �� Технологии

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Bootstrap 5, Font Awesome, AdminLTE 3
- **API**: REST API с JSON/XML поддержкой
- **База данных**: MySQL / SQLite
- **Аутентификация**: Laravel Breeze + API Token
- **Авторизация**: Custom Middleware + Spatie Permission
- **Тестирование**: PHPUnit (150+ тестов)
- **Документация**: Swagger/OpenAPI

## 📦 Требования

- PHP 8.2 или новее
- Composer
- MySQL 8+ или SQLite
- Node.js 18+/20+ (для сборки assets)
- Расширения PHP: gd (для тестовых изображений), pdo, mbstring и др.

## �� Установка

### 1) Клонирование репозитория
```bash
git clone <repository-url>
cd Task-12
```

### 2) Установка зависимостей
```bash
composer install
npm install
```

### 3) Настройка окружения
```bash
cp .env.example .env
php artisan key:generate
```

### 4) Настройка БД
Отредактируйте `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_posts
DB_USERNAME=root
DB_PASSWORD=
```

### 5) Миграции и создание администратора
```bash
php artisan migrate

# Создать администратора через tinker
php artisan tinker
User::create([
    'name' => 'Администратор',
    'email' => 'admin@admin.com',
    'password' => bcrypt('admin123'),
    'is_admin' => true,
    'email_verified_at' => now()
]);
exit
```

### 6) Сборка assets
```bash
npm run build    # или npm run dev для разработки
```

### 7) Публичный доступ к изображениям
```bash
php artisan storage:link
```

### 8) Запуск сервера
```bash
php artisan serve
```

Откройте в браузере: http://localhost:8000

## 👤 Учетные записи по умолчанию

### Администратор
- **Email**: krutiidmytro@gmail.com
- **Пароль**: admin123
- **Доступ**: Админ-панель (http://localhost:8000/admin)

### Обычный пользователь
Зарегистрируйтесь через форму регистрации или создайте через tinker.

## 📁 Структура проекта

Task-12/
├── app/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── PostController.php # публичные страницы постов
│ │ │ ├── CommentController.php # публичные комментарии
│ │ │ ├── Api/V1/ # API контроллеры
│ │ │ │ ├── PostController.php # API для постов
│ │ │ │ ├── CommentController.php # API для комментариев
│ │ │ │ └── CategoryController.php # API для категорий
│ │ │ └── Admin/ # контроллеры админ-панели
│ │ │ ├── DashboardController.php # главная админки
│ │ │ ├── PostController.php # управление постами
│ │ │ ├── UserController.php # управление пользователями
│ │ │ ├── CategoryController.php # управление категориями
│ │ │ └── CommentController.php # управление комментариями
│ │ ├── Resources/Api/V1/ # API Resources для трансформации данных
│ │ │ ├── PostResource.php
│ │ │ ├── PostCollection.php
│ │ │ ├── CommentResource.php
│ │ │ └── CommentCollection.php
│ │ └── Middleware/
│ │ ├── AdminMiddleware.php # проверка прав администратора
│ │ ├── PostOwnerMiddleware.php # проверка прав на посты
│ │ └── CommentOwnerMiddleware.php # проверка прав на комментарии
│ ├── Models/
│ │ ├── User.php # модель пользователя (с методами isAdmin, canEditPost)
│ │ ├── Post.php # модель поста
│ │ ├── Comment.php # модель комментария
│ │ ├── Category.php # модель категории
│ │ └── Tag.php # модель тега
│ └── Services/
│ ├── PostService.php # бизнес-логика постов (фильтры)
│ ├── CategoryService.php # управление категориями
│ └── TagService.php # управление тегами
├── resources/
│ ├── views/
│ │ ├── posts/ # шаблоны постов (index/show/create/edit)
│ │ ├── comments/ # шаблоны комментариев
│ │ ├── admin/ # шаблоны админ-панели (AdminLTE)
│ │ ├── auth/ # шаблоны аутентификации
│ │ └── layouts/
│ │ ├── app.blade.php # основной макет
│ │ └── navigation.blade.php # навигация с кнопкой админ-панели
│ └── css/
│ └── app.css # основные стили
├── tests/
│ ├── Unit/ # Unit-тесты (модели, middleware, сервисы, компоненты)
│ └── Feature/ # Feature-тесты (контроллеры, API, интеграция)
└── storage/api-docs/ # Swagger документация

## �� Дизайн

### Основной сайт
- **Bootstrap 5** с кастомными стилями
- **Адаптивный дизайн** для всех устройств
- **Современная навигация** с кнопкой админ-панели для администраторов

### Админ-панель
- **AdminLTE 3** — профессиональная админ-панель
- **Статистические карточки** с иконками
- **Таблицы с данными** — последние посты и комментарии
- **Боковое меню** для навигации

## �� Основные маршруты

### Публичные

GET / # главная (список постов)
GET /posts # список постов
GET /posts/{id} # просмотр поста
GET /posts/create # форма создания поста
POST /posts # создать пост
GET /comments # список комментариев
POST /comments # создать комментарий

### Защищенные (требуется авторизация)

GET /posts/{id}/edit # редактирование поста (владелец/админ)
PUT /posts/{id} # обновление поста (владелец/админ)
DELETE /posts/{id} # удаление поста (владелец/админ)
GET /comments/{id}/edit # редактирование комментария (владелец/админ)
PUT /comments/{id} # обновление комментария (владелец/админ)
DELETE /comments/{id} # удаление комментария (владелец/админ)
GET /profile # профиль
PATCH /profile # обновление профиля

### Админ-панель (требуется is_admin = true)

GET /admin # главная админки
GET /admin/posts # управление постами
GET /admin/users # управление пользователями
GET /admin/comments # управление комментариями
GET /admin/categories # управление категориями

### REST API

#### Посты
GET /api/v1/posts # список постов
GET /api/v1/posts/{id} # получить пост
POST /api/v1/posts # создать пост
PUT /api/v1/posts/{id} # обновить пост
DELETE /api/v1/posts/{id} # удалить пост

#### Комментарии
GET /api/v1/comments # список комментариев
GET /api/v1/comments/{id} # получить комментарий
POST /api/v1/comments # создать комментарий

#### Категории
GET /api/v1/categories # список категорий
GET /api/v1/categories/{id} # получить категорию
POST /api/v1/categories # создать категорию
PUT /api/v1/categories/{id} # обновить категорию
DELETE /api/v1/categories/{id} # удалить категорию

## �� Система прав доступа

### Middleware
- **AdminMiddleware** — доступ к админ-панели только для администраторов
- **PostOwnerMiddleware** — редактирование постов только владельцем или админом
- **CommentOwnerMiddleware** — редактирование комментариев только владельцем или админом

### Роли пользователей
- **Гость** — просмотр, создание постов/комментариев
- **Пользователь** — + редактирование своих постов/комментариев
- **Администратор** — полный доступ ко всему контенту + админ-панель

### Проверка прав
```php
// В модели User
$user->isAdmin()                    // проверка администратора
$user->canEditPost($post)           // может ли редактировать пост

// В Blade шаблонах
@if(auth()->user()->isAdmin())
    <a href="/admin">Админ-панель</a>
@endif
```

## 🧪 Тестирование

### Запуск тестов
```bash
php artisan test                     # все тесты
php artisan test --testsuite=Unit    # только Unit тесты
php artisan test --testsuite=Feature # только Feature тесты
php artisan test --coverage-html coverage-html  # HTML отчет покрытия
php artisan test --coverage-text     # текстовый отчет покрытия
php artisan test --filter="Admin"    # только админские тесты
php artisan test --filter="Api"      # только API тесты
```

### Покрытие тестами (150+ тестов)
- ✅ **Модели**: User, Post, Comment, Category, Tag
- ✅ **Контроллеры**: Post, Comment, Admin контроллеры
- ✅ **API контроллеры**: Post, Comment, Category API
- ✅ **Middleware**: Admin, PostOwner, CommentOwner
- ✅ **Сервисы**: PostService, CategoryService, TagService
- ✅ **View Components**: AppLayout, GuestLayout
- ✅ **Аутентификация**: регистрация, вход/выход, защищенные маршруты
- ✅ **Авторизация**: права владельца/админа на посты и комментарии
- ✅ **Админ-панель**: доступ, статистика, управление контентом
- ✅ **REST API**: CRUD операции, JSON/XML ответы
- ✅ **API Resources**: трансформация данных
- ✅ **Навигация**: отображение кнопок для разных ролей
- ✅ **Валидация**: поля постов, изображения, комментарии
- ✅ **Связи моделей**: отношения между Post, Comment, User, Category, Tag

### Примеры тестов
```bash
# Тесты прав доступа
php artisan test tests/Feature/PostPermissionsTest.php
php artisan test tests/Feature/CommentPermissionsTest.php

# Тесты админ-панели
php artisan test tests/Feature/Admin/AdminDashboardTest.php

# Тесты API
php artisan test tests/Feature/Api/PostControllerTest.php
php artisan test tests/Feature/Api/CommentControllerTest.php

# Тесты сервисов
php artisan test tests/Unit/Services/PostServiceTest.php

# Тесты компонентов
php artisan test tests/Unit/View/Components/AppLayoutTest.php
```

### Генерация отчета о покрытии
```bash
# HTML отчет (откройте coverage-html/index.html)
php artisan test --coverage-html coverage-html

# Текстовый отчет
php artisan test --coverage-text

# XML отчет для CI/CD
php artisan test --coverage-clover coverage.xml
```

**Отчет о покрытии:** `file:///C:/Foxminded/Task-12/coverage-html/index.html`

## �� Безопасность

- **CSRF-защита** форм
- **Серверная валидация** данных
- **Авторизация по ролям** (is_admin флаг + Spatie Permission)
- **Middleware защита** маршрутов
- **API Token аутентификация** для REST API
- **Защита от SQL-инъекций** (Eloquent/Query Builder)
- **XSS-защита** в Blade шаблонах
- **Проверка прав** на уровне контроллеров и middleware

## ⚡ Производительность

- **Eager Loading** (`with`) для уменьшения количества запросов
- **Кеширование** (при необходимости)
- **Пагинация** списков
- **Ленивая загрузка** изображений
- **Сервисы** для бизнес-логики
- **API Resources** для оптимизации JSON ответов

## �� Развертывание

### Продакшн
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Создать администратора на продакшне
php artisan tinker
User::create([
    'name' => 'Admin',
    'email' => 'admin@yoursite.com',
    'password' => bcrypt('secure_password'),
    'is_admin' => true,
    'email_verified_at' => now()
]);
```

### Переменные окружения (пример)
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## �� Участие в проекте

1. Форкните репозиторий
2. Создайте ветку `feature/new-feature`
3. Внесите изменения + добавьте тесты
4. Убедитесь, что все тесты проходят: `php artisan test`
5. Проверьте покрытие кода: `php artisan test --coverage-html`
6. Откройте Pull Request

## 📝 Лицензия

Проект распространяется по лицензии MIT. См. файл `LICENSE`.

## 📞 Поддержка

- Откройте Issue в репозитории
- Email: support@example.com
- Документация: (ссылка при необходимости)

## 🎯 Roadmap

### Версия 2.0
- [ ] Публичное API (для мобильных клиентов) ✅
- [ ] Система уведомлений
- [ ] Экспорт в PDF
- [ ] Многоязычность интерфейса
- [ ] Система плагинов
- [ ] Расширенная система ролей

### Версия 2.1
- [ ] WYSIWYG редактор
- [ ] Полнотекстовый поиск
- [ ] RSS-ленты
- [ ] Интеграция с соцсетями
- [ ] Система лайков/дизлайков
- [ ] Email-уведомления

## 🏆 Особенности реализации

### Архитектура
- **Service Layer** — бизнес-логика вынесена в сервисы
- **Middleware** — централизованная проверка прав доступа
- **Repository Pattern** — через Eloquent ORM
- **Request Validation** — валидация на уровне контроллеров
- **API Resources** — трансформация данных для API

### Безопасность
- **Двойная защита** — и в middleware, и в контроллерах
- **Проверка владельца** — пользователи могут редактировать только свой контент
- **Админские права** — полный доступ для администраторов
- **API Token** — безопасная аутентификация для API

### Тестирование
- **150+ автотестов** — полное покрытие функциональности
- **Feature тесты** — тестирование пользовательских сценариев
- **Unit тесты** — тестирование отдельных компонентов
- **API тесты** — тестирование REST API
- **Middleware тесты** — проверка системы прав доступа

---

**Сделано с ❤️ на Laravel 12**

🎉 **Готово к использованию!** Запустите `php artisan   serve` и откройте http://localhost:8000