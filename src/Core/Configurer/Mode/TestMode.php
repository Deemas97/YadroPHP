<?php
namespace Core\Configurer\Mode;

use Core\Service\GzipCompressor;
use Bootstrap\Config\DotEnv;
use Infrastructure\Cache\OpCacheManager;
use Infrastructure\Config\ContentSecurityPolicy;
use Infrastructure\Http\Protocol;
use Infrastructure\Jit\JitManager;

use Dev\DevModeManager;

class TestMode implements ModeInterface
{
    public function init(): void
    {
        $this->configErrorReporting();
        $this->configSecurity();
        $this->configPerformance();

        DevModeManager::init();
    }

    private function configErrorReporting(): void
    {
        ini_set('display_errors', '1');
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    }

    private function configSecurity(): void
    {
        if (Protocol::isHttpsForced()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');

        ini_set('session.cookie_lifetime', '0');

        ContentSecurityPolicy::apply();
    }

    private function configPerformance(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        if (function_exists('opcache_reset')) {
            OpCacheManager::reset();
        }
        
        if (DotEnv::getDataItem('JIT_ENABLED', '0') === '1') {
            JitManager::initFromEnv();
            JitManager::logStats();
        }
        
        if (DotEnv::getDataItem('GZIP_IN_TESTS', '0') === '1') {
            GzipCompressor::init(1);
        }
    }
}