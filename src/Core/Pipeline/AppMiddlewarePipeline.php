<?php
namespace Core\Pipeline;

use Bootstrap\Config\DotEnv;
use Core\Config\AppMiddlewareConfig;
use Core\Container\ReflectionContainerInterface;
use Core\MessageBus\MessageBusInterface;
use Core\Middleware\AppMiddlewareInterface;
use Core\Middleware\Extendable\ActionsMiddleware;
use Core\Middleware\Extendable\CompressionMiddleware;
use Core\Middleware\Extendable\ResponseMiddleware;
use Core\Middleware\MiddlewareInterface;
use Dev\Dumper;
use Exception;
use RuntimeException;

class AppMiddlewarePipeline implements PipelineInterface
{
    private array $middleware = [];
    private bool  $isSorted = false;
    private array $middlewarePriorities = [];
    private array $middlewareConfigs = [];

    public function __construct(
        private ReflectionContainerInterface $container,
        private AppMiddlewareConfig $configurer
    )
    {
        $this->loadFromConfig();
    }

    private function loadFromConfig(): void
    {
        $enabledMiddleware = $this->configurer->getEnabled();
        $priorities = $this->configurer->getPriorities();

        if (empty($enabledMiddleware) === true) {
            if (DotEnv::getDataItem('APP_DEFAULT_PIPELINE_ENABLED', '0') === '1') {
                $this->middleware[] = $this->container->get(ActionsMiddleware::class);
                $this->middlewarePriorities[ActionsMiddleware::class] = 3;

                if (DotEnv::getDataItem('GZIP_ENABLED', '0') === '1') {
                    $this->middleware[] = $this->container->get(CompressionMiddleware::class);
                    $this->middlewarePriorities[ActionsMiddleware::class] = 2;
                }
            
                $this->middleware[] = $this->container->get(ResponseMiddleware::class);
                $this->middlewarePriorities[ActionsMiddleware::class] = 1;
            }
        } else {
            foreach ($enabledMiddleware as $middlewareClass) {
                try {
                    $middleware = $this->container->get($middlewareClass);
                    
                    if (!$middleware instanceof AppMiddlewareInterface) {
                        throw new RuntimeException(
                            sprintf('App middleware %s должен реализовывать AppMiddlewareInterface', 
                            $middlewareClass)
                        );
                    }
                
                    $this->middleware[] = $middleware;
                    $this->middlewareConfigs[$middlewareClass] = $this->configurer->getConfig($middlewareClass);
                
                    if (isset($priorities[$middlewareClass])) {
                        $this->middlewarePriorities[$middlewareClass] = $priorities[$middlewareClass];
                    }
                } catch (Exception $e) {
                    error_log(sprintf(
                        'Failed to load app middleware %s: %s',
                        $middlewareClass,
                        $e->getMessage()
                    ));
                }
            }
        }

        $this->isSorted = false;
    }

    public function process(MessageBusInterface $messageBus): MessageBusInterface
    {
        $this->sortMiddlewareByPriority();
        
        foreach ($this->middleware as $middleware) {
            $middlewareClass = get_class($middleware);
            
            if (isset($this->middlewareConfigs[$middlewareClass])) {
                $messageBus->set('_app_middleware_config:' . $middlewareClass, 
                    $this->middlewareConfigs[$middlewareClass]);
            }
            
            $messageBus = $middleware->process($messageBus);
            
            if ($messageBus->get('_pipeline_stopped') === true) {
                break;
            }
        }
        
        return $messageBus;
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        if (!$middleware instanceof AppMiddlewareInterface) {
            throw new RuntimeException(
                'App middleware pipeline поддерживает только AppMiddlewareInterface'
            );
        }
        
        $this->middleware[] = $middleware;
        $this->isSorted = false;
        return $this;
    }

    public function prepend(MiddlewareInterface $middleware): self
    {
        if (!$middleware instanceof AppMiddlewareInterface) {
            throw new RuntimeException(
                'App middleware pipeline поддерживает только AppMiddlewareInterface'
            );
        }
        
        array_unshift($this->middleware, $middleware);
        $this->isSorted = false;
        return $this;
    }

    public function setPriority(string $middlewareClass, int $priority): self
    {
        $this->middlewarePriorities[$middlewareClass] = $priority;
        $this->isSorted = false;
        return $this;
    }

    public function getMiddlewareConfig(string $middlewareClass): ?array
    {
        return $this->middlewareConfigs[$middlewareClass] ?? null;
    }

    private function sortMiddlewareByPriority(): void
    {
        if ($this->isSorted || empty($this->middlewarePriorities)) {
            $this->isSorted = true;
            return;
        }

        usort($this->middleware, function ($a, $b) {
            $priorityA = $this->middlewarePriorities[get_class($a)] ?? 0;
            $priorityB = $this->middlewarePriorities[get_class($b)] ?? 0;
            
            return $priorityB <=> $priorityA;
        });
        
        $this->isSorted = true;
    }

    public function clear(): self
    {
        $this->middleware = [];
        $this->middlewarePriorities = [];
        $this->middlewareConfigs = [];
        $this->isSorted = true;
        return $this;
    }

    public function has(string $middlewareClass): bool
    {
        foreach ($this->middleware as $middleware) {
            if (get_class($middleware) === $middlewareClass) {
                return true;
            }
        }
        return false;
    }

    public function remove(string $middlewareClass): self
    {
        $this->middleware = array_filter($this->middleware, function ($middleware) use ($middlewareClass) {
            return get_class($middleware) !== $middlewareClass;
        });
        
        $this->middleware = array_values($this->middleware);
        unset(
            $this->middlewarePriorities[$middlewareClass],
            $this->middlewareConfigs[$middlewareClass]
        );
        
        return $this;
    }

    public function count(): int
    {
        return count($this->middleware);
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function reload(): self
    {
        $this->clear();
        $this->loadFromConfig();
        return $this;
    }
}