<?php
namespace Core\Configurer;

use Bootstrap\AutoloaderInterface;

interface ConfigurerInterface
{
    public function init(AutoloaderInterface $autoloader): void;
}