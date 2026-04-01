<?php
namespace Core\Middleware;

use Bootstrap\Config\ProjectMode;
use Core\MessageBus\MessageBusInterface;
use RuntimeException;

final class ClosureMiddleware implements CoreMiddlewareInterface
{
    private array $securityConfig;
    private array $validationRules;
    private bool $isConfigured = false;
    private array $modifications = [];

    public function __construct()
    {
        $this->loadConfiguration();
    }

    private function loadConfiguration(): void
    {
        $config = require (YADRO_PHP__CONFIGS_DIR . '/middleware/closure.php');
        
        if (!is_array($config)) {
            throw new RuntimeException('Closure middleware config must return array');
        }

        $this->securityConfig = $config['security'];
        $this->validationRules = $config['validation'];
        
        $this->isConfigured = true;
    }

    public function process(MessageBusInterface $messageBus): MessageBusInterface
    {
        if (!$messageBus->has('response')) {
            return $messageBus;
        }

        $originalResponse = $messageBus->get('response');
        $this->modifications = [];
        
        try {
            // Шаг 1: Нормализация ответа (приведение к единому формату)
            $normalizedResponse = $this->normalizeResponse($originalResponse);
            
            // Шаг 2: Проверка структуры
            $validatedResponse = $this->validateStructure($normalizedResponse);
            
            // Шаг 3: Удаление чувствительных данных
            $cleanedResponse = $this->removeSensitiveData($validatedResponse);
            
            // Шаг 4: Санитизация от XSS
            $sanitizedResponse = $this->sanitizeResponse($cleanedResponse);
            
            // Шаг 5: Проверка размера
            $this->checkResponseSize($sanitizedResponse);
            
            // Шаг 6: Добавление заголовков безопасности
            $this->ensureSecurityHeaders($messageBus);
            
            // Шаг 7: Логирование изменений (если были)
            $this->logModifications($messageBus, $originalResponse, $sanitizedResponse);
            
            $messageBus->set('response', $sanitizedResponse);
            $messageBus->set('_closure_processed', true);
            $messageBus->set('_closure_modified', !empty($this->modifications));
            
        } catch (RuntimeException $e) {
            // Критическая ошибка - возвращаем безопасный fallback
            $this->handleCriticalError($messageBus, $e, $originalResponse);
        }

        return $messageBus;
    }

    /**
     * Нормализация ответа в единый формат
     */
    private function normalizeResponse($response): array
    {
        // Если уже массив, проверяем наличие обязательных полей
        if (is_array($response)) {
            return $response;
        }

        // Строка - превращаем в data
        if (is_string($response)) {
            $this->modifications[] = 'normalized_string_to_data';
            return [
                'status' => 'success',
                'data' => $response,
                'message' => null
            ];
        }

        // Число - тоже в data
        if (is_numeric($response)) {
            $this->modifications[] = 'normalized_number_to_data';
            return [
                'status' => 'success',
                'data' => $response,
                'message' => null
            ];
        }

        // Объект с методом toArray()
        if (is_object($response) && method_exists($response, 'toArray')) {
            $this->modifications[] = 'normalized_object_to_array';
            return $response->toArray();
        }

        // Boolean
        if (is_bool($response)) {
            $this->modifications[] = 'normalized_boolean_to_data';
            return [
                'status' => $response ? 'success' : 'error',
                'data' => $response,
                'message' => null
            ];
        }

        // Null
        if ($response === null) {
            $this->modifications[] = 'normalized_null_to_empty';
            return [
                'status' => 'success',
                'data' => null,
                'message' => null
            ];
        }

        // Всё остальное - ошибка
        $this->modifications[] = 'normalized_unknown_to_error';
        return [
            'status' => 'error',
            'data' => null,
            'message' => 'Invalid response format'
        ];
    }

