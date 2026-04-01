<?php
namespace Core\Service\Renderer;

class CacheManager
{
    private string $cacheDir = YADRO_PHP__CACHE_DIR . '/templates/';

    private int $defaultTtl = 3600;
    private array $ttlMap = [];
    
    private array $metadata = [];
    private string $metadataFile;

    public function __construct()
    {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        $this->metadataFile = $this->cacheDir . 'cache_meta.json';
        $this->loadMetadata();
    }
    
    private function loadMetadata(): void
    {
        if (file_exists($this->metadataFile)) {
            $content = file_get_contents($this->metadataFile);
            $this->metadata = json_decode($content, true) ?: [];
        }
    }
    
    private function saveMetadata(): void
    {
        file_put_contents($this->metadataFile, json_encode($this->metadata, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    public function store(string $templatePath, array $data, string $content, array $placeholders = []): void
    {
        $cacheKey = $this->getCacheKey($templatePath, $data);
        $cacheFile = $this->getCacheFilePath($cacheKey);
        
        file_put_contents($cacheFile, $content, LOCK_EX);
        
        $this->metadata[$cacheKey] = [
            'template' => $templatePath,
            'data_hash' => crc32(json_encode($data)),
            'placeholders' => $placeholders,
            'created_at' => time(),
            'expires_at' => time() + ($this->ttlMap[$templatePath] ?? $this->defaultTtl),
            'file' => basename($cacheFile)
        ];
        
        $this->saveMetadata();
    }
    
    public function getCached(string $templatePath, array $data): ?array
    {
        $cacheKey = $this->getCacheKey($templatePath, $data);
        $cacheFile = $this->getCacheFilePath($cacheKey);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        if (!isset($this->metadata[$cacheKey])) {
            unlink($cacheFile);
            return null;
        }
        
        $meta = $this->metadata[$cacheKey];
        
        if (time() > $meta['expires_at']) {
            $this->invalidate($cacheKey);
            return null;
        }
        
        $content = file_get_contents($cacheFile);
        
        if ($content === false) {
            return null;
        }
        
        return [
            'content' => $content,
            'placeholders' => $meta['placeholders']
        ];
    }
    
    public function getPlaceholders(string $templatePath, array $data): array
    {
        $cacheKey = $this->getCacheKey($templatePath, $data);
        
        if (isset($this->metadata[$cacheKey])) {
            return $this->metadata[$cacheKey]['placeholders'] ?? [];
        }
        
        return [];
    }
    
    public function clear(?string $templatePath = null): void
    {
        if ($templatePath) {
            foreach ($this->metadata as $key => $meta) {
                if ($meta['template'] === $templatePath) {
                    $this->invalidate($key);
                }
            }
        } else {
            foreach (glob($this->cacheDir . '*.html') as $file) {
                unlink($file);
            }
            $this->metadata = [];
            if (file_exists($this->metadataFile)) {
                unlink($this->metadataFile);
            }
        }
        
        $this->saveMetadata();
    }
    
    public function invalidate(string $cacheKey): void
    {
        if (isset($this->metadata[$cacheKey])) {
            $cacheFile = $this->getCacheFilePath($cacheKey);
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
            unset($this->metadata[$cacheKey]);
        }
    }
    
    private function getCacheKey(string $templatePath, array $data): string
    {
        $dataHash = crc32(json_encode($data));
        $templateHash = md5($templatePath);
        return $templateHash . '_' . $dataHash;
    }
    
    private function getCacheFilePath(string $cacheKey): string
    {
        return $this->cacheDir . $cacheKey . '.html';
    }
    
    public function setTtl(string $templatePath, int $ttl): void
    {
        $this->ttlMap[$templatePath] = $ttl;
        
        foreach ($this->metadata as $key => $meta) {
            if ($meta['template'] === $templatePath) {
                $this->metadata[$key]['expires_at'] = time() + $ttl;
            }
        }
        $this->saveMetadata();
    }
    
    public function getCacheInfo(): array
    {
        $totalSize = 0;
        $files = glob($this->cacheDir . '*.html');
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        $activeCount = count($this->metadata);
        $expiredCount = 0;
        $now = time();
        
        foreach ($this->metadata as $meta) {
            if ($now > $meta['expires_at']) {
                $expiredCount++;
            }
        }
        
        return [
            'total_files' => count($files),
            'active_entries' => $activeCount,
            'expired_entries' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'cache_dir' => $this->cacheDir,
        ];
    }
    
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    public function cleanExpired(): int
    {
        $cleaned = 0;
        $now = time();
        
        foreach ($this->metadata as $key => $meta) {
            if ($now > $meta['expires_at']) {
                $this->invalidate($key);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}