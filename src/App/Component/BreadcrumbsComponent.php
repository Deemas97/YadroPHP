<?php
namespace App\Component;

class BreadcrumbsComponent
{
    private array $items = [];
    
    public function __construct(string $currentTitle = null)
    {
        $this->add('Главная', '/');
        
        if ($currentTitle) {
            $this->add($currentTitle);
        }
    }
    
    public function add(string $title, ?string $url = null): self
    {
        $this->items[] = [
            'title' => $title,
            'url' => $url,
            'is_last' => false,
        ];
        
        if (count($this->items) > 1) {
            $this->items[count($this->items) - 2]['is_last'] = false;
        }
        
        if ($url === null) {
            $this->items[count($this->items) - 1]['is_last'] = true;
        }
        
        return $this;
    }
    
    public function getItems(): array
    {
        return $this->items;
    }
    
    public function render(): string
    {
        ob_start();
        ?>
        <div class="breadcrumb-nav">
            <?php foreach ($this->items as $index => $item): ?>
                <?php if ($index > 0): ?>
                    <span class="breadcrumb-separator">/</span>
                <?php endif; ?>
                
                <?php if ($item['is_last'] || empty($item['url'])): ?>
                    <span class="breadcrumb-current"><?= htmlspecialchars($item['title']) ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>" class="breadcrumb-link">
                        <?= htmlspecialchars($item['title']) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}