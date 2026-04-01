<?php
namespace Core\Service;

use Core\Service\RendererInterface;
use Core\Service\Renderer\TemplateCompiler;
use Core\Service\Renderer\ContentProcessor;
use Core\Service\Renderer\CacheManager;
use Core\Service\Renderer\HtmlMinificator;

class Renderer implements CoreServiceInterface, RendererInterface
{
    private bool $cachingEnabled = false;
    private bool $isCachingMode = false;

    private bool $minifyingEnabled = true;

    public function __construct(
        private CacheManager     $cache,
        private ContentProcessor $processor,
        private TemplateCompiler $compiler,
        private HtmlMinificator  $minificator
    )
    {
        $this->compiler->setRenderer($this);
    }

    public function render(string $templatePath, array $data = []): string
    {
        $this->compiler->resetDynamicData();
        
        if ($this->cachingEnabled && !$this->isCachingMode) {
            $cached = $this->cache->getCached($templatePath, $data);
            if ($cached !== null) {
                if (!empty($cached['placeholders'])) {
                    $this->compiler->setDynamicPlaceholders($cached['placeholders']);
                }
                
                $content = $this->replaceDynamicPlaceholders($cached['content'], $data);
                
                return $this->processor->applySecurityMacros($content);
            }
        }

        $this->isCachingMode = true;
        
        $content = $this->compiler->compile($templatePath, $data);
        
        $this->isCachingMode = false;

        if ($this->cachingEnabled && !$this->isComponentRender()) {
            $placeholders = $this->compiler->getDynamicPlaceholders();

            if ($this->minifyingEnabled) {
                $content = $this->minificator->minify($content);
            }

            $this->cache->store($templatePath, $data, $content, $placeholders);
        }

        $content = $this->replaceDynamicPlaceholders($content, $data);

        return $this->processor->applySecurityMacros($content);
    }
    
    private function isComponentRender(): bool
    {
        return $this->compiler->getNestingLevel() > 0;
    }
    
    private function replaceDynamicPlaceholders(string $content, array $data): string
    {
        $placeholders = $this->compiler->getDynamicPlaceholders();
        
        foreach ($placeholders as $placeholder => $info) {
            if ($info['type'] === 'component') {
                $dynamicContent = $this->renderDynamicComponent($info['path'], $data, true);

                if ($this->minifyingEnabled) {
                    $dynamicContent = $this->minificator->minify($dynamicContent);
                }

                $content = str_replace($placeholder, $dynamicContent, $content);
            }
        }
        
        return $content;
    }

    public function renderDynamicComponent(string $componentPath, array $data = [], bool $fromCache = false): string
    {
        if (!$fromCache) {
            $placeholder = $this->compiler->markAsDynamicComponent($componentPath);
            
            $savedSections = $this->compiler->getSections();
            $savedSectionStack = $this->compiler->getSectionStack();
            $savedCurrentSection = $this->compiler->getCurrentSection();

            $this->compiler->clearSections();

            $this->compiler->compileComponent($componentPath, $data);

            $this->compiler->restoreSections($savedSections, $savedSectionStack, $savedCurrentSection);
            
            return $placeholder;
        } else {
            return $this->compiler->loadDynamicComponentContent($componentPath, $data);
        }
    }

    public function renderComponent(string $componentPath, array $data = []): string
    {
        $savedState = $this->compiler->saveState();
        $this->compiler->clearSections();
        
        $savedCaching = $this->cachingEnabled;
        $this->cachingEnabled = false;
        
        $content = $this->render($componentPath, $data);
        
        $this->cachingEnabled = $savedCaching;
        $this->compiler->restoreState($savedState);
        
        return $content;
    }

    public function startDynamicSection(string $name): void
    {
        $this->compiler->startDynamicSection($name);
    }
    
    public function isDynamicSection(string $name): bool
    {
        return $this->compiler->isDynamicSection($name);
    }

    public function clearCache(?string $templatePath = null): void
    {
        $this->cache->clear($templatePath);
    }

    public function extend(string $layout): void
    {
        $this->compiler->extend($layout);
    }

    public function section(string $name): string
    {
        return $this->compiler->section($name);
    }

    public function startSection(string $name): void
    {
        $this->compiler->startSection($name);
    }

    public function endSection(): void
    {
        $this->compiler->endSection();
    }

    public function script(string $src, array $attributes = []): string
    {
        return $this->processor->buildScriptTag($src, $attributes);
    }
    
    public function inlineScript(string $code, array $attributes = []): string
    {
        return $this->processor->inlineScript($code, $attributes);
    }

    public function style(string $href, array $attributes = []): string
    {
        return $this->processor->buildStyleTag($href, $attributes);
    }
    
    public function inlineStyle(string $code, array $attributes = []): string
    {
        $attrs = [];
        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $attrs[] = $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
        }
        return '<style ' . implode(' ', $attrs) . '>' . $code . '</style>';
    }

    public function breadcrumbs(array $items): string
    {
        ob_start();
        ?>
        <div class="breadcrumb-nav">
            <?php foreach ($items as $index => $item): ?>
                <?php if ($index > 0): ?>
                    <span class="breadcrumb-separator">/</span>
                <?php endif; ?>
                
                <?php if (isset($item['is_last']) && $item['is_last']): ?>
                    <span class="breadcrumb-current"><?= $this->escape($item['title']) ?></span>
                <?php else: ?>
                    <a href="<?= $this->escape($item['url']) ?>" class="breadcrumb-link">
                        <?= $this->escape($item['title']) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function escape(string|array $value): string|array
    {
        if (is_array($value)) {
            return array_map([$this, 'escapeString'], $value);
        }
        return $this->escapeString($value);
    }

    private function escapeString(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public function getCurrentUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function getBaseUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        return $protocol . $_SERVER['HTTP_HOST'];
    }

    public function isActiveRoute(string $path): bool
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($path === '/' && $currentPath === '/') {
            return true;
        }
        return strpos($currentPath, $path) === 0 && $path !== '/';
    }

    public function enableCaching(bool $flag): void
    {
        $this->cachingEnabled = $flag;
    }

    public function enableMinifying(bool $flag): void
    {
        $this->minifyingEnabled = $flag;
    }
    
    public function truncateUtf8(string $text, int $length = 200, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = substr($text, 0, $length);
        while (strlen($truncated) > 0) {
            $lastChar = ord(substr($truncated, -1));
            if (($lastChar & 0xC0) === 0xC0 || ($lastChar & 0xC0) === 0x80) {
                $truncated = substr($truncated, 0, -1);
            } else {
                break;
            }
        }
        
        $lastSpace = strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . $suffix;
    }

    public function getStats(): array
    {
        $cacheInfo = $this->cache->getCacheInfo();
        
        return [
            'caching_enabled' => $this->cachingEnabled,
            'minifying_enabled' => $this->minifyingEnabled,
            'sections_count' => count($this->compiler->getSections()),
            'nesting_level' => $this->compiler->getNestingLevel(),
            'dynamic_placeholders' => count($this->compiler->getDynamicPlaceholders()),
            'cache' => $cacheInfo,
            'compiler_state' => [
                'stack_depth' => count($this->compiler->getSectionsStack()),
                'current_sections' => array_keys($this->compiler->getSections()),
            ],
        ];
    }
}