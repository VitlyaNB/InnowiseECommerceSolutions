# Innowise E-Commerce Solutions

Современная E-Commerce платформа (интернет-магазин), разработанная с использованием монолитной архитектуры на **Laravel 12** (Backend) и **React 19** (Frontend, сборка через Vite).

Проект использует паттерны Clean Architecture (Service-Repository, DTO), полнотекстовый поиск через Elasticsearch, S3-совместимое хранилище (MinIO) и асинхронные очереди.

## 🚀 Требования к окружению

Для локального запуска вам потребуются только:
* [Docker](https://docs.docker.com/get-docker/)
* [Docker Compose](https://docs.docker.com/compose/install/)

## 🛠 Пошаговая инструкция по развёртыванию

**1. Клонируйте репозиторий и перейдите в его директорию:**
```bash
git clone <URL_ВАШЕГО_РЕПОЗИТОРИЯ>
cd innowiseecommercesolutions
```

**2. Подготовьте файл переменных окружения:**
Скопируйте `.env.example` в `.env`. В примере уже прописаны корректные доступы для связи сервисов внутри Docker-сети.
```bash
cp .env.example .env
```

**3. Запустите Docker-контейнеры:**
```bash
docker-compose up -d --build
```
> **Внимание:** При первом запуске контейнер `app` будет автоматически выполнять загрузку зависимостей (`composer install` и `npm install`). Это может занять несколько минут. Вы можете следить за процессом с помощью команды `docker-compose logs -f app`.

**4. Сгенерируйте ключ приложения и запустите миграции с сидерами:**
Когда контейнеры успешно запустятся (и база данных будет готова к приему соединений), выполните следующие команды:
```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

**5. Создайте бакет в MinIO:**
Приложение настроено на сохранение изображений в S3 хранилище. Вам нужно создать стартовый бакет:
1. Перейдите в панель управления MinIO: [http://localhost:9001](http://localhost:9001)
2. Введите логин: `minioadmin`, пароль: `minioadmin`
3. Перейдите в раздел **Buckets** и создайте бакет с названием `shop`.
4. В настройках созданного бакета (`Summary` -> `Access Policy`) установите политику доступа на **Public**, чтобы картинки успешно отображались на сайте.

**6. Проиндексируйте данные для поиска (Elasticsearch):**
Для корректной работы каталога и поиска, загрузите сгенерированные сидером товары в индекс:
```bash
docker-compose exec app php artisan scout:import "App\Models\Product"
```

## 7.  Демонстрационные данные (Seeding)

После выполнения команды `php artisan migrate --seed`, ваша база данных будет наполнена реалистичными данными для тестирования всех функций платформы.
```

## 🌐 Доступ к сервисам

* **Веб-сайт (React) и API (Laravel):** [http://localhost:8000](http://localhost:8000)
* **Vite HMR (для горячей перезагрузки):** `localhost:5173`
* **Swagger API Документация:** [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
* **MinIO Console (S3):** [http://localhost:9001](http://localhost:9001)
* **Elasticsearch API:** `localhost:9200`
* **База данных MySQL:** `localhost:3306` (Логин: `root` / Пароль: `pass`, База: `shop_db`)

## 🔐 Учетные данные по умолчанию

При выполнении миграций с флагом `--seed` автоматически создается учетная запись администратора с балансом:

* **Email:** `admin@example.com`
* **Пароль:** `password`
* **Роль:** `admin`

Войдите под этой учетной записью на сайте, чтобы получить доступ к маршруту панели управления `/admin`, где вы сможете добавлять товары, категории и управлять пользователями.

## 🏗 Архитектура контейнеров (`docker-compose.yml`)

1. **`app`** (PHP 8.3 + Node.js) — Серверная часть Laravel и Frontend сборщик (Vite).
2. **`worker`** — Демон Laravel очередей (`queue:work`), отвечающий за отправку Email-уведомлений и асинхронную синхронизацию категорий.
3. **`db`** — База данных MySQL 8.0.
4. **`minio`** — Локальный S3-сервер.
5. **`elasticsearch`** — Поисковый движок (версия 8.11) для работы быстрого и фасетного поиска товаров.

## ⚙️ Дополнительные команды

Перезапуск воркеров (если были изменены классы Job):
```bash
docker-compose restart worker
```

Запуск тестов внутри контейнера:
```bash
docker-compose exec app php artisan test
```
