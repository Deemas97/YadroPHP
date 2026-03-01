<?php
namespace Core\Pipeline;

use Core\Config\AppMiddlewareConfig;
use Core\Container\ReflectionContainerInterface;
use Core\MessageBus\MessageBusInterface;
use Core\Middleware\ClosureMiddleware;
use Core\Middleware\MiddlewareInterface;
use Core\Middleware\RequestMiddleware;
use Core\Middleware\SecurityMiddleware;
use Exception;

class Pipeline implements PipelineInterface
{
    private ReflectionContainerInterface $container;
    
    private CoreMiddlewarePipeline $corePipeline;
    private ?AppMiddlewarePipeline $appPipeline = null;
    private ?ClosureMiddleware     $closureMiddleware = null;

    private array  $defaultCoreMiddleware = [];
    private array  $defaultCorePriorities = [];
    private ?array $closureConfig = null;

    public function init(ReflectionContainerInterface $container): void
    {
        $this->container = $container;
        $this->corePipeline = new CoreMiddlewarePipeline();

        $this->defaultCoreMiddleware = [
            RequestMiddleware::class,
            SecurityMiddleware::class
        ];
        
        $this->defaultCorePriorities = [
            RequestMiddleware::class => 100,
            SecurityMiddleware::class => 90
        ];
        
        $this->initDefaultCorePipeline();
        $this->initClosureMiddleware();
    }

    public function process(MessageBusInterface $messageBus): MessageBusInterface
    {
        $messageBus = $this->corePipeline->process($messageBus);
        
        if ($this->isStopped($messageBus)) {
            return $messageBus;
        }
        
        $this->initAppPipeline();
        $messageBus = $this->appPipeline->process($messageBus);
        
        if ($this->isStopped($messageBus)) {
            return $messageBus;
        }
        
        if ($this->closureMiddleware !== null) {
            $messageBus = $this->closureMiddleware->process($messageBus);
        }
        
        return $messageBus;
    }

    private function initDefaultCorePipeline(): void
    {
        $this->corePipeline->clear();
        
        foreach ($this->defaultCoreMiddleware as $middlewareClass) {
            $middleware = $this->getMiddlewareInstance($middlewareClass);
            $this->corePipeline->pipe($middleware);
            
            if (isset($this->defaultCorePriorities[$middlewareClass])) {
                $this->corePipeline->setPriority($middlewareClass, $this->defaultCorePriorities[$middlewareClass]);
            }
        }
    }

    private function initClosureMiddleware(): void
    {
        try {
            $configPath = (YADRO_PHP__CONFIGS_DIR . '/middleware/closure.php');
            
            if (is_readable($configPath)) {
                $config = require $configPath;
                
                if (is_array($config)) {
                    $this->closureConfig = $config;
                    $this->closureMiddleware = new ClosureMiddleware();
                } else {
                    error_log('Closure middleware config must return array, ' . gettype($config) . ' given');
                }
            }
        } catch (Exception $e) {
            error_log('Failed to load closure middleware config: ' . $e->getMessage());
        }
    }

    private function getMiddlewareInstance(string $middlewareClass)
    {
        if ($middlewareClass === ClosureMiddleware::class) {
            return new ClosureMiddleware();
        }
        
        return $this->container->get($middlewareClass);
    }

    private function initAppPipeline(): void
    {
        if ($this->appPipeline !== null) {
            return;
        }

        $configPath = (YADRO_PHP__CONFIGS_DIR . '/middleware/app.php');
        
        try {
            $configurer = new AppMiddlewareConfig($configPath);
            $this->appPipeline = new AppMiddlewarePipeline($this->container, $configurer);
        } catch (Exception $e) {
            error_log('App middleware config not found, using empty pipeline');
            $this->appPipeline = new AppMiddlewarePipeline(
                $this->container, 
                new AppMiddlewareConfig($configPath)
            );
        }
    }

    private function isStopped(MessageBusInterface $messageBus): bool
    {
        return ($messageBus->has('_pipeline_stopped') && 
                $messageBus->get('_pipeline_stopped') === true);
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->corePipeline->pipe($middleware);
        return $this;
    }

    public function prepend(MiddlewareInterface $middleware): self
    {
        $this->corePipeline->prepend($middleware);
        return $this;
    }

    public function setPriority(string $middlewareClass, int $priority): self
    {
        $this->corePipeline->setPriority($middlewareClass, $priority);
        return $this;
    }

    public function clear(): self
    {
        $this->corePipeline->clear();
        if ($this->appPipeline) {
            $this->appPipeline->clear();
        }
        return $this;
    }

    public function has(string $middlewareClass): bool
    {
        return $this->corePipeline->has($middlewareClass) || 
               ($this->appPipeline && $this->appPipeline->has($middlewareClass));
    }

    public function remove(string $middlewareClass): self
    {
        $this->corePipeline->remove($middlewareClass);
        if ($this->appPipeline) {
            $this->appPipeline->remove($middlewareClass);
        }
        return $this;
    }

    public function count(): int
    {
        $count = $this->corePipeline->count();
        if ($this->appPipeline) {
            $count += $this->appPipeline->count();
        }
        return $count;
    }

    public function getMiddleware(): array
    {
        $middleware = $this->corePipeline->getMiddleware();
        if ($this->appPipeline) {
            $middleware = array_merge($middleware, $this->appPipeline->getMiddleware());
        }
        return $middleware;
    }
}