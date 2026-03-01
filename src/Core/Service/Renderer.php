<?php
namespace Core\Service;

use Bootstrap\Config\DotEnv;
use Core\Service\Renderer\HtmlMinificator;
use Core\Service\Renderer\TemplatesCachingService;
use Infrastructure\Config\ContentSecurityPolicy;
use Throwable;
use RuntimeException;

class Renderer implements CoreServiceInterface
{
    private string $templatesDir = (YADRO_PHP__TEMPLATES_DIR . DIRECTORY_SEPARATOR);

    private array   $sectionsStack    = [];
    private array   $currentSections  = [];
    private ?string $currentSection   = null;
    private ?string $layout           = null;

    private bool $cachingEnabled   = false;
    private bool $minifyingEnabled = true;
    private bool $isMinified       = false;
    
    private const string CSP_NONCE_PLACEHOLDER   = '{{CSP_NONCE}}';
    private const string CSP_HASH_PLACEHOLDER    = '{{CSP_HASH}}';
    private const string CSRF_TOKEN_PLACEHOLDER  = '{{CSRF_TOKEN}}';

    private ?string $cspNonce  = null;
    private ?string $cspHash   = null;
    private ?string $csrfToken = null;

    private bool   $sriEnabled     = false;
    private string $sriDefaultAlgo = 'sha384';
    
    public function __construct(
        private SessionManager $sessionManager,
        private SubresourceIntegrityService $sriService,
        private TemplatesCachingService $caching,
        private HtmlMinificator $minificator
    )
    {
        $this->cspNonce  = ContentSecurityPolicy::getNonce();
        $this->csrfToken = $this->sessionManager->getCsrfToken();

        $this->sriEnabled = (DotEnv::getDataItem('SRI_ENABLED', '0') === '1');
    }

    public function render(string $templatePath, array $data = []): string
    {
        $cacheFilePath = null;
        if ($this->cachingEnabled === true) {
            $cacheFilePath = $this->caching->getCacheFilePath($templatePath, $data);
            
            $cachedContent = $this->caching->extractFromCache($cacheFilePath);
            if ($cachedContent !== null) {
                return $this->applySecurityMacros($cachedContent);
            }
        }

        $content = $this->compileTemplate($templatePath, $data);

        if ($this->minifyingEnabled && !$this->isMinified) {
            $content = $this->minifyContent($content);
        }

        if ($this->cachingEnabled === true && $cacheFilePath !== null) {
            $this->saveToCache($cacheFilePath, $content);
        }

        return $this->applySecurityMacros($content);
    }

    public function enableCaching(bool $flag): void
    {
        $this->cachingEnabled = $flag;
    }

    public function enableMinifying(bool $flag): void
    {
        $this->minifyingEnabled = $flag;
    }

    private function compileTemplate(string $templatePath, array $data): string
    {
        $this->sectionsStack[] = $this->currentSections;
        $this->currentSections = [];
        $this->currentSection = null;
        $this->layout = null;
        
        $fullPath = ($this->templatesDir . $templatePath);
        
        if (!file_exists($fullPath)) {
            throw new RuntimeException("Не найден файл шаблона: " . $fullPath);
        }

        $content = $this->renderTemplate($fullPath, $data);
        
        if ($this->layout) {
            $content = $this->compileTemplate($this->layout, array_merge($data, ['content' => $content]));
        }

        $this->currentSections = array_pop($this->sectionsStack);
        
        return $content;
    }

    private function minifyContent(string $content): string
    {
        $contentMinified = $this->minificator->minify($content);
        $this->isMinified = true;

        return $contentMinified;
    }

    private function saveToCache(string $cacheFilePath, string &$content): void
    {
        $this->caching->cache($cacheFilePath, $content);
    }
    
