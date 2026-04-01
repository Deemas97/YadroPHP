<?php
namespace Core\Logger;

class GlobalLogger implements LoggerInterface
{
    public function log(string $message): void
    {
        error_log($message);
    }
}