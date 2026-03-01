<?php
namespace Core\Service;

class SubresourceIntegrityService implements CoreServiceInterface
{
    private array $cache = [];
    private array $config = [];

    public function __construct()
    {
        $this->config = require (YADRO_PHP__CONFIGS_DIR . '/subresource_integrity.php') ?? [];
    }

    public function getIntegrity(string $url, string $algo = 'sha384'): ?string
    {
        $cacheKey = $url . '|' . $algo;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        if ($this->isLocalFile($url)) {
            return null;
        }

        if (isset($this->config['hashes'][$algo][$url])) {
            $this->cache[$cacheKey] = $this->config['hashes'][$algo][$url];
            return $this->config['hashes'][$algo][$url];
        }

        // ADD CLI-TOOL FOR CDN'S CONTENT UPDATING
        // https://www.srihash.org/

        return null;
    }

    private function isLocalFile(string $url): bool
    {
        // Проверяем по началу URL
        return strpos($url, 'http') !== 0;
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}