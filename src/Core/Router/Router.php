<?php
namespace Core\Router;

use Core\Config\RoutesConfig;
use Core\Router\Route;
use Core\Router\RouteInterface;
use Core\Container\RoutesContainer;
use Core\Service\ServiceInterface;
use Core\Service\ServiceProviderInterface;

use App\Service\RouterMediator;
use Core\MessageBus\MessageBusInterface;
use Core\MessageBus\Request;
use Infrastructure\Http\ServerData;

class Router implements RouterInterface, ServiceProviderInterface
{
    private const array ERROR_ROUTES_MAP = [
        'error400' => '/error/400',
        'error404' => '/error/404',
        'error405' => '/error/405',
        'error406' => '/error/406',
        'error414' => '/error/414',
        'error500' => '/error/500'
    ];

    private const MAX_EXECUTION_TIME = 0.05;
    private const MAX_URI_LENGTH = 1024;
    private const MAX_PATH_SEGMENTS = 16;
    private const MAX_PATTERN_LENGTH = 256;
    private const PATTERN_COMPLEXITY_LIMIT = 5;

    private ?RouteInterface $currentRoute = null;
    private array $staticRoutes = [];
    private array $dynamicRoutes = [];

    public function __construct(
        private readonly ServerData      $serverData,
        private readonly RoutesConfig    $config,
        private readonly RoutesContainer $routes
    )
    {
        $this->initRoutes();
    }

    public function initMessageBus(): MessageBusInterface
    {
        $messageBus = new Request();

        $messageBus->set('request_time', microtime(true));
        $messageBus->set('request_id',   uniqid('req_', true));

        $route = $this->resolve(
            explode('?', $this->serverData->getUri())[0],
            ($this->serverData->getMethod() ?? 'GET')
        );

        $messageBus->set('route_controller_name',  $route->getControllerName());
        $messageBus->set('route_method_name',      $route->getMethodName());
        $messageBus->set('route_http_method',      $route->getHttpMethod());
        $messageBus->set('route_parameters',       $route->getParameters());
        $messageBus->set('route_query_string',     $this->serverData->getQueryString());
        
        return $messageBus;
    }

    private function initRoutes(): void
    {
        while (($routeData = $this->config->extractRouteData()) !== null) {
            if (strpos($routeData['path'], '{') !== false && !$this->isPatternSafe($routeData['path'])) {
                error_log("Unsafe route pattern detected: " . $routeData['path']);
                continue;
            }

            $route = new Route(
                $routeData['path'],
                $routeData['http_method'],
                $routeData['is_xhr'] ?? false,
                $routeData['controller'],
                $routeData['controller_method'],
            );

            if (isset($routeData['parameters'])) {
                $route->setParameters($routeData['parameters']);
            }
            
            $this->routes->set($routeData['path'], $route);

            if (strpos($routeData['path'], '{') !== false) {
                $this->dynamicRoutes[$routeData['path']] = $route;
            } else {
                $this->staticRoutes[$routeData['path']] = $route;
            }
        }
    }

    private function resolve(string $requestUri, string $httpMethod): RouteInterface
    {
        // Валидация длины URI
        if (strlen($requestUri) > self::MAX_URI_LENGTH) {
            $this->logSecurityEvent('URI too long', ['length' => strlen($requestUri)]);
            return $this->getErrorRoute('error414');
        }

        $uri = trim(strtok($requestUri, '?'), '/');
        
        // Защита от path traversal
        $uri = $this->preventPathTraversal($uri);
        
        $segmentCount = (substr_count($uri, '/') + 1);
        if ($segmentCount > self::MAX_PATH_SEGMENTS) {
            $this->logSecurityEvent('Too many path segments', ['count' => $segmentCount]);
            return $this->getErrorRoute('error414');
        }

        $normalizedUri = ('/' . $uri);

        // Быстрый поиск по статическим маршрутам
        if (isset($this->staticRoutes[$normalizedUri])) {
            return $this->validateRoute($httpMethod, $this->staticRoutes[$normalizedUri]);
        }

        // Поиск по динамическим маршрутам с защитой от ReDoS
        foreach ($this->dynamicRoutes as $pattern => $route) {
            // Пропускаем потенциально опасные паттерны
            if (!$this->isPatternSafe($pattern)) {
                continue;
            }
            
            $result = $this->matchDynamicRouteSafe($pattern, $uri);

            if ($result['matched']) {
                
                $filteredMatches = $this->filterMatches($result['matches']);
                $route->setParameters($filteredMatches);
                return $this->validateRoute($httpMethod, $route);
            }
        }

        return $this->getErrorRoute('error404');
    }

