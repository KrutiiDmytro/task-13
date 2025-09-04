# Инструкция по запуску проекта

## Быстрый запуск для проверки

```bash
# 1. Установить зависимости
composer install

# 2. Настроить окружение
cp .env.example .env
php artisan key:generate

# 3. Настроить базу данных в .env файле
# Открыть .env и прописать настройки БД

# 4. Создать базу и заполнить данными
php artisan migrate:fresh --seed

# 5. Запустить сервер
php artisan serve
```

## Доступ в админ-панель

- **URL:** http://localhost:8000/admin
- **Email:** admin@admin.com  
- **Пароль:** admin123

## Что будет создано

- ✅ Категории (4 шт.)
- ✅ Теги (несколько)
- ✅ Посты (несколько с контентом)
- ✅ Комментарии
- ✅ Администратор с правами
- ✅ Роли и разрешения

## Тестирование

```bash
php artisan test
```
```

## **Шаг 3: Выполнить команды**

```powershell
# Добавить файлы
git add .

# Коммит
git commit -m "feat: add complete setup instructions and admin seeder with roles"

# Отправить
git push origin main
```