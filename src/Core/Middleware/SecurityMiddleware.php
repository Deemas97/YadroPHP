<?php
namespace Core\Middleware;

use Core\MessageBus\MessageBusInterface;
use Core\Security\AuthAttribute;
use Core\Security\CsrfAttribute;
use Core\Service\AuthService;
use Core\Service\CsrfService;
use ReflectionMethod;
use RuntimeException;

final class SecurityMiddleware implements CoreMiddlewareInterface
{
    public function __construct(
        private AuthService $authService,
        private CsrfService $csrfService
    )
    {}

    public function process(MessageBusInterface $messageBus): MessageBusInterface
    {
        $reflectionMethod = $messageBus->get('reflectionMethod');
        
        if (!$reflectionMethod instanceof ReflectionMethod) {
            throw new RuntimeException("ReflectionMethod is not provided in MessageBus");
        }

        $methodAttributes = $this->validateMethodAttributes($reflectionMethod, $messageBus);
        
        $messageBus->set('controllerMethodAttributes', $methodAttributes);
        
        if ($methodAttributes['requiresAuth']) {
            $this->addUserInfoToMessageBus($messageBus);
        }

        $this->csrfService->initCsrfSession();
        
        return $messageBus;
    }

    private function validateMethodAttributes(ReflectionMethod $method, MessageBusInterface $messageBus): array
    {
        $attributes = [
            'requiresCSRF' => false,
            'csrfAjaxOnly' => false,

            'requiresAuth' => false,
            'authTable' => '',
            'authRoles' => [],
            'authStatus' => '',
            'authStrict' => true
        ];

        $csrfAttributes = $method->getAttributes(CsrfAttribute::class);
        if (!empty($csrfAttributes)) {
            $csrfAttr = $csrfAttributes[0]->newInstance();
            $attributes['requiresCSRF'] = $csrfAttr->enabled;
            $attributes['csrfAjaxOnly'] = $csrfAttr->ajaxOnly;
            $messageBus->set('csrf_ajax', $attributes['csrfAjaxOnly']);

            if ($csrfAttr->enabled) {
                $headers = ($messageBus->get('headers') ?? []);
                if (!$this->csrfService->validateToken($headers, $attributes['csrfAjaxOnly'])) {
                    $this->redirect('/error_403');
                }
            }
        }

        $authAttributes = $method->getAttributes(AuthAttribute::class);
        if (!empty($authAttributes)) {
            $authAttr = $authAttributes[0]->newInstance();
            $attributes['requiresAuth'] = true;
            $attributes['authTable'] = $authAttr->table;
            $attributes['authRoles'] = $authAttr->roles;
            $attributes['authStatus'] = $authAttr->status;
            $attributes['authStrict'] = $authAttr->strict;

            $this->validateAuth($authAttr->table, $authAttr->roles, $authAttr->status, $authAttr->strict);
        }

        return $attributes;
    }

    private function validateAuth(string $table, array $requiredRoles = [], string $requiredStatus = '', bool $strict = true): void
    {
        $this->authService->setUserTable($table);

        if (!$this->authService->isAuthenticated()) {
            $this->redirect('/admin/login');
        }

        $user = $this->authService->getUser();

        if (!$user) {
            $this->redirect('/admin/login');
        }

        if ($requiredStatus !== '') {
            $userStatus = $user->getStatus();

            $lockPath = '';

            switch ($userStatus) {
                case 'premoderation':
                    $lockPath = '/admin/premoderation_info';
                    break;
                case 'banned':
                    $lockPath = '/admin/ban_info';
                    break;
                default:
                    $lockPath = '/crash';
            }

            if ($userStatus !== $requiredStatus) {
                $this->redirect($lockPath);
            }
        }

        if (!empty($requiredRoles)) {
            $userRole = $user->getRole();

            if (is_string($userRole)) {
                $hasRequiredRole = in_array($userRole, $requiredRoles);
            } elseif (is_array($userRole)) {
                if ($strict) {
                    $hasRequiredRole = empty(array_diff($requiredRoles, $userRole));
                } else {
                    $hasRequiredRole = !empty(array_intersect($requiredRoles, $userRole));
                }
            } else {
                $hasRequiredRole = false;
            }

            if (!$hasRequiredRole) {
                $this->redirect('/error_403');
            }
        }
    }

    private function addUserInfoToMessageBus(MessageBusInterface $messageBus): void
    {
        $isAuthenticated = $this->authService->isAuthenticated();
        $messageBus->set('user_authenticated', $isAuthenticated);
        
        if ($isAuthenticated) {
            $user = $this->authService->getUser();
            if ($user) {
                $messageBus->set('user', $user);
                $messageBus->set('user_id', $user->getId());
                $messageBus->set('user_email', $user->getEmail());
                $messageBus->set('user_name', $user->getName());
                
                if (method_exists($user, 'getRole')) {
                    $role = $user->getRole();
                    $messageBus->set('user_role', $role);
                    $messageBus->set('user_roles', is_array($role) ? $role : [$role]);
                }
            }
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}