<?php
namespace Core\Middleware\Extendable;

use Core\MessageBus\MessageBusInterface;
use Core\Middleware\CoreMiddlewareInterface;
use Core\View\View;

class ResponseMiddleware implements CoreMiddlewareInterface
{
    public function process(MessageBusInterface $messageBus): MessageBusInterface
    {
        $statusCode = ($messageBus->get('status') ?? 200);
        http_response_code($statusCode);

        $response = $messageBus->get('response');

        switch ($messageBus->get('type')) {
            case 'html':
                ob_start();
                if ($response instanceof View) {
                    echo $response->getContent();
                } else {
                    echo $response;
                }
                echo ob_get_clean();
                break;
            case 'api':
            default:
                header('Content-Type: application/json');

                if (is_string($response)) {
                    echo $response;
                } else {
                    echo json_encode($response);
                }
        }
        
        return $messageBus;
    }
}