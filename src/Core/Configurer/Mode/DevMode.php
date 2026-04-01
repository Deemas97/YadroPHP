<?php
namespace Core\Configurer\Mode;

use Bootstrap\Config\DotEnv;
use Core\Service\GzipCompressor;
use Infrastructure\Config\ContentSecurityPolicy;
use Infrastructure\Http\Protocol;
use Infrastructure\Jit\JitManager;

use Dev\DevModeManager;

class DevMode implements ModeInterface
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
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);
    }

    private function configSecurity(): void
    {
        if (Protocol::isHttpsForced()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 0');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        ContentSecurityPolicy::apply();
    }

    private function configPerformance(): void
    {
        if (DotEnv::getDataItem('JIT_ENABLED', '0') === '1') {
            JitManager::initFromEnv();
            JitManager::logStats();
        }
        
        if (DotEnv::getDataItem('GZIP_ENABLED', '0') === '1') {
            $gzipLevel = (int) DotEnv::getDataItem('GZIP_COMPRESSION_LEVEL', '6');
            GzipCompressor::init($gzipLevel);
            GzipCompressor::enableOutputCompression();
        }
    }
}