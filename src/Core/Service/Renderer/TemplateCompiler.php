<?php
namespace Core\Service\Renderer;

use Core\Service\RendererInterface;
use RuntimeException;
use Throwable;

class TemplateCompiler
{
    private string $templatesDir = YADRO_PHP__TEMPLATES_DIR . DIRECTORY_SEPARATOR;
    private ?RendererInterface $renderer = null;
    private array $sectionsStack = [];
    private array $currentSections = [];
    private array $sectionStack = [];
    private ?string $currentSection = null;
    private ?string $layout = null;
    private int $nestingLevel = 0;
    
    private array $dynamicSections = [];
    private array $dynamicComponents = [];
    private array $dynamicContentMap = []; // Маппинг ID -> содержимое
    private array $dynamicPlaceholders = []; // Плейсхолдеры для кэша
    private int $dynamicCounter = 0;

    public function setRenderer(RendererInterface $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * Помечает компонент как динамический и возвращает плейсхолдер
     */
    public function markAsDynamicComponent(string $componentPath): string
    {
        $id = 'DYNAMIC_CONTENT_' . ($this->dynamicCounter++);
        $this->dynamicComponents[$componentPath] = $id;
        $placeholder = "{{$id}}";
        $this->dynamicPlaceholders[$placeholder] = [
            'type' => 'component',
            'path' => $componentPath,
            'id' => $id
        ];
        return $placeholder;
    }
    
    /**
     * Регистрирует динамическое содержимое для последующей замены
     */
    public function registerDynamicContent(string $placeholder, string $content): void
    {
        $this->dynamicContentMap[$placeholder] = $content;
    }
    
    /**
     * Получает динамическое содержимое по плейсхолдеру
     */
    public function getDynamicContent(string $placeholder): ?string
    {
        return $this->dynamicContentMap[$placeholder] ?? null;
    }
    
    /**
     * Получает все динамические плейсхолдеры для кэширования
     */
    public function getDynamicPlaceholders(): array
    {
        return $this->dynamicPlaceholders;
    }
    
    /**
     * Восстанавливает динамические плейсхолдеры из кэша
     */
    public function setDynamicPlaceholders(array $placeholders): void
    {
        $this->dynamicPlaceholders = $placeholders;
        // Обновляем счетчик для новых динамических компонентов
        if (!empty($placeholders)) {
            $maxId = 0;
            foreach ($placeholders as $placeholder => $data) {
                if (preg_match('/DYNAMIC_CONTENT_(\d+)/', $placeholder, $matches)) {
                    $maxId = max($maxId, (int)$matches[1]);
                }
            }
            $this->dynamicCounter = $maxId + 1;
        }
    }
    
    /**
     * Загружает динамическое содержимое для компонента
     */
    public function loadDynamicComponentContent(string $componentPath, array $data): string
    {
        $fullPath = $this->templatesDir . $componentPath;
        
        if (!file_exists($fullPath)) {
            throw new RuntimeException("Динамический компонент не найден: " . $fullPath);
        }
        
        ob_start();
        
        try {
            extract($data, EXTR_SKIP);
            include $fullPath;
            $content = ob_get_clean();
            return $content;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Ошибка рендеринга динамического компонента: " . $e->getMessage());
        }
    }
    
    /**
     * Проверяет, является ли компонент динамическим
     */
    public function isDynamicComponent(string $componentPath): bool
    {
        return isset($this->dynamicComponents[$componentPath]);
    }

    public function getDynamicContentMap(): array
    {
        return array_merge($this->dynamicContentMap, $this->dynamicComponents);
    }
    
    public function getSectionStack(): array
    {
        return $this->sectionStack;
    }
    
    public function getCurrentSection(): ?string
    {
        return $this->currentSection;
    }
    
    public function clearSections(): void
    {
        $this->currentSections = [];
        $this->currentSection = null;
        $this->sectionStack = [];
    }
    
    public function restoreSections(array $sections, array $sectionStack, ?string $currentSection): void
    {
        $this->currentSections = $sections;
        $this->sectionStack = $sectionStack;
        $this->currentSection = $currentSection;
    }
    
    /**
     * Компилирует компонент и возвращает содержимое с плейсхолдерами для кэша
     */
    public function compileComponent(string $templatePath, array $data): string
    {
        $fullPath = $this->templatesDir . $templatePath;
        
        if (!file_exists($fullPath)) {
            throw new RuntimeException("Шаблон не найден: " . $fullPath);
        }
        
        ob_start();
        
        try {
            extract($data, EXTR_SKIP);
            include $fullPath;
            $content = ob_get_clean();
            
            // Если это динамический компонент, регистрируем содержимое
            if ($this->isDynamicComponent($templatePath)) {
                $placeholder = $this->dynamicComponents[$templatePath];
                $this->registerDynamicContent($placeholder, $content);
                return "{{$placeholder}}";
            }
            
            return $content;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Ошибка рендеринга: " . $e->getMessage());
        }
    }

    public function compile(string $templatePath, array $data): string
    {
        $this->nestingLevel++;
        
        $this->sectionsStack[] = $this->currentSections;
        $this->currentSections = [];
        $this->currentSection = null;
        
        $previousLayout = $this->layout;
        $this->layout = null;
        
        $fullPath = $this->templatesDir . $templatePath;
        
        if (!file_exists($fullPath)) {
            throw new RuntimeException("Шаблон не найден: " . $fullPath);
        }

        $content = $this->renderTemplate($fullPath, $data);
        
        if ($this->layout) {
            $layoutData = array_merge($data, ['content' => $content]);
            $content = $this->compile($this->layout, $layoutData);
        }
        
        $this->layout = $previousLayout;
        
        $parentSections = array_pop($this->sectionsStack);
        $this->currentSections = array_merge($parentSections, $this->currentSections);
        
        $this->nestingLevel--;
        
        return $content;
    }

    private function renderTemplate(string $templatePath, array $data): string
    {
        ob_start();
        
        try {
            extract($data, EXTR_SKIP);
            include $templatePath;
            $content = ob_get_clean();
            return $content;
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Ошибка рендеринга: " . $e->getMessage());
        }
    }

    public function __call(string $name, array $arguments)
    {
        if ($this->renderer && method_exists($this->renderer, $name)) {
            return $this->renderer->$name(...$arguments);
        }
        
        throw new RuntimeException("Call to undefined method " . __CLASS__ . "::{$name}()");
    }

    public function startSection(string $name): void
    {
        if ($this->currentSection !== null) {
            $currentContent = ob_get_contents();
            if ($currentContent !== false && $currentContent !== '') {
                $this->sectionStack[] = [
                    'name' => $this->currentSection,
                    'content' => $currentContent
                ];
                ob_clean();
            }
        }
        
        $this->currentSection = $name;
        ob_start();
    }
    
    public function endSection(): void
    {
        if ($this->currentSection === null) {
            if (!empty($this->sectionStack)) {
                $lastSection = array_pop($this->sectionStack);
                $this->currentSection = $lastSection['name'];
                echo $lastSection['content'];
            }
            return;
        }
        
        $content = ob_get_clean();
        $this->currentSections[$this->currentSection] = $content;
        $this->currentSection = null;
        
        if (!empty($this->sectionStack)) {
            $lastSection = array_pop($this->sectionStack);
            $this->currentSection = $lastSection['name'];
            echo $lastSection['content'];
        }
    }
    
    public function section(string $name): string
    {
        if (isset($this->currentSections[$name])) {
            return $this->currentSections[$name];
        }
        
        foreach (array_reverse($this->sectionsStack) as $sections) {
            if (isset($sections[$name])) {
                return $sections[$name];
            }
        }
        
        return '';
    }
    
    public function startDynamicSection(string $name): void {
        $this->dynamicSections[] = $name;
        $id = 'DYNAMIC_CONTENT_' . ($this->dynamicCounter++);
        $placeholder = "{{$id}}";
        $this->dynamicPlaceholders[$placeholder] = [
            'type' => 'section',
            'name' => $name,
            'id' => $id
        ];
        $this->startSection($name);
        // Возвращаем плейсхолдер, который будет использован в кэше
        echo $placeholder;
        ob_start(); // Перезапускаем буфер для содержимого секции
    }
    
    public function isDynamicSection(string $name): bool
    {
        return in_array($name, $this->dynamicSections);
    }
    
    public function hasDynamicContent(): bool
    {
        return !empty($this->dynamicSections) || !empty($this->dynamicComponents);
    }
    
    public function getSections(): array
    {
        return $this->currentSections;
    }
    
    public function getNestingLevel(): int
    {
        return $this->nestingLevel;
    }
    
    public function getSectionsStack(): array
    {
        return $this->sectionsStack;
    }

    public function extend(string $layout): void
    {
        $this->layout = $layout;
    }

    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }
    