    private function applySecurityMacros(string $content): string
    {
        $replacements = [
            self::CSP_NONCE_PLACEHOLDER  => ($this->cspNonce ?? ''),
            self::CSP_HASH_PLACEHOLDER   => ($this->cspHash ?? ''),
            self::CSRF_TOKEN_PLACEHOLDER => ($this->csrfToken ?? '')
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }
    
    protected function renderTemplate(string $templatePath, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        
        try {
            include $templatePath;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Ошибка рендеринга шаблона: " . $e->getMessage());
        }
        
        return ob_get_clean();
    }

    public function escape(string|array $value)
    {
        if (is_array($value)) {
            return array_map([$this, 'escapeString'], $value);
        }
        return $this->escapeString($value);
    }
    
    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }
    
    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }
    
    public function startSection(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->currentSections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    public function section(string $name): string
    {
        if (isset($this->currentSections[$name])) {
            return $this->currentSections[$name];
        }
        
        foreach (array_reverse($this->sectionsStack) as $sections) {
            if (isset($sections[$name])) {
                return $sections[$name];
            }
        }
        
        return '';
    }

    public function includeComponent(string $componentPath, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        include ($this->templatesDir . $componentPath);
    }

    public function getStatusColor(string $status, array $statusConfig): string
    {
        foreach ($statusConfig as $typeConfig) {
            if (isset($typeConfig[$status])) {
                return $typeConfig[$status]['color'];
            }
        }
        
        return '#858796';
    }

    public function getStatusText(string $status, array $statusConfig): string
    {
        foreach ($statusConfig as $typeConfig) {
            if (isset($typeConfig[$status])) {
                return $typeConfig[$status]['text'];
            }
        }
        
        return $status;
    }

    private function escapeString(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function truncate(string $string, int $length, string $ellipsis = '...'): string
    {
        if (strlen($string) <= $length) {
            return $string;
        }
        return substr($string, 0, $length - strlen($ellipsis)) . $ellipsis;
    }

    /**
     * Включение/выключение SRI
     */
    public function enableSri(bool $enabled = true): void
    {
        $this->sriEnabled = $enabled;
    }

    /**
     * Установка алгоритма хеширования для SRI
     */
    public function setSriAlgo(string $algo): void
    {
        $this->sriDefaultAlgo = $algo;
    }

    /**
     * Хелпер для скриптов с SRI
     */
    public function script(string $src, array $attributes = []): string
    {
        // Если SRI отключен - обычный тег
        if (!$this->sriEnabled) {
            return $this->buildScriptTag($src, $attributes);
        }
    
        // Пытаемся получить SRI только для внешних ресурсов
        $integrity = $this->sriService->getIntegrity($src, $this->sriDefaultAlgo);
    
        if ($integrity) {
            $attributes['src'] = $src;
            $attributes['integrity'] = $integrity;
            $attributes['crossorigin'] = $attributes['crossorigin'] ?? 'anonymous';
            
            // ВАЖНО: передаем и src, и атрибуты
            return $this->buildScriptTag($src, $attributes);
        }
    
        // Для локальных файлов - обычный тег
        return $this->buildScriptTag($src, $attributes);
    }

    /**
     * Хелпер для стилей с SRI
     */
    public function style(string $href, array $attributes = []): string
    {
        // Если SRI отключен - обычный тег
        if (!$this->sriEnabled) {
            return $this->buildLinkTag($href, $attributes);
        }

        // Пытаемся получить SRI только для внешних ресурсов
        $integrity = $this->sriService->getIntegrity($href, $this->sriDefaultAlgo);

        if ($integrity) {
            $attributes['href'] = $href;
            $attributes['integrity'] = $integrity;
            $attributes['crossorigin'] = $attributes['crossorigin'] ?? 'anonymous';

            if (!isset($attributes['rel'])) {
                $attributes['rel'] = 'stylesheet';
            }

            // ВАЖНО: передаем и href, и атрибуты
            return $this->buildLinkTag($href, $attributes);
        }

        // Для локальных файлов или если SRI не найден - обычный тег
        return $this->buildLinkTag($href, $attributes);
    }

    /**
     * Построение тега script
     */
    private function buildScriptTag(array|string $src, array $attributes = []): string
    {
        if (is_string($src)) {
            $attributes['src'] = $src;
        }

        $attrs = [];
        foreach ($attributes as $name => $value) {
            if ($value === true) {
                $attrs[] = $name;
            } elseif ($value !== false && $value !== null) {
                $attrs[] = $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
        }

        return '<script ' . implode(' ', $attrs) . '></script>';
    }

    /**
     * Построение тега link
     */
    private function buildLinkTag(array|string $href, array $attributes = []): string
    {
        if (is_string($href)) {
            $attributes['href'] = $href;
        }

        if (!isset($attributes['rel'])) {
            $attributes['rel'] = 'stylesheet';
        }

        $attrs = [];
        foreach ($attributes as $name => $value) {
            if ($value === true) {
                $attrs[] = $name;
            } elseif ($value !== false && $value !== null) {
                $attrs[] = $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
        }

        return '<link ' . implode(' ', $attrs) . '>';
    }
}