<?php
namespace YadroPHP;

use Exception;
use RuntimeException;

use Bootstrap\AutoloaderInterface;
use Bootstrap\Config\DotEnv;
use Bootstrap\Config\ProjectMode;

use Core\Container\GlobalContainer;
use Core\Container\ReflectionContainerInterface;
use Core\Configurer\ConfigurerInterface;
use Core\Configurer\Configurer;
use Core\Pipeline\Pipeline;
use Core\Pipeline\PipelineInterface;
use Core\Router\Router;
use Core\Router\RouterInterface;

class Kernel
{
    private bool $isBooted = false;

    protected ?AutoloaderInterface         $autoloader;

    protected ReflectionContainerInterface $container;
    protected ConfigurerInterface          $configurer;
    protected RouterInterface              $router;
    protected PipelineInterface            $pipeline;

    public function __construct(?AutoloaderInterface $autoloader = null)
    {
        $this->autoloader = $autoloader;
    }

    final public function handle(): array
    {
        try {
            $this->init();

            $messageBus = $this->router->initMessageBus();
            $messageBus = $this->pipeline->process($messageBus);

            return $messageBus->getAll();
        } catch (Exception $e) {
            $this->handleException($e);
            return [];
        }
    }

    protected function handleException(Exception $error): void
    {
        if (php_sapi_name() === 'cli') {
            echo "[Error]: " . $error->getMessage() . PHP_EOL;
        } else {
            error_log($error->getMessage());
        }
    }

    private function init(): void
    {
        if ($this->isBooted === true) {
            throw new RuntimeException('Экземпляр ядра уже создан');
        }

        $this->initEnvironment();
        $this->initContainer();
        
        $this->initConfigurer();
        $this->initRouter();
        $this->initPipeline();

        $this->isBooted = true;
    }

    private function initEnvironment(): void
    {
        DotEnv::init(YADRO_PHP__ROOT_DIR);
        ProjectMode::init();
    }

    protected function initContainer(): void
    {
        $this->container = new GlobalContainer();
    }

    protected function initConfigurer(): void
    {
        $configurer = $this->container->get(Configurer::class);
        $configurer->init($this->autoloader);
        $this->configurer = $configurer;
    }

    protected function initRouter(): void
    {
        $this->router = $this->container->get(Router::class);
    }

    protected function initPipeline(): void
    {
        $pipeline = $this->container->get(Pipeline::class);
        $pipeline->init($this->container);
        $this->pipeline = $pipeline;
    }
}