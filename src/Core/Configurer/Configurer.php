<?php
namespace Core\Configurer;

use Bootstrap\AutoloaderInterface;
use Bootstrap\Config\ProjectMode;
use Core\Configurer\Mode\DevMode;
use Core\Configurer\Mode\ProductionMode;
use Core\Configurer\Mode\TestMode;

class Configurer implements ConfigurerInterface
{
    public function init(?AutoloaderInterface $autoloader = null): void
    {
        $projectMode = ProjectMode::getCurrentMode();

        if (($projectMode === 'dev') || ($projectMode === 'test')) {
            if ($autoloader !== null) {
                $autoloader->addNamespace('Dev', (YADRO_PHP__SRC_DIR . DIRECTORY_SEPARATOR));
            }
        }

        switch ($projectMode) {
            case 'dev':
                $state = new DevMode();
                break;
            case 'test':
                $state = new TestMode();
                break;
            case 'production':
            default:
                $state = new ProductionMode();
        }

        $state->init();
    }
}