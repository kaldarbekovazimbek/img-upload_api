# Image Uploader API

REST API для загрузки и хранения изображений. Загруженные файлы автоматически сжимаются в WebP и дедуплицируются.

## Требования

- PHP >= 8.2 с расширениями: `gd`, `pdo_mysql`, `mbstring`, `openssl`
- MySQL >= 8.0
- Composer

Проверить расширения:
```bash
php -m | grep -E "gd|pdo_mysql|mbstring|openssl"
```

---

## Установка

### 1. Клонировать репозиторий

```bash
git clone <repo-url>
cd img-uploder-api
```

### 2. Установить зависимости

```bash
composer install
```

### 3. Создать `.env`

```bash
cp .env.example .env
```

### 4. Настроить `.env`

```env
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=img_uploader
DB_USERNAME=root
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
```

### 5. Сгенерировать ключи

```bash
# Ключ приложения
php artisan key:generate

# Секрет для JWT
php artisan jwt:secret
```

### 6. Запустить миграции

```bash
php artisan migrate
```

### 7. Создать символическую ссылку на storage

```bash
php artisan storage:link
```

---

## Запуск

### Разработка (все процессы одной командой)

```bash
composer dev
```

Запускает одновременно:
- `php artisan serve` — HTTP сервер на `http://localhost:8000`
- `php artisan queue:listen` — обработчик очереди (сжатие изображений)
- `php artisan pail` — логи в реальном времени

### Запуск по отдельности

```bash
# HTTP сервер
php artisan serve

# Обработчик очереди (обязательно, иначе изображения останутся в статусе pending)
php artisan queue:work
```

---

## API

Базовый URL: `http://localhost:8000/api`

Все ответы в формате:
```json
{
    "success": true,
    "code": null,
    "message": "...",
    "data": {}
}
```

### Аутентификация

| Метод | Эндпоинт | Описание |
|-------|----------|----------|
| POST | `/user/register` | Регистрация |
| POST | `/user/login` | Вход, возвращает JWT токен |
| POST | `/user/logout` | Выход |
| POST | `/user/refresh-token` | Обновить токен |

Все защищённые маршруты требуют заголовок:
```
Authorization: Bearer <token>
```

### Изображения (требуют авторизацию)

| Метод | Эндпоинт | Описание |
|-------|----------|----------|
| GET | `/image` | Список изображений (15 на страницу) |
| GET | `/image/{id}` | Получить изображение по ID |
| POST | `/image/upload` | Загрузить изображение |
| DELETE | `/image/{id}` | Удалить изображение |

#### POST `/image/upload`

Тело запроса: `multipart/form-data`

| Поле | Тип | Описание |
|------|-----|----------|
| `image` | file | Изображение (jpeg, png, gif, webp) |

Ответ `202 Accepted`:
```json
{
    "success": true,
    "code": null,
    "message": "Image accepted, processing in background.",
    "data": { "id": 5, "status": "pending", "name": "photo.jpg" }
}
```

Статусы изображения:
- `pending` — обрабатывается в фоне
- `ready` — готово к использованию
- `failed` — ошибка при обработке

#### GET `/image?page=2`

Пагинация через query-параметр `page`.

---

## Полезные команды

```bash
# Запустить тесты
composer test

# Проверить код (PHPStan)
composer analyse

# Исправить стиль кода
composer cs-fix
```

---

## Структура проекта

```
app/
├── Dto/                # Объекты передачи данных (spatie/laravel-data)
├── Enums/              # ApiCode, ImageStatus
├── Http/
│   ├── Controllers/    # ImageController, AuthController
│   ├── Requests/       # Form Request валидация
│   └── Responses/      # ApiResponse — единый формат ответов
├── Jobs/               # ProcessImageJob — сжатие в фоне
├── Models/             # Image, User
├── Repository/         # ImageRepository
├── Services/           # ImageService — сжатие в WebP
└── UseCase/            # StoreImageUseCase, DeleteImageUseCase
```