    /**
     * Валидация структуры ответа
     */
    private function validateStructure(array $response): array
    {
        // Эти проверки делаем всегда (они дешевые)
        $this->checkDepth($response, $this->validationRules['max_depth']);

        // Дорогие проверки - только если есть изменения
        static $lastResponseHash = null;
        static $lastValidated = null;

        $currentHash = md5(serialize($response));

        if ($currentHash === $lastResponseHash && $lastValidated !== null) {
            return $lastValidated;
        }    
    
        $rules = $this->validationRules;

        // Проверка наличия status (если требуется)
        if ($rules['require_status_field'] && !isset($response['status'])) {
            $this->modifications[] = 'added_missing_status';
            $response['status'] = 'success';
        }

        // Валидация значения status
        if (isset($response['status']) && !in_array($response['status'], $rules['status_values'])) {
            $this->modifications[] = 'corrected_invalid_status';
            $response['status'] = 'error';
        }

        // Фильтрация запрещенных ключей
        if (!empty($rules['allowed_keys'])) {
            foreach (array_keys($response) as $key) {
                if (!in_array($key, $rules['allowed_keys']) && strpos($key, '_') !== 0) {
                    $this->modifications[] = "removed_disallowed_key:{$key}";
                    unset($response[$key]);
                }
            }
        }

        $lastResponseHash = $currentHash;
        $lastValidated = $response;

        return $response;
    }

    /**
     * Проверка глубины вложенности
     */
    private function checkDepth($data, int $maxDepth, int $currentDepth = 0): void
    {
        if ($currentDepth > $maxDepth) {
            throw new RuntimeException("Response exceeds maximum depth of {$maxDepth}");
        }

        if (is_array($data)) {
            foreach ($data as $value) {
                if (is_array($value) || is_object($value)) {
                    $this->checkDepth($value, $maxDepth, $currentDepth + 1);
                }
            }
        }
    }

    /**
     * Удаление чувствительных данных
     */
    private function removeSensitiveData(array $data): array
    {
        if (!$this->securityConfig['remove_sensitive_data']) {
            return $data;
        }

        $sensitiveKeys = $this->securityConfig['sensitive_keys'];
        
        array_walk_recursive($data, function(&$value, $key) use ($sensitiveKeys) {
            foreach ($sensitiveKeys as $sensitive) {
                if (stripos((string)$key, $sensitive) !== false) {
                    $this->modifications[] = "filtered_sensitive_key:{$key}";
                    $value = '[FILTERED]';
                    break;
                }
            }
        });

        return $data;
    }

