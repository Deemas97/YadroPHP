# YadroPHP

[🇷🇺 Русский](#русская-версия)

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
    <img src="https://img.shields.io/badge/Size-~650_KB-green" alt="Size ~500KB">
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
- 🚀 **Высокая производительность:** 3-10 мс мин.время отклика
- 📦 **Минимальный размер:** Ядро ~650 КБ
- 🔒 **Встроенная безопасность:** CSP, CORS, CSRF, Subresource Integrity
- 🏗️ **Чистая архитектура:** Слоистая структура (Bootstrap, Core, Infrastructure, App, Domain \[optional\])

### 🚀 Быстрый старт

#### Требования
- **PHP 8.5** или выше
- **Расширения:** `opcache` (с JIT), `mysqli`, `mbstring`, `json`, `openssl`
- **Веб-сервер:** Apache или Nginx
- **Рекомендуется:** 128 МБ RAM, 512 МБ дискового пространства

#### Установка

##### Способ 1: Клонирование репозитория (рекомендуется для разработки)
```bash
# Клонируйте репозиторий
git clone https://github.com/yadrophp/framework.git ваш-проект
cd ваш-проект

# Настройте окружение
cp .env.example .env.local

# Отредактируйте .env.example и .env.local под нужды разработки
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
php bin/console/jit_manager.php help
```

##### Продакшен (production mode):
```bash
# Настройте веб-сервер (Apache/Nginx) на директорию public/
# Удалите .env.local
# Включите OPcache, JIT и Gzip в настройках PHP
```

#### Ваш первый контроллер

Создайте файл `src/App/Controller/Web/MainController.php`:

```php
<?php
namespace App\Controller;

use Core\Controller\ControllerRendering;
use Core\Controller\ControllerResponseInterface;
use Core\Service\Renderer;

class MainController extends ControllerRendering
{
    public function __construct(Renderer $renderer)
    {
        parent::__construct($renderer);
    }

    public function index(): ControllerResponseInterface
    {
        return $this->render('main.html.php', [
            'message' => 'Привет от YadroPHP!',
            'version' => '1.0.0'
        ]);
    }
    
    public function apiExample(): ControllerResponseInterface
    {
        return $this->initJsonResponse(['success' => true, 'data' => 'Hello World!']);
    }
}
```

Добавьте маршрут в `configs/routes.php`:

```php
<?php
return [
    [
        'path' => '/',
        'http_method' => 'GET',
        'controller' => 'App\Controller\MainController',
        'controller_method' => 'index'
    ],
    [
        'path' => '/api/',
        'http_method' => 'GET',
        'controller' => 'App\Controller\MainController',
        'controller_method' => 'apiExample'
    ],
];
```

Создайте шаблон `templates/main.html.php`:

```php
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($message) ?></title>
    <link rel="stylesheet" href="<?= YADRO_PHP__ASSETS_DIR ?>/css/app.css">
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
Bootstrap            → Автозагрузка, конфигурация, инициализация
Core                 → Ядро фреймворка (роутинг, DI, middleware)
Infrastructure       → Инфраструктура (БД, кэш, файловая система)
App                  → Ваше приложение (контроллеры, сервисы, компоненты)
Domain (опционально) → Предметная область (DTO, бизнес-логика)
```

**Шаблоны проектирования:**
- **Chain of Responsibility:** Конвейер middleware-компонентов
- **Dependency Injection:** Внедрение зависимостей через конструктор
- **State:** Выбор сценария выполнения (dev/test/production)
- **Reflecting Factory:** Создание объектов через контейнер при помощи ReflectionAPI
- **Strategy:** Различные реализации сервисов

#### 🔒 Безопасность

**Многоуровневая система безопасности:**

1. **Content Security Policy (CSP)**
   ```php
   // configs/content_security_policy.php
   return [
    'production' => [
        'default-src' => ["'self'"],
        
        'script-src' => [
            "'self'",
            'https://cdn.jsdelivr.net',
            'https://code.jquery.com',
            'https://unpkg.com',
            "'nonce-{nonce}'",
            "'strict-dynamic'"
        ],
        
        'style-src' => [
            "'self'",
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://fonts.googleapis.com',
            "'nonce-{nonce}'"
        ],
        
        'img-src' => [
            "'self'",
            'data:',
            'blob:',
            'https:'
        ],
        
        'font-src' => [
            "'self'",
            'data:',
            'https://fonts.gstatic.com',
            'https://cdnjs.cloudflare.com'
        ],
        
        'connect-src' => [
            "'self'",
            'https://cdn.jsdelivr.net',
            'https://code.jquery.com'
        ],
        
        'worker-src' => ["'self'", 'blob:'],
        'child-src' => ["'self'", 'blob:'],
        'frame-src' => ["'self'"],
        
        'frame-ancestors' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'object-src' => ["'none'"],
        'manifest-src' => ["'self'"],
        
        'report-uri' => ['/csp-report'],
        'report-to' => ['csp-endpoint']
    ],
    
    'development' => [
        'default-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https:'],
        'style-src' => ["'self'", "'unsafe-inline'", 'https:'],
        'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
        'font-src' => ["'self'", 'data:', 'https:'],
        'connect-src' => ["'self'", 'https:'],
        'worker-src' => ["'self'", 'blob:'],
        'frame-src' => ["'self'"],
        'frame-ancestors' => ["'none'"]
    ],
    
    'test' => [
        'default-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
        'font-src' => ["'self'", 'data:', 'https:'],
        'frame-ancestors' => ["'none'"]
    ]
    ];
   ```

2. **CORS (Cross-Origin Resource Sharing)**
   - Контроль доступа к API
   - Настройка разрешенных источников
   - Предзапросы (preflight) поддержка

3. **CSRF Protection**
   - Генерация токенов
   - Поддержка валидации POST/PUT/PATCH/DELETE и др. запросов
   - Интеграция с формами
  
4. **Subresource Integrity**
   - Валидация хешей содержимого от внешних источников
   - Интеграция со стилями и скриптами

5. **Аутентификация и авторизация**
   - Сессионная аутентификация
   - Ролевая модель доступа
   - Атрибуты контроля доступа к методам

5. **Защита входных данных**
   - Поддержка экранирования HTML, SQL
   - Валидация типов данных
   - Санитизация пользовательского ввода

6. **ClosureMiddleware**
   - Предотвращение ошибок безопасности в пользовательских middleware
   - Изоляция выполнения

#### ⚡ Производительность

**Оптимизации:**

1. **JIT-компиляция (PHP 8.5)**
   - Включение/выключение предварительной компиляции
   - Гибкая настройка компонента кэширования
   - Ускорение выполнения объемных частей фреймворка

2. **Многоуровневое кэширование**
   - Кэш байт-кода (OpCache)
   - Кэш конфигураций
   - Кэш шаблонов

4. **Gzip сжатие**
   - Настраиваемое сжатие ответов, включая автоматический режим

5. **Оптимизированный роутер**
   - Быстрый поиск маршрутов
   - Поддержка HTTP-методов, запросов XhrHttpRequest, параметров и ограничений

#### 🛠️ Инструменты разработчика

**Встроенные инструменты:**

1. **CLI Консоль**
   ```bash
   # Оптимизация производительности
   php bin/console/jit_manager.php optimize
   
   # Прогрев OpCache
   php bin/console/preload.php
   ```

2. **Dev Mode Features**
   - Детальное логирование
   - Профайлер запросов
   - Отладчик переменных
   - Мониторинг ресурсов

3. **Assets Watcher (скоро)**
   - Автоматическая перекомпиляция CSS/JS
   - Hot reload для разработки

4. **API Documentation Generator (скоро)**
   - Автогенерация документации
   - Swagger/OpenAPI совместимость
   - Интерактивная документация

#### 🗃️ Работа с данными

**База данных:**

```php
<?php
namespace App\Controller\Web;

use Core\Controller\ControllerRendering;
use Core\Controller\ControllerResponseInterface;
use Core\Security\AuthAttribute;
use Core\Service\Renderer;
use Core\Service\AuthService;
use Core\Service\DBConnectionManager;

class UserController extends ControllerRendering
{
    public function __construct(
        Renderer $renderer,
        private AuthService $auth,
        private DBConnectionManager $dbManager
    )
    {
        parent::__construct($renderer);
    }

    #[AuthAttribute(table: 'employees', roles: ['admin', 'manager'], status: 'active')]
    public function index(): ControllerResponseInterface
    {
        $user = $this->auth->getUser();
        $userId = $this->auth->getUser()->getId();

        $dbConnection = $this->dbManager->getConnection();
        
        $sqlGetEmployees = "SELECT * FROM employees WHERE id = {$userId} LIMIT 1";
        $result = $dbConnection->query($sqlGetEmployees);

        $userData = $result[0];

        $data = [
            'title' => 'Профиль',
            'company_name' => 'YadroPHP',
            'user_session' => [
                'role' => $user->getRole(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar()
            ],

            'user_data' => $userData
        ];

        return $this->render('pages/profile.html.php', $data);
    }

    #[AuthAttribute(table: 'employees', roles: ['admin', 'manager'], status: 'active')]
    public function edit(): ControllerResponseInterface
    {
        $user = $this->auth->getUser();
        $userId = $this->auth->getUser()->getId();
        
        $dbConnection = $this->dbManager->getConnection();
        
        $sqlGetEmployees = "SELECT * FROM employees WHERE id = {$userId} LIMIT 1";
        $result = $dbConnection->query($sqlGetEmployees);

        $userData = $result[0];

        $data = [
            'title' => 'Профиль',
            'company_name' => 'YadroPHP',
            'user_session' => [
                'role' => $user->getRole(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar()
            ],

            'user_data' => $userData
        ];

        return $this->render('pages/profile_edit.html.php', $data);
    }
}
```

### 📚 Документация

Полная документация доступна и периодически обновляется
на сайте: [yadrophp.ru/docs](https://yadrophp.ru/docs)

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
   - Оптимизация запросов

6. **⚡ Оптимизация производительности**
   - Настройка OPcache
   - Использование JIT
   - Кэширование
   - Профайлинг

7. **🎨 Frontend разработка**
   - Работа с шаблонами
   - JavaScript интеграция

8. **🚀 Деплоймент**
   - Настройка production
   - Мониторинг

9. **🔧 API Reference**
   - Классы и методы
   - Интерфейсы
   - Расширения

### 🤝 Поддержка и сообщество (скоро)

#### Официальные каналы:
- **🌐 Официальный сайт:** [yadrophp.ru](https://yadrophp.ru)
- **🐙 GitHub:** [github.com/yadrophp](https://github.com/Deemas97/YadroPHP)

#### Сообщества:
- **💬 VK сообщество:** [vk.com/yadrophp](https://vk.com/yadrophp)
- **📚 Документация:** [docs.yadrophp.ru](https://docs.yadrophp.ru/docs)

### 👥 Участие в разработке

Мы приветствуем участие в разработке YadroPHP!

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
