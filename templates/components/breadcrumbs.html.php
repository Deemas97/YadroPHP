<?php
/**
 * Компонент хлебных крошек
 * 
 * @var array $data Данные с ключом 'items' - массив элементов:
 *   - title (string)
 *   - url (string|null)
 *   - is_last (bool)
 */
$items = $data['items'] ?? [];
if (empty($items)) {
    return;
}
?>
<div class="breadcrumb-nav">
    <?php foreach ($items as $index => $item): ?>
        <?php if ($index > 0): ?>
            <span class="breadcrumb-separator">/</span>
        <?php endif; ?>
        
        <?php if (isset($item['is_last']) && $item['is_last']): ?>
            <span class="breadcrumb-current"><?= $this->escape($item['title']) ?></span>
        <?php else: ?>
            <a href="<?= $this->escape($item['url']) ?>" class="breadcrumb-link">
                <?= $this->escape($item['title']) ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>