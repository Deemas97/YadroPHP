<?php
namespace Core\Configurer\Mode;

use Bootstrap\Config\DotEnv;
use Core\Service\GzipCompressor;
use Infrastructure\Config\ContentSecurityPolicy;
use Infrastructure\Http\Protocol;
use Infrastructure\Jit\JitManager;

class ProductionMode implements ModeInterface
{
    public function init(): void
    {
        $this->configErrorReporting();
        $this->configSecurity();
        $this->configPerformance();
    }

    private function configErrorReporting(): void
    {
        ini_set('display_errors', '0');
        error_reporting(0);
    }

    private function configSecurity(): void
    {
        if (Protocol::isHttps() || Protocol::isHttpsForced()) {
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
        }

        if (DotEnv::getDataItem('GZIP_ENABLED', '0') === '1') {
            GzipCompressor::init(6);
            GzipCompressor::enableOutputCompression();
        }
    }
}