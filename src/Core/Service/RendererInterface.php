<?php
namespace Core\Service;

interface RendererInterface
{
    public function render(string $templatePath, array $data = []): string;
    public function renderComponent(string $componentPath, array $data = []): string;
    public function escape(string|array $value): string|array;
}