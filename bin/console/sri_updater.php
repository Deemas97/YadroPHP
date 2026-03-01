#!/usr/bin/env php
<?php
/**
 * CLI Script для обновления SRI хешей
 * 
 * Использование:
 *   php bin/update-sri-hashes.php [--algo=sha384] [--force] [--url="https://example.com/file.js"]
 * 
 * Опции:
 *   --algo      Алгоритм хеширования (sha256, sha384, sha512)
 *   --force     Принудительное обновление всех хешей
 *   --url       Обновить хеш для конкретного URL
 *   --check     Только проверить, не обновлять
 */

// Определяем корневую директорию
define('YADRO_PHP__ROOT_DIR', realpath(__DIR__ . '/../'));
define('YADRO_PHP__CONFIGS_DIR', YADRO_PHP__ROOT_DIR . '/configs');

class SriHashUpdater
{
    private string $configFile;
    private array $config;
    private array $stats = [
        'total' => 0,
        'updated' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];
    
    private array $options;
    private const API_URL = 'https://www.srihash.org/api';
    private const USER_AGENT = 'YadroPHP SRI Updater/1.0';
    
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'algo' => 'sha384',
            'force' => false,
            'url' => null,
            'check' => false,
        ], $options);
        
        $this->configFile = YADRO_PHP__CONFIGS_DIR . '/subresource_integrity.php';
        $this->loadConfig();
    }
    
    /**
     * Загрузка конфигурации
     */
    private function loadConfig(): void
    {
        if (!file_exists($this->configFile)) {
            $this->error("Config file not found: {$this->configFile}");
            exit(1);
        }
        
        $this->config = require $this->configFile;
        
        if (!is_array($this->config)) {
            $this->error("Invalid config format");
            exit(1);
        }
    }
    
    /**
     * Сохранение конфигурации
     */
    private function saveConfig(): bool
    {
        // Создаем резервную копию
        $backupFile = $this->configFile . '.backup.' . date('Y-m-d_H-i-s');
        copy($this->configFile, $backupFile);
        
        // Форматируем конфиг для красивой записи
        $content = "<?php\n\nreturn " . $this->arrayExport($this->config) . ";\n";
        
        // Атомарная запись
        $tmpFile = $this->configFile . '.tmp.' . uniqid('', true);
        if (file_put_contents($tmpFile, $content, LOCK_EX) !== false) {
            rename($tmpFile, $this->configFile);
            $this->info("Backup saved to: " . basename($backupFile));
            return true;
        }
        
        return false;
    }
    
    /**
     * Красивый экспорт массива
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
     * Получение SRI хеша через API
     */
    private function fetchSriHash(string $url, string $algo = 'sha384'): ?string
    {
        $this->debug("Fetching hash for: $url");
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_URL . '?url=' . urlencode($url),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => self::USER_AGENT,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        unset($ch);
        
        if ($error) {
            $this->error("CURL Error for $url: $error");
            return null;
        }
        
        if ($httpCode !== 200) {
            $this->error("HTTP $httpCode for $url");
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!is_array($data) || !isset($data['result'])) {
            $this->error("Invalid API response for $url");
            return null;
        }
        
        // Ищем хеш для нужного алгоритма
        foreach ($data['result'] as $hash) {
            if (strpos($hash, $algo . '-') === 0) {
                return $hash;
            }
        }
        
        $this->error("No $algo hash found for $url");
        return null;
    }
    
    /**
     * Обновление хеша для URL
     */
    private function updateHash(string $url, string $algo, bool $checkOnly = false): bool
    {
        $this->stats['total']++;
        
        $currentHash = $this->config['hashes'][$algo][$url] ?? null;
        
        // Получаем новый хеш
        $newHash = $this->fetchSriHash($url, $algo);
        
        if (!$newHash) {
            $this->stats['failed']++;
            $this->error("  ✗ Failed to fetch hash for: $url");
            return false;
        }
        
        if ($currentHash === $newHash) {
            $this->stats['skipped']++;
            $this->info("  ✓ Hash is up to date: $url");
            return true;
        }
        
        if ($checkOnly) {
            $this->stats['skipped']++;
            $this->warn("  ! Hash needs update: $url");
            $this->warn("    Old: $currentHash");
            $this->warn("    New: $newHash");
            return false;
        }
        
        // Обновляем хеш
        $this->config['hashes'][$algo][$url] = $newHash;
        $this->stats['updated']++;
        
        $this->info("  ✓ Updated: $url");
        $this->debug("    Old: $currentHash");
        $this->debug("    New: $newHash");
        
        return true;
    }
    
    /**
     * Запуск обновления
     */
    public function run(): void
    {
        $this->printHeader();
        
        $algo = $this->options['algo'];
        $checkOnly = $this->options['check'];
        
        if (!in_array($algo, $this->config['supported_algos'])) {
            $this->error("Unsupported algorithm: $algo");
            $this->info("Supported: " . implode(', ', $this->config['supported_algos']));
            exit(1);
        }
        
        $this->info("Algorithm: $algo");
        $this->info("Mode: " . ($checkOnly ? "Check only" : "Update"));
        $this->info("");
        
        // Обновление конкретного URL
        if ($this->options['url']) {
            $this->info("Updating single URL: " . $this->options['url']);
            $this->updateHash($this->options['url'], $algo, $checkOnly);
        } 
        // Обновление всех URL
        else {
            $urls = array_keys($this->config['hashes'][$algo] ?? []);
            
            if (empty($urls)) {
                $this->warn("No URLs found for algorithm: $algo");
                return;
            }
            
            $this->info("Found " . count($urls) . " URLs to process");
            $this->info(str_repeat('-', 50));
            
            foreach ($urls as $url) {
                $this->updateHash($url, $algo, $checkOnly);
                // Небольшая задержка чтобы не забанили
                usleep(200000); // 0.2 секунды
            }
        }
        
        $this->printStats();
        
        // Сохраняем изменения
        if (!$checkOnly && $this->stats['updated'] > 0) {
            $this->config['metadata']['last_updated'] = date('Y-m-d H:i:s');
            $this->config['metadata']['total_hashes'] = count($this->config['hashes'][$algo]);
            
            if ($this->saveConfig()) {
                $this->info("");
                $this->success("✓ Config updated successfully!");
            } else {
                $this->error("✗ Failed to save config!");
                exit(1);
            }
        } elseif ($checkOnly && $this->stats['failed'] === 0 && $this->stats['updated'] === 0) {
            $this->success("✓ All hashes are up to date!");
        }
    }
    
    /**
     * Вывод заголовка
     */
    private function printHeader(): void
    {
        $this->info("========================================");
        $this->info("   YadroPHP SRI Hash Updater v1.0");
        $this->info("========================================");
        $this->info("");
    }
    
    /**
     * Вывод статистики
     */
    private function printStats(): void
    {
        $this->info("");
        $this->info("Statistics:");
        $this->info(str_repeat('-', 50));
        $this->info("Total:   {$this->stats['total']}");
        
        if ($this->stats['updated'] > 0) {
            $this->info("Updated: {$this->stats['updated']}");
        }
        
        if ($this->stats['failed'] > 0) {
            $this->error("Failed:  {$this->stats['failed']}");
        }
        
        if ($this->stats['skipped'] > 0) {
            $this->info("Skipped: {$this->stats['skipped']}");
        }
    }
    
    /**
     * Форматированный вывод
     */
    private function info(string $message): void
    {
        echo "\033[36m[INFO]\033[0m $message\n";
    }
    
    private function success(string $message): void
    {
        echo "\033[32m[OK]\033[0m $message\n";
    }
    
    private function warn(string $message): void
    {
        echo "\033[33m[WARN]\033[0m $message\n";
    }
    
    private function error(string $message): void
    {
        echo "\033[31m[ERROR]\033[0m $message\n";
    }
    
    private function debug(string $message): void
    {
        if ($this->options['debug'] ?? false) {
            echo "\033[90m[DEBUG]\033[0m $message\n";
        }
    }
}

// Парсинг аргументов командной строки
$options = [
    'algo' => 'sha384',
    'force' => false,
    'url' => null,
    'check' => false,
    'debug' => false,
];

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    
    if (strpos($arg, '--') === 0) {
        $parts = explode('=', $arg, 2);
        $option = substr($parts[0], 2);
        
        if (count($parts) === 2) {
            $options[$option] = $parts[1];
        } else {
            $options[$option] = true;
        }
    }
}

// Запуск
try {
    $updater = new SriHashUpdater($options);
    $updater->run();
} catch (Exception $e) {
    echo "\033[31m[FATAL]\033[0m " . $e->getMessage() . "\n";
    exit(1);
}