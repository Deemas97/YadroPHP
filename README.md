# YadroPHP

[🇷🇺 Русский](#русская-версия) | [🇺🇸 English](#english-version)

<div align="center">
  <img src="public/assets/img/logo.png" alt="YadroPHP Logo" width="120" height="120">
  <h1>YadroPHP</h1>
  <p>
    <strong>Легковесный, высокопроизводительный PHP-фреймворк для современных веб-приложений</strong>
  </p>
  
  <p>
    <a href="https://packagist.org/packages/yadro/framework">
      <img src="https://img.shields.io/packagist/v/yadro/framework" alt="Packagist Version">
    </a>
    <a href="https://opensource.org/licenses/GPL-3.0">
      <img src="https://img.shields.io/badge/license-GPL-blue" alt="License GPL">
    </a>
    <img src="https://img.shields.io/badge/PHP-8.5+-purple" alt="PHP 8.5+">
    <img src="https://img.shields.io/badge/Size-~500_KB-green" alt="Size ~500KB">
    <img src="https://img.shields.io/badge/Performance-3--10ms-brightgreen" alt="Performance 3-10ms">
    <img src="https://img.shields.io/badge/Architecture-MVC-orange" alt="Architecture MVC">
    <img src="https://img.shields.io/badge/Status-Active-success" alt="Status Active">
  </p>
  
  <p>
    <a href="#быстрый-старт">Быстрый старт</a> •
    <a href="#особенности">Особенности</a> •
    <a href="#структура-проекта">Структура</a> •
    <a href="#документация">Документация</a> •
    <a href="#поддержка">Поддержка</a>
  </p>
</div>

---

<div id="русская-версия"></div>

## 🇷🇺 Русская версия

### 📖 О фреймворке

**YadroPHP** — это современный, легковесный PHP-фреймворк с открытым исходным кодом, разработанный в России. Созданный на базе PHP 8.5, фреймворк предлагает минималистичное ядро (~500 КБ) без потери производительности и безопасности. Идеально подходит для веб-приложений и REST API, которые требуют высокой скорости работы и низкого потребления ресурсов.

**Ключевые преимущества:**
- 🚀 **Высокая производительность:** 3-10 мс время отклика
- 📦 **Минимальный размер:** Ядро всего ~500 КБ
- 🔒 **Встроенная безопасность:** CSP, CORS, CSRF защита
- 🏗️ **Чистая архитектура:** Слоистая структура (Bootstrap, Core, Infrastructure, App)
- 🇷🇺 **Российская разработка:** Локальная поддержка и сообщество

### 🚀 Быстрый старт

#### Требования
- **PHP 8.5** или выше
- **Расширения:** `opcache` (с JIT), `mysqli`, `mbstring`, `json`, `openssl`
- **Веб-сервер:** Apache или Nginx
- **Рекомендуется:** 128 МБ RAM, 1 ГБ дискового пространства

#### Установка

##### Способ 1: Клонирование репозитория (рекомендуется для разработки)
```bash
# Клонируйте репозиторий
git clone https://github.com/yadrophp/framework.git ваш-проект
cd ваш-проект

# Настройте окружение
cp env.example env.local

# Отредактируйте env.local под ваши нужды
# Укажите настройки базы данных, режим работы и т.д.
```

##### Способ 2: Composer (скоро)
```bash
composer create-project yadro/framework ваш-проект
```

#### Запуск

##### Разработка (development mode):
```bash
# Запуск встроенного веб-сервера PHP
php -S localhost:8000 -t public

# Или используйте CLI для управления проектом
php bin/console/jit_manager.php --optimize
```

##### Продакшн (production mode):
```bash
# Настройте веб-сервер (Apache/Nginx) на директорию public/
# Установите env.local с режимом PRODUCTION
# Включите OPcache и JIT в настройках PHP
```

#### Ваш первый контроллер

Создайте файл `src/App/Controller/Web/HelloController.php`:

```php
<?php
namespace App\Controller\Web;

use Core\Controller\ControllerRendering;
use Core\Controller\ControllerResponseInterface;
use Core\Service\Renderer;

class HelloController extends ControllerRendering
{
    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        return $this->render('hello.html.php', [
            'message' => 'Привет от YadroPHP!',
            'version' => '1.0.0'
        ]);
    }
    
    public function apiExample(): ControllerResponseInterface
    {
        return $this->json([
            'status' => 'success',
            'data' => [
                'framework' => 'YadroPHP',
                'version' => '1.0.0',
                'performance' => '3-10ms'
            ]
        ]);
    }
}
```

Добавьте маршрут в `configs/routes.php`:

```php
<?php
return [
    'web' => [
        ['GET', '/hello', 'App\Controller\Web\HelloController::index'],
        ['GET', '/api/hello', 'App\Controller\Web\HelloController::apiExample'],
    ],
    'api' => [
        // API маршруты
    ]
];
```

Создайте шаблон `templates/hello.html.php`:

```php
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($message) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($message) ?></h1>
        <p>Версия фреймворка: <?= htmlspecialchars($version) ?></p>
        <p>Добро пожаловать в YadroPHP!</p>
    </div>
</body>
</html>
```

### ✨ Особенности

#### 🏗️ Архитектура и проектирование

**Слоистая архитектура:**
```
Bootstrap    → Автозагрузка, конфигурация, инициализация
Core         → Ядро фреймворка (роутинг, DI, middleware)
Infrastructure → Инфраструктура (БД, кэш, файловая система)
App          → Ваше приложение (контроллеры, сервисы, модели)
```

**Шаблоны проектирования:**
- **Chain of Responsibility:** Конвейер middleware-компонентов
- **Dependency Injection:** Внедрение зависимостей через конструктор
- **State:** Выбор сценария выполнения (dev/test/production)
- **Factory:** Создание объектов через контейнер
- **Strategy:** Различные реализации сервисов

#### 🔒 Безопасность

**Многоуровневая система безопасности:**

1. **Content Security Policy (CSP)**
   ```php
   // configs/content_security_policy.php
   return [
       'default-src' => "'self'",
       'script-src' => "'self' 'unsafe-inline'",
       'style-src' => "'self' 'unsafe-inline'",
       'img-src' => "'self' data: https:",
   ];
   ```

2. **CORS (Cross-Origin Resource Sharing)**
   - Контроль доступа к API
   - Настройка разрешенных источников
   - Предзапросы (preflight) поддержка

3. **CSRF Protection**
   - Автоматическая генерация токенов
   - Валидация всех POST/PUT/PATCH/DELETE запросов
   - Интеграция с формами

4. **Аутентификация и авторизация**
   - JWT или сессионная аутентификация
   - Ролевая модель доступа
   - Атрибуты контроля доступа к методам

5. **Защита входных данных**
   - Автоматическое экранирование HTML
   - Валидация типов данных
   - Санитизация пользовательского ввода

6. **ClosureMiddleware**
   - Предотвращение ошибок безопасности в пользовательских middleware
   - Изоляция выполнения
   - Контроль доступа к системным ресурсам

#### ⚡ Производительность

**Оптимизации:**

1. **JIT-компиляция (PHP 8.5)**
   ```php
   // bin/console/jit_manager.php
   opcache_compile_file($file); // Предварительная компиляция
   ```

2. **Многоуровневое кэширование**
   - Кэш маршрутов
   - Кэш шаблонов
   - Кэш конфигураций
   - Кэш запросов к БД

3. **Gzip сжатие**
   - Автоматическое сжатие ответов
   - Поддержка brotli (если доступно)
   - Настройка уровня сжатия

4. **Оптимизированный роутер**
   - Быстрый поиск маршрутов
   - Кэширование дерева маршрутов
   - Поддержка параметров и ограничений

#### 🛠️ Инструменты разработчика

**Встроенные инструменты:**

1. **CLI Консоль**
   ```bash
   # Оптимизация производительности
   php bin/console/jit_manager.php --optimize
   
   # Генерация документации API
   php bin/console/api_doc_generator.php
   
   # Очистка кэша
   php bin/console/cache_clear.php
   ```

2. **Dev Mode Features**
   - Детальное логирование
   - Профайлер запросов
   - Отладчик переменных
   - Мониторинг ресурсов

3. **Assets Watcher**
   - Автоматическая перекомпиляция CSS/JS
   - Hot reload для разработки
   - Минификация ресурсов

4. **API Documentation Generator**
   - Автогенерация документации
   - Swagger/OpenAPI совместимость
   - Интерактивная документация

#### 🗃️ Работа с данными

**База данных:**

```php
<?php
namespace App\Service;

use Infrastructure\DataBase\MySQLConnector;
use Core\Service\DBConnectionManager;

class UserService
{
    private MySQLConnector $db;
    
    public function __construct(DBConnectionManager $dbManager)
    {
        $this->db = $dbManager->getConnection('default');
    }
    
    public function getUsers(int $limit = 10): array
    {
        $query = "SELECT id, username, email FROM users 
                  WHERE active = 1 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        return $this->db->executePrepared($query, [$limit]);
    }
    
    public function createUser(array $data): int
    {
        $query = "INSERT INTO users (username, email, password_hash) 
                  VALUES (?, ?, ?)";
        
        return $this->db->executePrepared(
            $query, 
            [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT)
            ]
        );
    }
}
```

**Миграции:**
```php
// Пример миграции
class CreateUsersTable
{
    public function up(MySQLConnector $db): void
    {
        $db->execute("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
                             ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    }
}
```

### 📁 Структура проекта

```
.
├── bin/                            # CLI скрипты
│   └── console/
│       ├── jit_manager.php        # Управление JIT компиляцией
│       └── preload.php            # Preloading скрипты
│
├── configs/                       # Конфигурации
│   ├── content_security_policy.php # CSP настройки
│   ├── middleware/                # Конфигурации middleware
│   │   ├── app.php               # Пользовательские middleware
│   │   └── closure.php           # Closure middleware настройки
│   └── routes.php                # Маршруты приложения
│
├── public/                        # Публичная директория
│   ├── assets/                   # Статические ресурсы
│   │   ├── css/                  # Стили
│   │   ├── img/                  # Изображения
│   │   │   └── logo.png          # Логотип
│   │   └── js/                   # JavaScript файлы
│   └── index.php                 # Точка входа
│
├── src/                          # Исходный код
│   ├── App/                      # Ваше приложение
│   │   ├── Controller/          # Контроллеры
│   │   │   ├── Api/            # API контроллеры
│   │   │   └── Web/            # Web контроллеры
│   │   ├── Middleware/          # Пользовательские middleware
│   │   │   └── TestMiddleware.php
│   │   └── Service/             # Сервисы приложения
│   │       └── RouterMediator.php
│   │
│   ├── Bootstrap/               # Загрузчик приложения
│   │   ├── Autoloader.php      # Автозагрузчик
│   │   ├── AutoloaderPsr4.php  # PSR-4 автозагрузчик
│   │   └── Config/             # Конфигурация загрузки
│   │       ├── DotEnv.php      # Загрузка .env файлов
│   │       └── ProjectMode.php # Определение режима работы
│   │
│   ├── Core/                   # Ядро фреймворка
│   │   ├── Config/            # Конфигурации ядра
│   │   │   ├── AppMiddlewareConfig.php
│   │   │   └── RoutesConfig.php
│   │   ├── Container/         # DI контейнер
│   │   │   ├── ContainerInterface.php
│   │   │   ├── ControllerContainer.php
│   │   │   ├── GlobalContainer.php
│   │   │   ├── ReflectionContainerInterface.php
│   │   │   ├── Reflector.php
│   │   │   ├── RoutesContainerInterface.php
│   │   │   ├── RoutesContainer.php
│   │   │   ├── SharingContainerInterface.php
│   │   │   └── SharingContainer.php
│   │   ├── Controller/        # Базовые контроллеры
│   │   │   ├── ControllerAbstract.php
│   │   │   ├── ControllerRendering.php
│   │   │   ├── ControllerResponseInterface.php
│   │   │   └── ControllerResponse.php
│   │   ├── Logger/           # Логирование
│   │   │   └── LoggerInterface.php
│   │   ├── MessageBus/       # Шина сообщений
│   │   │   ├── ActionsInterface.php
│   │   │   ├── Actions.php
│   │   │   ├── MessageBusInterface.php
│   │   │   ├── RequestInterface.php
│   │   │   ├── Request.php
│   │   │   ├── ResponseInterface.php
│   │   │   └── Response.php
│   │   ├── Middleware/       # Middleware ядра
│   │   │   ├── ActionsMiddleware.php
│   │   │   ├── AppMiddlewareInterface.php
│   │   │   ├── ClosureMiddleware.php
│   │   │   ├── CompressionMiddleware.php
│   │   │   ├── CoreMiddlewareInterface.php
│   │   │   ├── MiddlewareInterface.php
│   │   │   ├── RequestMiddleware.php
│   │   │   ├── ResponseMiddleware.php
│   │   │   ├── SecurityMiddleware.php
│   │   │   └── SpecificMiddlewareInterface.php
│   │   ├── Pipeline/         # Конвейер middleware
│   │   │   ├── AppMiddlewarePipeline.php
│   │   │   ├── CombinedPipeline.php
│   │   │   ├── CoreMiddlewarePipeline.php
│   │   │   ├── PipelineInterface.php
│   │   │   ├── Pipeline.php
│   │   │   └── SpecificMiddlewarePipeline.php
│   │   ├── Router/          # Маршрутизация
│   │   │   ├── RouteInterface.php
│   │   │   ├── Route.php
│   │   │   ├── RouterInterface.php
│   │   │   └── Router.php
│   │   ├── Security/        # Безопасность
│   │   │   ├── AttributeInterface.php
│   │   │   ├── AuthAttribute.php
│   │   │   └── CsrfAttribute.php
│   │   ├── Service/         # Сервисы ядра
│   │   │   ├── AuthService/
│   │   │   │   └── User.php
│   │   │   ├── AuthService.php
│   │   │   ├── Configurer/
│   │   │   │   ├── ModeStateDev.php
│   │   │   │   ├── ModeStateInterface.php
│   │   │   │   ├── ModeStateProduction.php
│   │   │   │   └── ModeStateTest.php
│   │   │   ├── Configurer.php
│   │   │   ├── CookiesManager.php
│   │   │   ├── CoreServiceInterface.php
│   │   │   ├── CsrfService.php
│   │   │   ├── DBConnectionManager.php
│   │   │   ├── GzipCompressor.php
│   │   │   ├── InfrastructureServiceInterface.php
│   │   │   ├── PipelineService.php
│   │   │   ├── Renderer/
│   │   │   │   ├── HtmlMinificator.php
│   │   │   │   └── TemplatesCachingService.php
│   │   │   ├── Renderer.php
│   │   │   ├── ServiceInterface.php
│   │   │   ├── ServiceProviderInterface.php
│   │   │   └── SessionManager.php
│   │   └── View/           # Представления
│   │       ├── ViewInterface.php
│   │       └── View.php
│   │
│   ├── Dev/                # Инструменты разработки
│   │   ├── ApiDocGenerator.php
│   │   ├── AssetsWatcher.php
│   │   ├── Controller/
│   │   │   ├── Api/
│   │   │   │   ├── AssetsWatcherApiController.php
│   │   │   │   └── CacheApiController.php
│   │   │   └── Web/
│   │   ├── DBLogger.php
│   │   ├── Dumper.php
│   │   ├── HttpInspector.php
│   │   ├── PerformanceProfiler.php
│   │   └── ResourcesMonitor.php
│   │
│   ├── Domain/            # Доменная логика (DDD)
│   │   ├── DTO/          # Data Transfer Objects
│   │   ├── Entity/       # Сущности
│   │   └── Repository/   # Репозитории
│   │
│   ├── Infrastructure/   # Инфраструктурный слой
│   │   ├── Cache/       # Кэширование
│   │   │   └── OpCacheManager.php
│   │   ├── Cli/         # CLI инструменты
│   │   │   └── CliViewer.php
│   │   ├── Config/      # Конфигурация инфраструктуры
│   │   │   ├── AppConfig.php
│   │   │   └── ContentSecurityPolicy.php
│   │   ├── DataBase/    # Работа с БД
│   │   │   ├── DBConnectorInterface.php
│   │   │   └── MySQLConnector.php
│   │   ├── FileSystem/  # Файловая система
│   │   ├── Http/        # HTTP компоненты
│   │   │   ├── Attribute/
│   │   │   │   ├── Auth.php
│   │   │   │   ├── Route.php
│   │   │   └── Validate.php
│   │   │   ├── Client/
│   │   │   │   ├── CurlHttpClient.php
│   │   │   │   └── HttpClientInterface.php
│   │   │   ├── Protocol.php
│   │   │   └── ServerData.php
│   │   ├── Jit/        # JIT компиляция
│   │   │   └── JitManager.php
│   │   ├── Mail/       # Работа с почтой
│   │   ├── Queue/      # Очереди задач
│   │   └── Service/    # Инфраструктурные сервисы
│   │       ├── DevModeManager.php
│   │       └── Timer.php
│   │
│   └── Kernel.php      # Ядро приложения
│
├── templates/          # Шаблоны представлений
├── tests/             # Тесты (PHPUnit)
└── var/               # Временные файлы
    ├── cache/         # Кэш
    │   ├── dev/       # Кэш для разработки
    │   └── templates/ # Кэш шаблонов
    └── log/           # Логи
        └── dev/       # Логи разработки
            ├── http/  # HTTP логи
            └── profiles/ # Профайлы производительности
```

### 🎯 Сравнение с другими фреймворками

| Функция | YadroPHP | Laravel | Symfony | Slim |
|---------|----------|---------|---------|------|
| **Размер ядра** | ~500 КБ | ~30 МБ | ~40 МБ | ~2 МБ |
| **Время инициализации** | 1-3 мс | 50-100 мс | 70-150 мс | 5-10 мс |
| **Потребление памяти** | 5-15 МБ | 40-80 МБ | 50-100 МБ | 10-25 МБ |
| **Требуемая версия PHP** | 8.5+ | 8.1+ | 8.2+ | 8.0+ |
| **Встроенная безопасность** | CSP, CORS, CSRF | Базовая | Базовая | Минимальная |
| **JIT поддержка** | Да (оптимизировано) | Да | Да | Нет |
| **Российская поддержка** | Прямая | Через сообщество | Через сообщество | Через сообщество |

### 📚 Документация

Полная документация доступна на сайте: [yadrophp.ru](https://yadrophp.ru)

#### Разделы документации:
1. **📖 Руководство по установке**
   - Системные требования
   - Установка на разных ОС
   - Настройка веб-серверов

2. **🏗️ Архитектура фреймворка**
   - Обзор слоев
   - Поток выполнения
   - Контейнер зависимостей

3. **🚀 Быстрый старт**
   - Создание первого приложения
   - Работа с маршрутами
   - Создание контроллеров

4. **🔒 Безопасность**
   - Настройка CSP
   - Работа с CORS
   - CSRF защита
   - Аутентификация

5. **🗃️ Работа с базой данных**
   - Подключение MySQL
   - Выполнение запросов
   - Миграции
   - Оптимизация запросов

6. **⚡ Оптимизация производительности**
   - Настройка OPcache
   - Использование JIT
   - Кэширование
   - Профайлинг

7. **🎨 Frontend разработка**
   - Работа с шаблонами
   - Assets управление
   - JavaScript интеграция

8. **🚀 Деплоймент**
   - Настройка production
   - Мониторинг
   - Резервное копирование

9. **🔧 API Reference**
   - Классы и методы
   - Интерфейсы
   - Расширения

### 🤝 Поддержка и сообщество

#### Официальные каналы:
- **🌐 Официальный сайт:** [yadrophp.ru](https://yadrophp.ru)
- **📦 Packagist:** [yadro/framework](https://packagist.org/packages/yadro/framework)
- **🐙 GitHub:** [github.com/yadrophp](https://github.com/yadrophp)

#### Сообщества:
- **💬 VK сообщество:** [vk.com/yadrophp](https://vk.com/yadrophp) - Основное сообщество
- **📱 Telegram канал:** [@yadrophp](https://t.me/yadrophp) - Анонсы и новости
- **💭 Max сообщество:** [max.im/yadrophp](https://max.im/yadrophp) - Обсуждение и помощь
- **📚 Документация:** [docs.yadrophp.ru](https://docs.yadrophp.ru) - Полная документация

#### Поддержка разработки:
- **🐛 Баг-репорты:** [GitHub Issues](https://github.com/yadrophp/framework/issues)
- **💡 Запросы функций:** [GitHub Discussions](https://github.com/yadrophp/framework/discussions)
- **👥 Контрибьютинг:** [CONTRIBUTING.md](CONTRIBUTING.md)

### 🛣️ Roadmap

#### Версия 1.0 (Текущая)
- [x] Базовый MVC каркас
- [x] Маршрутизация
- [x] DI контейнер
- [x] Middleware конвейер
- [x] Базовая безопасность (CSP, CORS, CSRF)
- [x] Работа с MySQL
- [x] Шаблонизация
- [x] CLI инструменты
- [x] Инструменты разработки

#### Версия 1.1 (Q1 2024)
- [ ] Поддержка PostgreSQL
- [ ] Очереди задач (Queue)
- [ ] Отправка email
- [ ] Расширенная аутентификация (JWT, OAuth2)
- [ ] GraphQL поддержка
- [ ] Docker-образы
- [ ] Unit тесты покрытие 80%

#### Версия 1.2 (Q2 2024)
- [ ] Поддержка Redis
- [ ] WebSocket сервер
- [ ] Микросервисная архитектура
- [ ] Мониторинг и метрики
- [ ] Internationalization (i18n)
- [ ] Админ-панель генератор

#### Версия 2.0 (Q3 2024)
- [ ] PHP 8.6+ поддержка
- [ ] Фиберы (Fibers) поддержка
- [ ] AI-интеграции
- [ ] Модульная архитектура
- [ ] Marketplace пакетов
- [ ] Полная документация на английском

### 👥 Участие в разработке

Мы приветствуем участие в разработке YadroPHP!

#### Как помочь проекту:
1. **Тестирование:** Попробуйте фреймворк в своих проектах
2. **Документация:** Помогите улучшить документацию
3. **Код:** Предложите улучшения или исправления
4. **Перевод:** Помогите с переводом на другие языки
5. **Продвижение:** Расскажите о фреймворке

#### Процесс контрибьютинга:
1. Форкните репозиторий
2. Создайте ветку для своей фичи
3. Внесите изменения
4. Напишите тесты
5. Создайте Pull Request
6. Обсудите изменения с командой

#### Требования к коду:
- Следуйте PSR-1, PSR-2, PSR-4, PSR-12
- Пишите комментарии на русском или английском
- Добавляйте тесты для новой функциональности
- Обновляйте документацию

### 📄 Лицензия

YadroPHP распространяется под лицензией **GNU General Public License v3.0 (GPL-3.0)**.

#### Основные положения:
- ✅ **Свободное использование:** Можно использовать в коммерческих проектах
- ✅ **Модификация:** Можно изменять исходный код
- ✅ **Распространение:** Можно распространять копии
- 🔄 **Copyleft:** Измененные версии должны быть под той же лицензией

#### Для коммерческого использования:
1. Вы можете использовать YadroPHP в коммерческих проектах
2. Если вы модифицируете ядро фреймворка, вы должны открыть эти изменения
3. Приложения, построенные на YadroPHP, могут иметь свою лицензию

Полный текст лицензии: [LICENSE](LICENSE)

---

<div id="english-version"></div>

## 🇺🇸 English Version

### 📖 About the Framework

**YadroPHP** is a modern, lightweight PHP framework with open source, developed in Russia. Built on PHP 8.5, the framework offers a minimalistic core (~500 KB) without compromising performance and security. Perfect for web applications and REST APIs that require high speed and low resource consumption.

**Key Advantages:**
- 🚀 **High Performance:** 3-10 ms response time
- 📦 **Minimal Size:** Core only ~500 KB
- 🔒 **Built-in Security:** CSP, CORS, CSRF protection
- 🏗️ **Clean Architecture:** Layered structure (Bootstrap, Core, Infrastructure, App)
- 🇷🇺 **Russian Development:** Local support and community

### 🚀 Quick Start

#### Requirements
- **PHP 8.5** or higher
- **Extensions:** `opcache` (with JIT), `mysqli`, `mbstring`, `json`, `openssl`
- **Web Server:** Apache or Nginx
- **Recommended:** 128 MB RAM, 1 GB disk space

#### Installation

##### Method 1: Clone Repository (recommended for development)
```bash
# Clone the repository
git clone https://github.com/yadrophp/framework.git your-project
cd your-project

# Configure environment
cp env.example env.local

# Edit env.local according to your needs
# Set database settings, mode, etc.
```

##### Method 2: Composer (coming soon)
```bash
composer create-project yadro/framework your-project
```

#### Running

##### Development (development mode):
```bash
# Run built-in PHP web server
php -S localhost:8000 -t public

# Or use CLI for project management
php bin/console/jit_manager.php --optimize
```

##### Production (production mode):
```bash
# Configure web server (Apache/Nginx) to point to public/
# Set env.local with PRODUCTION mode
# Enable OPcache and JIT in PHP settings
```

#### Your First Controller

Create file `src/App/Controller/Web/HelloController.php`:

```php
<?php
namespace App\Controller\Web;

use Core\Controller\ControllerRendering;
use Core\Controller\ControllerResponseInterface;
use Core\Service\Renderer;

class HelloController extends ControllerRendering
{
    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        return $this->render('hello.html.php', [
            'message' => 'Hello from YadroPHP!',
            'version' => '1.0.0'
        ]);
    }
    
    public function apiExample(): ControllerResponseInterface
    {
        return $this->json([
            'status' => 'success',
            'data' => [
                'framework' => 'YadroPHP',
                'version' => '1.0.0',
                'performance' => '3-10ms'
            ]
        ]);
    }
}
```

Add route to `configs/routes.php`:

```php
<?php
return [
    'web' => [
        ['GET', '/hello', 'App\Controller\Web\HelloController::index'],
        ['GET', '/api/hello', 'App\Controller\Web\HelloController::apiExample'],
    ],
    'api' => [
        // API routes
    ]
];
```

Create template `templates/hello.html.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($message) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($message) ?></h1>
        <p>Framework version: <?= htmlspecialchars($version) ?></p>
        <p>Welcome to YadroPHP!</p>
    </div>
</body>
</html>
```

### ✨ Features

#### 🏗️ Architecture and Design

**Layered Architecture:**
```
Bootstrap    → Autoloading, configuration, initialization
Core         → Framework core (routing, DI, middleware)
Infrastructure → Infrastructure (DB, cache, file system)
App          → Your application (controllers, services, models)
```

**Design Patterns:**
- **Chain of Responsibility:** Middleware component pipeline
- **Dependency Injection:** Constructor dependency injection
- **State:** Execution scenario selection (dev/test/production)
- **Factory:** Object creation through container
- **Strategy:** Different service implementations

#### 🔒 Security

**Multi-level Security System:**

1. **Content Security Policy (CSP)**
   ```php
   // configs/content_security_policy.php
   return [
       'default-src' => "'self'",
       'script-src' => "'self' 'unsafe-inline'",
       'style-src' => "'self' 'unsafe-inline'",
       'img-src' => "'self' data: https:",
   ];
   ```

2. **CORS (Cross-Origin Resource Sharing)**
   - API access control
   - Allowed origins configuration
   - Preflight request support

3. **CSRF Protection**
   - Automatic token generation
   - All POST/PUT/PATCH/DELETE request validation
   - Form integration

4. **Authentication & Authorization**
   - JWT or session authentication
   - Role-based access model
   - Method access control attributes

5. **Input Data Protection**
   - Automatic HTML escaping
   - Data type validation
   - User input sanitization

6. **ClosureMiddleware**
   - Prevention of security errors in custom middleware
   - Execution isolation
   - System resource access control

#### ⚡ Performance

**Optimizations:**

1. **JIT Compilation (PHP 8.5)**
   ```php
   // bin/console/jit_manager.php
   opcache_compile_file($file); // Pre-compilation
   ```

2. **Multi-level Caching**
   - Route cache
   - Template cache
   - Configuration cache
   - Database query cache

3. **Gzip Compression**
   - Automatic response compression
   - Brotli support (if available)
   - Compression level configuration

4. **Optimized Router**
   - Fast route searching
   - Route tree caching
   - Parameter and constraint support

#### 🛠️ Developer Tools

**Built-in Tools:**

1. **CLI Console**
   ```bash
   # Performance optimization
   php bin/console/jit_manager.php --optimize
   
   # API documentation generation
   php bin/console/api_doc_generator.php
   
   # Cache clearing
   php bin/console/cache_clear.php
   ```

2. **Dev Mode Features**
   - Detailed logging
   - Request profiler
   - Variable debugger
   - Resource monitoring

3. **Assets Watcher**
   - Automatic CSS/JS recompilation
   - Hot reload for development
   - Resource minification

4. **API Documentation Generator**
   - Auto-generated documentation
   - Swagger/OpenAPI compatibility
   - Interactive documentation

#### 🗃️ Data Management

**Database:**

```php
<?php
namespace App\Service;

use Infrastructure\DataBase\MySQLConnector;
use Core\Service\DBConnectionManager;

class UserService
{
    private MySQLConnector $db;
    
    public function __construct(DBConnectionManager $dbManager)
    {
        $this->db = $dbManager->getConnection('default');
    }
    
    public function getUsers(int $limit = 10): array
    {
        $query = "SELECT id, username, email FROM users 
                  WHERE active = 1 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        return $this->db->executePrepared($query, [$limit]);
    }
    
    public function createUser(array $data): int
    {
        $query = "INSERT INTO users (username, email, password_hash) 
                  VALUES (?, ?, ?)";
        
        return $this->db->executePrepared(
            $query, 
            [
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT)
            ]
        );
    }
}
```

**Migrations:**
```php
// Example migration
class CreateUsersTable
{
    public function up(MySQLConnector $db): void
    {
        $db->execute("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
                             ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
        ");
    }
}
```

### 📁 Project Structure

```
.
├── bin/                            # CLI scripts
│   └── console/
│       ├── jit_manager.php        # JIT compilation management
│       └── preload.php            # Preloading scripts
│
├── configs/                       # Configurations
│   ├── content_security_policy.php # CSP settings
│   ├── middleware/                # Middleware configurations
│   │   ├── app.php               # Custom middleware
│   │   └── closure.php           # Closure middleware settings
│   └── routes.php                # Application routes
│
├── public/                        # Public directory
│   ├── assets/                   # Static resources
│   │   ├── css/                  # Styles
│   │   ├── img/                  # Images
│   │   │   └── logo.png          # Logo
│   │   └── js/                   # JavaScript files
│   └── index.php                 # Entry point
│
├── src/                          # Source code
│   ├── App/                      # Your application
│   │   ├── Controller/          # Controllers
│   │   │   ├── Api/            # API controllers
│   │   │   └── Web/            # Web controllers
│   │   ├── Middleware/          # Custom middleware
│   │   │   └── TestMiddleware.php
│   │   └── Service/             # Application services
│   │       └── RouterMediator.php
│   │
│   ├── Bootstrap/               # Application bootstrapper
│   │   ├── Autoloader.php      # Autoloader
│   │   ├── AutoloaderPsr4.php  # PSR-4 autoloader
│   │   └── Config/             # Loading configuration
│   │       ├── DotEnv.php      # .env file loading
│   │       └── ProjectMode.php # Operation mode detection
│   │
│   ├── Core/                   # Framework core
│   │   ├── Config/            # Core configurations
│   │   │   ├── AppMiddlewareConfig.php
│   │   │   └── RoutesConfig.php
│   │   ├── Container/         # DI container
│   │   │   ├── ContainerInterface.php
│   │   │   ├── ControllerContainer.php
│   │   │   ├── GlobalContainer.php
│   │   │   ├── ReflectionContainerInterface.php
│   │   │   ├── Reflector.php
│   │   │   ├── RoutesContainerInterface.php
│   │   │   ├── RoutesContainer.php
│   │   │   ├── SharingContainerInterface.php
│   │   │   └── SharingContainer.php
│   │   ├── Controller/        # Base controllers
│   │   │   ├── ControllerAbstract.php
│   │   │   ├── ControllerRendering.php
│   │   │   ├── ControllerResponseInterface.php
│   │   │   └── ControllerResponse.php
│   │   ├── Logger/           # Logging
│   │   │   └── LoggerInterface.php
│   │   ├── MessageBus/       # Message bus
│   │   │   ├── ActionsInterface.php
│   │   │   ├── Actions.php
│   │   │   ├── MessageBusInterface.php
│   │   │   ├── RequestInterface.php
│   │   │   ├── Request.php
│   │   │   ├── ResponseInterface.php
│   │   │   └── Response.php
│   │   ├── Middleware/       # Core middleware
│   │   │   ├── ActionsMiddleware.php
│   │   │   ├── AppMiddlewareInterface.php
│   │   │   ├── ClosureMiddleware.php
│   │   │   ├── CompressionMiddleware.php
│   │   │   ├── CoreMiddlewareInterface.php
│   │   │   ├── MiddlewareInterface.php
│   │   │   ├── RequestMiddleware.php
│   │   │   ├── ResponseMiddleware.php
│   │   │   ├── SecurityMiddleware.php
│   │   │   └── SpecificMiddlewareInterface.php
│   │   ├── Pipeline/         # Middleware pipeline
│   │   │   ├── AppMiddlewarePipeline.php
│   │   │   ├── CombinedPipeline.php
│   │   │   ├── CoreMiddlewarePipeline.php
│   │   │   ├── PipelineInterface.php
│   │   │   ├── Pipeline.php
│   │   │   └── SpecificMiddlewarePipeline.php
│   │   ├── Router/          # Routing
│   │   │   ├── RouteInterface.php
│   │   │   ├── Route.php
│   │   │   ├── RouterInterface.php
│   │   │   └── Router.php
│   │   ├── Security/        # Security
│   │   │   ├── AttributeInterface.php
│   │   │   ├── AuthAttribute.php
│   │   │   └── CsrfAttribute.php
│   │   ├── Service/         # Core services
│   │   │   ├── AuthService/
│   │   │   │   └── User.php
│   │   │   ├── AuthService.php
│   │   │   ├── Configurer/
│   │   │   │   ├── ModeStateDev.php
│   │   │   │   ├── ModeStateInterface.php
│   │   │   │   ├── ModeStateProduction.php
│   │   │   │   └── ModeStateTest.php
│   │   │   ├── Configurer.php
│   │   │   ├── CookiesManager.php
│   │   │   ├── CoreServiceInterface.php
│   │   │   ├── CsrfService.php
│   │   │   ├── DBConnectionManager.php
│   │   │   ├── GzipCompressor.php
│   │   │   ├── InfrastructureServiceInterface.php
│   │   │   ├── PipelineService.php
│   │   │   ├── Renderer/
│   │   │   │   ├── HtmlMinificator.php
│   │   │   │   └── TemplatesCachingService.php
│   │   │   ├── Renderer.php
│   │   │   ├── ServiceInterface.php
│   │   │   ├── ServiceProviderInterface.php
│   │   │   └── SessionManager.php
│   │   └── View/           # Views
│   │       ├── ViewInterface.php
│   │       └── View.php
│   │
│   ├── Dev/                # Development tools
│   │   ├── ApiDocGenerator.php
│   │   ├── AssetsWatcher.php
│   │   ├── Controller/
│   │   │   ├── Api/
│   │   │   │   ├── AssetsWatcherApiController.php
│   │   │   │   └── CacheApiController.php
│   │   │   └── Web/
│   │   ├── DBLogger.php
│   │   ├── Dumper.php
│   │   ├── HttpInspector.php
│   │   ├── PerformanceProfiler.php
│   │   └── ResourcesMonitor.php
│   │
│   ├── Domain/            # Domain logic (DDD)
│   │   ├── DTO/          # Data Transfer Objects
│   │   ├── Entity/       # Entities
│   │   └── Repository/   # Repositories
│   │
│   ├── Infrastructure/   # Infrastructure layer
│   │   ├── Cache/       # Caching
│   │   │   └── OpCacheManager.php
│   │   ├── Cli/         # CLI tools
│   │   │   └── CliViewer.php
│   │   ├── Config/      # Infrastructure configuration
│   │   │   ├── AppConfig.php
│   │   │   └── ContentSecurityPolicy.php
│   │   ├── DataBase/    # Database operations
│   │   │   ├── DBConnectorInterface.php
│   │   │   └── MySQLConnector.php
│   │   ├── FileSystem/  # File system
│   │   ├── Http/        # HTTP components
│   │   │   ├── Attribute/
│   │   │   │   ├── Auth.php
│   │   │   │   ├── Route.php
│   │   │   └── Validate.php
│   │   │   ├── Client/
│   │   │   │   ├── CurlHttpClient.php
│   │   │   │   └── HttpClientInterface.php
│   │   │   ├── Protocol.php
│   │   │   └── ServerData.php
│   │   ├── Jit/        # JIT compilation
│   │   │   └── JitManager.php
│   │   ├── Mail/       # Email operations
│   │   ├── Queue/      # Task queues
│   │   └── Service/    # Infrastructure services
│   │       ├── DevModeManager.php
│   │       └── Timer.php
│   │
│   └── Kernel.php      # Application kernel
│
├── templates/          # View templates
├── tests/             # Tests (PHPUnit)
└── var/               # Temporary files
    ├── cache/         # Cache
    │   ├── dev/       # Development cache
    │   └── templates/ # Template cache
    └── log/           # Logs
        └── dev/       # Development logs
            ├── http/  # HTTP logs
            └── profiles/ # Performance profiles
```

### 🎯 Comparison with Other Frameworks

| Feature | YadroPHP | Laravel | Symfony | Slim |
|---------|----------|---------|---------|------|
| **Core Size** | ~500 KB | ~30 MB | ~40 MB | ~2 MB |
| **Init Time** | 1-3 ms | 50-100 ms | 70-150 ms | 5-10 ms |
| **Memory Usage** | 5-15 MB | 40-80 MB | 50-100 MB | 10-25 MB |
| **PHP Version Required** | 8.5+ | 8.1+ | 8.2+ | 8.0+ |
| **Built-in Security** | CSP, CORS, CSRF | Basic | Basic | Minimal |
| **JIT Support** | Yes (optimized) | Yes | Yes | No |
| **Russian Support** | Direct | Community | Community | Community |

### 📚 Documentation

Full documentation is available at: [yadrophp.ru](https://yadrophp.ru)

#### Documentation Sections:
1. **📖 Installation Guide**
   - System requirements
   - Installation on different OS
   - Web server configuration

2. **🏗️ Framework Architecture**
   - Layer overview
   - Execution flow
   - Dependency container

3. **🚀 Quick Start**
   - Creating first application
   - Working with routes
   - Creating controllers

4. **🔒 Security**
   - CSP configuration
   - CORS handling
   - CSRF protection
   - Authentication

5. **🗃️ Database Operations**
   - MySQL connection
   - Query execution
   - Migrations
   - Query optimization

6. **⚡ Performance Optimization**
   - OPcache configuration
   - JIT usage
   - Caching
   - Profiling

7. **🎨 Frontend Development**
   - Template handling
   - Assets management
   - JavaScript integration

8. **🚀 Deployment**
   - Production setup
   - Monitoring
   - Backup

9. **🔧 API Reference**
   - Classes and methods
   - Interfaces
   - Extensions

### 🤝 Support and Community

#### Official Channels:
- **🌐 Official Website:** [yadrophp.ru](https://yadrophp.ru)
- **📦 Packagist:** [yadro/framework](https://packagist.org/packages/yadro/framework)
- **🐙 GitHub:** [github.com/yadrophp](https://github.com/yadrophp)

#### Communities:
- **💬 VK Community:** [vk.com/yadrophp](https://vk.com/yadrophp) - Main community
- **📱 Telegram Channel:** [@yadrophp](https://t.me/yadrophp) - Announcements and news
- **💭 Max Community:** [max.im/yadrophp](https://max.im/yadrophp) - Discussion and help
- **📚 Documentation:** [docs.yadrophp.ru](https://docs.yadrophp.ru) - Full documentation

#### Development Support:
- **🐛 Bug Reports:** [GitHub Issues](https://github.com/yadrophp/framework/issues)
- **💡 Feature Requests:** [GitHub Discussions](https://github.com/yadrophp/framework/discussions)
- **👥 Contributing:** [CONTRIBUTING.md](CONTRIBUTING.md)

### 🛣️ Roadmap

#### Version 1.0 (Current)
- [x] Basic MVC structure
- [x] Routing
- [x] DI container
- [x] Middleware pipeline
- [x] Basic security (CSP, CORS, CSRF)
- [x] MySQL operations
- [x] Templating
- [x] CLI tools
- [x] Development tools

#### Version 1.1 (Q1 2024)
- [ ] PostgreSQL support
- [ ] Task queues (Queue)
- [ ] Email sending
- [ ] Extended authentication (JWT, OAuth2)
- [ ] GraphQL support
- [ ] Docker images
- [ ] Unit test coverage 80%

#### Version 1.2 (Q2 2024)
- [ ] Redis support
- [ ] WebSocket server
- [ ] Microservice architecture
- [ ] Monitoring and metrics
- [ ] Internationalization (i18n)
- [ ] Admin panel generator

#### Version 2.0 (Q3 2024)
- [ ] PHP 8.6+ support
- [ ] Fibers support
- [ ] AI integrations
- [ ] Modular architecture
- [ ] Package marketplace
- [ ] Full English documentation

### 👥 Contributing

We welcome contributions to YadroPHP development!

#### How to help the project:
1. **Testing:** Try the framework in your projects
2. **Documentation:** Help improve documentation
3. **Code:** Suggest improvements or fixes
4. **Translation:** Help with translations to other languages
5. **Promotion:** Tell others about the framework

#### Contribution process:
1. Fork the repository
2. Create a branch for your feature
3. Make changes
4. Write tests
5. Create Pull Request
6. Discuss changes with the team

#### Code requirements:
- Follow PSR-1, PSR-2, PSR-4, PSR-12
- Write comments in Russian or English
- Add tests for new functionality
- Update documentation

### 📄 License

YadroPHP is distributed under the **GNU General Public License v3.0 (GPL-3.0)**.

#### Main provisions:
- ✅ **Free Use:** Can be used in commercial projects
- ✅ **Modification:** Can modify source code
- ✅ **Distribution:** Can distribute copies
- 🔄 **Copyleft:** Modified versions must be under same license

#### For commercial use:
1. You can use YadroPHP in commercial projects
2. If you modify the framework core, you must open source those changes
3. Applications built on YadroPHP can have their own license

Full license text: [LICENSE](LICENSE)

---

<div align="center">
  <sub>Built with ❤️ in Russia</sub><br>
  <sub>YadroPHP © 2024</sub><br>
  <sub>Lightweight • Fast • Secure</sub>
</div>
```
