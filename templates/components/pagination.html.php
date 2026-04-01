<?php
/**
 * Компонент пагинации
 * 
 * @var array $data Данные с ключами:
 *   - current_page (int)
 *   - last_page (int)
 *   - base_url (string)
 *   - query_params (array) - опционально
 */
$currentPage = $data['current_page'] ?? 1;
$lastPage = $data['last_page'] ?? 1;
$baseUrl = $data['base_url'] ?? '';
$queryParams = $data['query_params'] ?? [];

if ($lastPage <= 1) {
    return;
}

$queryString = http_build_query($queryParams);
$baseUrlWithParams = $baseUrl . ($queryString ? '?' . $queryString . '&' : '?');

$delta = 2;
$start = max(1, $currentPage - $delta);
$end = min($lastPage, $currentPage + $delta);
?>
<div class="pagination">
    <?php if ($currentPage > 1): ?>
        <a href="<?= $baseUrlWithParams ?>page=<?= $currentPage - 1 ?>" class="pagination-prev">
            ← Предыдущие
        </a>
    <?php else: ?>
        <button class="pagination-prev" disabled>← Предыдущие</button>
    <?php endif; ?>
    
    <div class="pagination-pages">
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $currentPage): ?>
                <span class="pagination-page active"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= $baseUrlWithParams ?>page=<?= $i ?>" class="pagination-page">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    
    <?php if ($currentPage < $lastPage): ?>
        <a href="<?= $baseUrlWithParams ?>page=<?= $currentPage + 1 ?>" class="pagination-next">
            Следующие →
        </a>
    <?php else: ?>
        <button class="pagination-next" disabled>Следующие →</button>
    <?php endif; ?>
</div>