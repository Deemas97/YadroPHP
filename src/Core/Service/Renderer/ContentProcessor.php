<?php
namespace Core\Service\Renderer;

use Core\Service\SessionManager;
use Core\Service\SubresourceIntegrityService;
use Infrastructure\Config\ContentSecurityPolicy;
use Bootstrap\Config\DotEnv;

class ContentProcessor
{
    private const CSP_NONCE_PLACEHOLDER = '{{CSP_NONCE}}';
    private const CSP_HASH_PLACEHOLDER = '{{CSP_HASH}}';
    private const CSRF_TOKEN_PLACEHOLDER = '{{CSRF_TOKEN}}';

    private ?string $cspNonce;
    private ?string $csrfToken;
    private bool $sriEnabled;
    private string $sriAlgo = 'sha384';

    private array $sriCache = [];

    public function __construct(
        private SessionManager $sessionManager,
        private SubresourceIntegrityService $sriService
    )
    {
        $this->cspNonce = ContentSecurityPolicy::getNonce();
        $this->csrfToken = $this->sessionManager->getCsrfToken();
        $this->sriEnabled = (DotEnv::getDataItem('SRI_ENABLED', '0') === '1');
    }

    public function setSriEnabled(bool $enabled): void
    {
        $this->sriEnabled = $enabled;
    }

    public function setSriAlgo(string $algo): void
    {
        $this->sriAlgo = $algo;
    }

    public function applySecurityMacros(string $content): string
    {
        return str_replace(
            [self::CSP_NONCE_PLACEHOLDER, self::CSRF_TOKEN_PLACEHOLDER],
            [$this->cspNonce ?? '', $this->csrfToken ?? ''],
            $content
        );
    }

    public function buildScriptTag(string $src, array $attributes = []): string
    {
        if ($this->sriEnabled && $this->sriService !== null) {
            $cacheKey = $src . $this->sriAlgo;
            
            if (!isset($this->sriCache[$cacheKey])) {
                try {
                    $this->sriCache[$cacheKey] = $this->sriService->getIntegrity($src, $this->sriAlgo);
                } catch (\Exception $e) {
                    error_log("SRI error for {$src}: " . $e->getMessage());
                    $this->sriCache[$cacheKey] = null;
                }
            }
            
            $integrity = $this->sriCache[$cacheKey];
            if ($integrity) {
                $attributes['integrity'] = $integrity;
                $attributes['crossorigin'] = $attributes['crossorigin'] ?? 'anonymous';
            }
        }

        $attributes['src'] = $src;
        return $this->buildTag('script', $attributes, true);
    }

    public function buildStyleTag(string $href, array $attributes = []): string
    {
        if ($this->sriEnabled) {
            $cacheKey = $href . $this->sriAlgo;
            
            if (!isset($this->sriCache[$cacheKey])) {
                $this->sriCache[$cacheKey] = $this->sriService->getIntegrity($href, $this->sriAlgo);
            }
            
            $integrity = $this->sriCache[$cacheKey];
            if ($integrity) {
                $attributes['integrity'] = $integrity;
                $attributes['crossorigin'] = $attributes['crossorigin'] ?? 'anonymous';
            }
        }

        $attributes['href'] = $href;
        $attributes['rel'] = $attributes['rel'] ?? 'stylesheet';
        return $this->buildTag('link', $attributes);
    }

    private function buildTag(string $tag, array $attributes, bool $selfClosing = true, ?string $content = null): string
    {
        $attrs = [];
        foreach ($attributes as $name => $value) {
            if ($value === true) {
                $attrs[] = $name;
            } elseif ($value !== false && $value !== null) {
                $attrs[] = $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
            }
        }
        
        $attrString = implode(' ', $attrs);
        
        if ($selfClosing) {
            return '<' . $tag . ($attrString ? ' ' . $attrString : '') . '></' . $tag . '>';
        }
        
        return '<' . $tag . ($attrString ? ' ' . $attrString : '') . '>' . $content . '</' . $tag . '>';
    }

    public function inlineScript(string $code, array $attributes = []): string
    {
        if ($this->cspNonce) {
            $attributes['nonce'] = $this->cspNonce;
        }
        return $this->buildTag('script', $attributes, false, $code);
    }
}