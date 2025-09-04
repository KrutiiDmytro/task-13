# Инструкция по запуску проекта

## Быстрый запуск для проверки

```bash
# 1. Установить зависимости
composer install

# 2. Настроить окружение
cp .env.example .env
php artisan key:generate

# 3. Настроить базу данных в .env файле
# Открыть .env и указать: DB_DATABASE=blog_posts

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

### **2. Выполнить команды в PowerShell:**

```powershell
# Добавить файлы в git
git add .

# Сделать коммит
git commit -m "docs: add complete setup instructions for mentor"

# Отправить в репозиторий
git push origin main
```

### **3. Проверить что отправилось:**

```powershell
git status
```

**Должно показать:** `Your branch is up to date with 'origin/main'`