    public function getLayout(): ?string
    {
        return $this->layout;
    }
    
    public function saveState(): array
    {
        return [
            'sectionsStack' => $this->sectionsStack,
            'currentSections' => $this->currentSections,
            'sectionStack' => $this->sectionStack,
            'currentSection' => $this->currentSection,
            'layout' => $this->layout,
            'nestingLevel' => $this->nestingLevel,
            'dynamicSections' => $this->dynamicSections,
            'dynamicComponents' => $this->dynamicComponents,
            'dynamicPlaceholders' => $this->dynamicPlaceholders,
            'dynamicCounter' => $this->dynamicCounter,
        ];
    }
    
    public function restoreState(array $state): void
    {
        $this->sectionsStack = $state['sectionsStack'];
        $this->currentSections = $state['currentSections'];
        $this->sectionStack = $state['sectionStack'] ?? [];
        $this->currentSection = $state['currentSection'];
        $this->layout = $state['layout'];
        $this->nestingLevel = $state['nestingLevel'] ?? 0;
        $this->dynamicSections = $state['dynamicSections'] ?? [];
        $this->dynamicComponents = $state['dynamicComponents'] ?? [];
        $this->dynamicPlaceholders = $state['dynamicPlaceholders'] ?? [];
        $this->dynamicCounter = $state['dynamicCounter'] ?? 0;
    }
    
    /**
     * Очищает динамические данные перед новым рендерингом
     */
    public function resetDynamicData(): void
    {
        $this->dynamicSections = [];
        $this->dynamicComponents = [];
        $this->dynamicContentMap = [];
        $this->dynamicPlaceholders = [];
        // Не сбрасываем счетчик, чтобы ID были уникальными
    }
}