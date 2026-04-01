<?php
namespace Core\Middleware\Extendable;

use Core\Service\GzipCompressor;
use Core\MessageBus\MessageBusInterface;
use Core\Middleware\CoreMiddlewareInterface;
use Core\View\View;

class CompressionMiddleware implements CoreMiddlewareInterface
{
    public function process(MessageBusInterface $messageBus): MessageBusInterface
    {
        $headers = $messageBus->get('headers') ?? [];
        $acceptEncoding = $headers['Accept-Encoding'] ?? $headers['accept-encoding'] ?? '';
        $supportsGzip = strpos($acceptEncoding, 'gzip') !== false;

        if (!$supportsGzip || !GzipCompressor::isEnabled()) {
            return $messageBus;
        }

        $responseType = $messageBus->get('type');
        $response = $messageBus->get('response');

        switch ($responseType) {
            case 'html':
                ob_start();
                if ($response instanceof View) {
                    echo $response->getContent();
                } else {
                    echo $response;
                }
                $content = ob_get_clean();

                $compressed = GzipCompressor::compressIfNeeded($content, 'text/html');
                if (GzipCompressor::isCompressed($compressed)) {
                    $response = $compressed;
                    header('Content-Encoding: gzip');
                } else {
                    $response = $content;
                }

                $messageBus->set('response', $response);
                break;
            case 'api':
            default:
                if (is_array($response)) {
                    $json = json_encode($response);
                    $compressed = GzipCompressor::compress($json);
                    if ($compressed !== false) {
                        header('Content-Encoding: gzip');
                        $messageBus->set('response', $compressed);
                    }
                }
        }

        return $messageBus;
    }
}