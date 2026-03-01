<?php
namespace Core\Controller;

abstract class ControllerAbstract
{
    protected function initResponse(): ControllerResponse
    {
        return new ControllerResponse();
    }

    protected function initJsonResponse(
        array $data = [],
        int $statusCode = 200,
        array $headers = []
    ): ControllerResponse
    {
        $response = new ControllerResponse();
        $response->setStatusCode($statusCode);

        if (!empty($data)) {
            $response->set('data', $data);
        }
        
        $response->set('is_json', true);
        
        if (!empty($headers)) {
            $response->set('headers', $headers);
        }
        
        return $response;
    }
}