    /**
     * Санитизация ответа
     */
    private function sanitizeResponse(array $response): array
    {
        if (!$this->validationRules['sanitize_strings']) {
            return $response;
        }

        $sanitized = [];
        
        foreach ($response as $key => $value) {
            // Санитизируем ключи (удаляем потенциально опасные символы)
            $cleanKey = $this->sanitizeKey($key);
            
            if ($cleanKey !== $key) {
                $this->modifications[] = "sanitized_key:{$key}->{$cleanKey}";
            }
            
            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeResponse($value);
            } elseif (is_string($value)) {
                $sanitized[$cleanKey] = $this->sanitizeString($value);
            } elseif (is_object($value)) {
                // Объекты преобразуем в массивы для безопасности
                $this->modifications[] = "converted_object_to_array:{$cleanKey}";
                $sanitized[$cleanKey] = $this->sanitizeResponse(
                    method_exists($value, 'toArray') ? $value->toArray() : (array)$value
                );
            } else {
                $sanitized[$cleanKey] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Санитизация строки
     */
    private function sanitizeString(string $value): string
    {
        $original = $value;
        $rules = $this->validationRules;

        if ($rules['trim_strings']) {
            $value = trim($value);
        }

        // Проверка опасных паттернов (ранний выход)
        foreach ($this->securityConfig['disallowed_patterns'] as $pattern) {
            if (preg_match($pattern, $value)) {
                $this->modifications[] = 'blocked_dangerous_pattern';
                throw new RuntimeException('Response contains dangerous content');
            }
        }

        if ($rules['strip_tags']) {
            $value = strip_tags($value);
        }

        if ($rules['escape_html']) {
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if ($value !== $original) {
            $this->modifications[] = 'sanitized_string_content';
        }

        return $value;
    }

    /**
     * Санитизация ключа массива
     */
    private function sanitizeKey(string $key): string
    {
        // Удаляем потенциально опасные символы из ключей
        $cleanKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        return $cleanKey ?: 'invalid_key';
    }

    /**
     * Проверка размера ответа
     */
    private function checkResponseSize(array $response): void
    {
        $maxSize = $this->securityConfig['max_response_size'];
        
        $jsonResponse = json_encode($response);
        if ($jsonResponse === false) {
            throw new RuntimeException('Failed to encode response to JSON');
        }
        
        $size = strlen($jsonResponse);
        
        if ($size > $maxSize) {
            $this->modifications[] = "response_truncated:{$size}>{$maxSize}";
            throw new RuntimeException(
                sprintf('Response size (%s bytes) exceeds maximum allowed size', $size)
            );
        }
    }

    /**
     * Добавление заголовков безопасности
     */
    private function ensureSecurityHeaders(MessageBusInterface $messageBus): void
    {
        $headers = $messageBus->get('response_headers') ?? [];
        
        foreach ($this->securityConfig['force_headers'] as $name => $value) {
            if (!isset($headers[$name])) {
                $headers[$name] = $value;
                $this->modifications[] = "added_header:{$name}";
            }
        }
        
        // Удаляем опасные заголовки
        $dangerousHeaders = ['X-Powered-By', 'Server', 'X-AspNet-Version'];
        foreach ($dangerousHeaders as $dangerous) {
            if (isset($headers[$dangerous])) {
                unset($headers[$dangerous]);
                $this->modifications[] = "removed_header:{$dangerous}";
            }
        }
        
        $messageBus->set('response_headers', $headers);
    }

    /**
     * Логирование модификаций
     */
    private function logModifications(
        MessageBusInterface $messageBus,
        $original,
        $modified
    ): void {
        if (empty($this->modifications)) {
            return;
        }

        $context = [
            'route' => $messageBus->get('route_uri', 'unknown'),
            'method' => $messageBus->get('route_http_method', 'unknown'),
            'modifications' => $this->modifications,
            'original_type' => gettype($original)
        ];

        // В dev режиме логируем подробно
        if (ProjectMode::getCurrentMode() === 'dev') {
            $context['original_sample'] = $this->getSample($original);
            $context['modified_sample'] = $this->getSample($modified);
        }
    }

    /**
     * Получение сэмпла данных для логирования
     */
    private function getSample($data, int $maxLength = 200): string
    {
        $encoded = json_encode($data);
        if ($encoded === false) {
            return '[unencodable]';
        }
        
        if (strlen($encoded) > $maxLength) {
            return substr($encoded, 0, $maxLength) . '...';
        }
        
        return $encoded;
    }

    /**
     * Обработка критической ошибки
     */
    private function handleCriticalError(
        MessageBusInterface $messageBus,
        RuntimeException $e,
        $originalResponse
    ): void {
        // В dev режиме пробрасываем исключение
        if (ProjectMode::getCurrentMode() === 'dev') {
            throw $e;
        }

        // В production возвращаем безопасный ответ
        $messageBus->set('response', [
            'status' => 'error',
            'message' => 'Internal server error',
            'data' => null
        ]);
        
        $messageBus->set('_closure_error', $e->getMessage());
        $messageBus->set('_closure_fallback', true);
    }

    /**
     * Получение статуса конфигурации
     */
    public function isCustomConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Получение списка модификаций для текущего запроса
     */
    public function getModifications(): array
    {
        return $this->modifications;
    }
}