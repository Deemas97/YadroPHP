<?php
namespace Bootstrap;

interface AutoloaderInterface
{
    public function register(): void;
    public function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void;
    public function loadClass(string $class);
    public function getStats(): array;
}