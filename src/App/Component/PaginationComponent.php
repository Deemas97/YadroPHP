<?php
namespace App\Component;

use Domain\DTO\PaginationDTO;

class PaginationComponent
{
    public function __construct(
        private PaginationDTO $pagination,
        private string $baseUrl,
        private array $queryParams = []
    ) {}
    
    public function render(): string
    {
        if ($this->pagination->lastPage <= 1) {
            return '';
        }
        
        $queryString = http_build_query($this->queryParams);
        $baseUrlWithParams = $this->baseUrl . ($queryString ? '?' . $queryString . '&' : '?');
        
        ob_start();
        ?>
        <div class="pagination">
            <?php if ($this->pagination->hasPrev()): ?>
                <a href="<?= $baseUrlWithParams ?>page=<?= $this->pagination->getPrevPage() ?>" class="pagination-prev">
                    ← Предыдущие
                </a>
            <?php else: ?>
                <button class="pagination-prev" disabled>← Предыдущие</button>
            <?php endif; ?>
            
            <div class="pagination-pages">
                <?php foreach ($this->pagination->getPageRange() as $page): ?>
                    <?php if ($page == $this->pagination->currentPage): ?>
                        <span class="pagination-page active"><?= $page ?></span>
                    <?php else: ?>
                        <a href="<?= $baseUrlWithParams ?>page=<?= $page ?>" class="pagination-page">
                            <?= $page ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <?php if ($this->pagination->hasNext()): ?>
                <a href="<?= $baseUrlWithParams ?>page=<?= $this->pagination->getNextPage() ?>" class="pagination-next">
                    Следующие →
                </a>
            <?php else: ?>
                <button class="pagination-next" disabled>Следующие →</button>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}