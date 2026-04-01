<?php
namespace Bootstrap;

class AutoloaderPsr4 implements AutoloaderInterface
{
    private array $prefixes = [];
    private array $classMap = [];
    private array $missingClasses = [];
    private ?string $cacheFile = null;
    private bool $cacheLoaded = false;
    private bool $cacheDirty = false;
    
    private const CACHED_LAYERS = [
        'Bootstrap\\',
        'Core\\',
        'Infrastructure\\'
    ];
    
    private const DYNAMIC_LAYERS = [
        'App\\',
        'Domain\\',
        'Dev\\'
    ];

    public function __construct(?string $cacheDir = null)
    {
        if ($cacheDir !== null) {
            $this->cacheFile = rtrim($cacheDir, DIRECTORY_SEPARATOR) . '/autoloader_cache.php';
            $this->loadCache();
        }
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass'], true, true);
        register_shutdown_function([$this, 'saveCache']);
    }

    public function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = [];
        }
        
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $baseDir);
        } else {
            $this->prefixes[$prefix][] = $baseDir;
        }
    }

    public function loadClass(string $class): ?string
    {
        // Проверяем missing cache
        if (isset($this->missingClasses[$class])) {
            return null;
        }

        // Проверяем classMap
        if (isset($this->classMap[$class])) {
            require $this->classMap[$class];
            return $this->classMap[$class];
        }
        
        // Ищем файл
        $file = $this->findFile($class);
        
        if ($file !== null && file_exists($file)) {
            // Сохраняем в classMap
            $this->classMap[$class] = $file;
            
            // Помечаем для кэширования только нужные слои
            if ($this->shouldCacheClass($class)) {
                $this->cacheDirty = true;
            }
            
            require $file;
            return $file;
        }
        
        // Запоминаем отсутствующие классы (но не кэшируем их)
        $this->missingClasses[$class] = true;
        return null;
    }

    /**
     * Проверка, нужно ли кэшировать класс
     */
    private function shouldCacheClass(string $class): bool
    {
        // Не кэшируем App слой
        if (in_array($class, self::DYNAMIC_LAYERS) === true) {
            return false;
        }
        
        // Кэшируем только указанные слои
        foreach (self::CACHED_LAYERS as $layer) {
            if (strpos($class, $layer) === 0) {
                return true;
            }
        }
        
        return false;
    }

    private function findFile(string $class): ?string
    {
        $prefix = $class;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            
            if ($file = $this->loadMappedFile($prefix, $class)) {
                return $file;
            }
            
            $prefix = rtrim($prefix, '\\');
        }
        
        return null;
    }

    private function loadMappedFile(string $prefix, string $relativeClass): ?string
    {
        if (!isset($this->prefixes[$prefix])) {
            return null;
        }
        
        foreach ($this->prefixes[$prefix] as $baseDir) {
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            
            if (file_exists($file)) {
                return $file;
            }
        }
        
        return null;
    }

    /**
     * Загрузка кэша из файла
     */
    private function loadCache(): void
    {
        if ($this->cacheLoaded || !$this->cacheFile || !file_exists($this->cacheFile)) {
            return;
        }

        $cached = require $this->cacheFile;
        
        if (is_array($cached) && isset($cached['classMap'], $cached['timestamp'])) {
            // Проверяем возраст кэша (не старше 24 часов)
            if (time() - $cached['timestamp'] < 86400) {
                // Загружаем только кэшированные слои
                foreach ($cached['classMap'] as $class => $file) {
                    if ($this->shouldCacheClass($class)) {
                        $this->classMap[$class] = $file;
                    }
                }
            }
        }
        
        $this->cacheLoaded = true;
    }

    /**
     * Сохранение кэша (только для нужных слоев)
     */
    public function saveCache(): bool
    {
        if (!$this->cacheDirty || !$this->cacheFile) {
            return false;
        }

        // Собираем только классы из кэшируемых слоев
        $cachedClassMap = [];
        foreach ($this->classMap as $class => $file) {
            if ($this->shouldCacheClass($class)) {
                $cachedClassMap[$class] = $file;
            }
        }

        // Если нет классов для кэширования, выходим
        if (empty($cachedClassMap)) {
            return false;
        }

        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheData = [
            'classMap' => $cachedClassMap,
            'timestamp' => time(),
            'stats' => [
                'total' => count($cachedClassMap),
                'layers' => self::CACHED_LAYERS
            ]
        ];

        // Используем ваш второй вариант с красивым форматированием
        $content = ('<?php return ' . $this->arrayExport($cacheData) . ';');
        
        // Атомарная запись
        $tmpFile = ($this->cacheFile . '.tmp.' . uniqid());
        if (file_put_contents($tmpFile, $content, LOCK_EX) !== false) {
            // Проверяем синтаксис перед заменой
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($tmpFile, true);
            }
            
            rename($tmpFile, $this->cacheFile);
            $this->cacheDirty = false;
            
            // Очищаем opcache для файла кэша
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($this->cacheFile, true);
            }
            
            return true;
        }

        return false;
    }

    /**
     * Красивый экспорт массива в PHP код
     */
    private function arrayExport(array $data, int $depth = 0): string
    {
        if (empty($data)) {
            return '[]';
        }

        $indent = str_repeat('    ', $depth);
        $entries = [];

        foreach ($data as $key => $value) {
            $keyString = is_string($key) ? "'" . addslashes($key) . "'" : $key;
            
            if (is_array($value)) {
                $entries[] = $indent . "    $keyString => " . $this->arrayExport($value, $depth + 1);
            } elseif (is_string($value)) {
                $entries[] = $indent . "    $keyString => '" . addslashes($value) . "'";
            } elseif (is_int($value) || is_float($value)) {
                $entries[] = $indent . "    $keyString => $value";
            } elseif (is_bool($value)) {
                $entries[] = $indent . "    $keyString => " . ($value ? 'true' : 'false');
            } elseif ($value === null) {
                $entries[] = $indent . "    $keyString => null";
            } else {
                $entries[] = $indent . "    $keyString => '" . addslashes((string)$value) . "'";
            }
        }

        return "[\n" . implode(",\n", $entries) . "\n" . $indent . "]";
    }

    /**
     * Очистка кэша
     */
    public function clearCache(): bool
    {
        // Очищаем только кэшированные классы
        foreach (array_keys($this->classMap) as $class) {
            if ($this->shouldCacheClass($class)) {
                unset($this->classMap[$class]);
            }
        }
        
        $this->cacheDirty = true;
        
        if ($this->cacheFile && file_exists($this->cacheFile)) {
            return unlink($this->cacheFile);
        }
        
        return true;
    }

    /**
     * Получение статистики
     */
    public function getStats(): array
    {
        $cachedCount = 0;
        $appCount = 0;
        
        foreach (array_keys($this->classMap) as $class) {
            if (in_array($class, self::DYNAMIC_LAYERS) === false) {
                ++$appCount;
            } else {
                ++$cachedCount;
            }
        }

        return [
            'registered_namespaces' => count($this->prefixes),
            'total_classes' => count($this->classMap),
            'cached_classes' => $cachedCount,
            'app_classes' => $appCount,
            'missing_classes' => count($this->missingClasses),
            'cache_file' => $this->cacheFile,
            'cache_dirty' => $this->cacheDirty,
            'cached_layers' => self::CACHED_LAYERS
        ];
    }
}