    private function matchDynamicRouteSafe(string $pattern, string $uri): array
    {
        // Нормализуем
        $normalizedPattern = trim($pattern, '/');
        $normalizedUri = trim($uri, '/');

        // Компилируем на лету
        $regex = $this->compilePattern($normalizedPattern);

        $startTime = microtime(true);
        $matched = preg_match($regex, $normalizedUri, $matches) === 1;
        $executionTime = microtime(true) - $startTime;

        // Только базовая защита от ReDoS
        if ($executionTime > self::MAX_EXECUTION_TIME) {
            error_log("Slow route match: $pattern, time: $executionTime");
            return ['matched' => false, 'matches' => []];
        }

        return [
            'matched' => $matched,
            'matches' => $matches
        ];
    }

    private function compilePattern(string $pattern): string
    {
        // Экранируем специальные символы regex
        $pattern = preg_quote($pattern, '@');

        // Возвращаем {param} в исходное состояние
        $pattern = str_replace(['\{', '\}'], ['{', '}'], $pattern);

        // Заменяем {param} на regex группу
        $regex = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $pattern);

        return '@^' . $regex . '$@u';
    }

    private function isPatternSafe(string $pattern): bool
    {
        // Проверка длины
        if (strlen($pattern) > self::MAX_PATTERN_LENGTH) {
            return false;
        }

        // Проверка количества параметров
        $parameterCount = substr_count($pattern, '{');
        if ($parameterCount > self::PATTERN_COMPLEXITY_LIMIT) {
            return false;
        }

        // Проверка на вложенные фигурные скобки
        if (preg_match('/\{[^{}]*\{/', $pattern)) {
            return false;
        }

        // Проверка количества сегментов
        $segmentCount = substr_count($pattern, '/');
        if ($segmentCount > self::MAX_PATH_SEGMENTS) {
            return false;
        }

        // Проверка на потенциально опасные символы
        if (preg_match('/[^\w\-\/{}]/u', $pattern)) {
            return false;
        }

        return true;
    }

    private function validateRoute(string $httpMethod, RouteInterface $route): RouteInterface
    {
        // Проверка HTTP метода
        if ($httpMethod !== $route->getHttpMethod()) {
            return $this->getErrorRoute('error405');
        }

        // Проверка XHR
        if ($this->serverData->isAjax() !== $route->isXhr()) {
            return $this->getErrorRoute('error406');
        }

        return $route;
    }

    private function preventPathTraversal(string $path): string
    {
        // Удаляем потенциально опасные последовательности
        $path = str_replace(['..', './', '\\'], '', $path);
        
        // Удаляем множественные слеши
        $path = preg_replace('#/+#', '/', $path);
        
        // Удаляем начальные и конечные слеши
        $path = trim($path, '/');
        
        return $path;
    }

    private function filterMatches(array $matches): array
    {
        // Оставляем только именованные параметры
        $filtered = [];
        foreach ($matches as $key => $value) {
            if (is_string($key) && is_string($value)) {
                // Дополнительная проверка длины значения
                if (strlen($value) <= 255) {
                    $filtered[$key] = $value;
                }
            }
        }
        return $filtered;
    }

    private function logSecurityEvent(string $message, array $context = []): void
    {
        $logData = [
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'time' => date('Y-m-d H:i:s'),
            'context' => $context
        ];
        
        error_log('SECURITY: ' . json_encode($logData));
    }

    private function getErrorRoute(string $errorCode): RouteInterface
    {
        $routePath = (self::ERROR_ROUTES_MAP[$errorCode] ?? '/error/400');
        $route = $this->routes->get($routePath);
        
        if (!$route) {
            // Создаем fallback маршрут если не найден
            $route = new Route(
                $routePath,
                'GET',
                false,
                'App\\Controller\\ErrorController',
                $errorCode
            );
        }
        
        $this->currentRoute = $route;
        return $route;
    }
    
    public function getCurrentRoute(): RouteInterface
    {
        return $this->currentRoute;
    }

    public function getRoutes(): RoutesContainer
    {
        return $this->routes;
    }

    public function getMediatorServiceName(): string
    {
        return RouterMediator::class;
    }

    public function getMediatorService(): ServiceInterface
    {
        return new RouterMediator($this);
    }
}