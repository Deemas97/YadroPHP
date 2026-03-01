<?php
namespace Bootstrap;

require YADRO_PHP__SRC_DIR . '/Bootstrap/AutoloaderInterface.php';
require YADRO_PHP__SRC_DIR . '/Bootstrap/AutoloaderPsr4.php';

class Autoloader
{
    private const array AUTOLOADER_NAMESPACES_MAP = [
        'Bootstrap'      => '/',
        'Core'           => '/',
        'Infrastructure' => '/',
        'App'            => '/',
        'Domain'         => '/'
    ];

    private static ?AutoloaderInterface $loader = null;
    
    public function init(): AutoloaderInterface
    {
        if (self::$loader !== null) {
            return self::$loader;
        }

        self::$loader = new AutoloaderPsr4(YADRO_PHP__ROOT_DIR . '/var/cache');
        
        $this->registerNamespaces();
        self::$loader->register();

        require YADRO_PHP__KERNEL_FILE;
        
        return self::$loader;
    }

    private function registerNamespaces(): void
    {
        foreach (self::AUTOLOADER_NAMESPACES_MAP as $namespace => $path) {
            $fullPath = (YADRO_PHP__SRC_DIR . $path);
            if (is_dir($fullPath)) {
                self::$loader->addNamespace($namespace, $fullPath);
            } else {
                error_log("Warning: Namespace path not found: $fullPath");
            }
        }
    }

    public function getStats(): ?array
    {
        return self::$loader?->getStats();
    }

    public function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        $this->init()->addNamespace($prefix, $baseDir, $prepend);
    }
}

return (new Autoloader())->init();