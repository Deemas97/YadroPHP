<?php
/**
 * Компонент SEO-метатегов
 * 
 * @var array $data Данные с ключами:
 *   - title (string)
 *   - meta_description (string)
 *   - meta_keywords (string)
 *   - og_title (string)
 *   - og_description (string)
 *   - og_type (string)
 *   - og_image (string)
 *   - canonical_url (string)
 *   - schema (string)
 */
?>
<title><?= $this->escape($data['title']) ?></title>
<meta name="description" content="<?= $this->escape($data['meta_description']) ?>">
<meta name="keywords" content="<?= $this->escape($data['meta_keywords']) ?>">
<link rel="canonical" href="<?= $this->escape($data['canonical_url']) ?>">

<meta property="og:title" content="<?= $this->escape($data['og_title']) ?>">
<meta property="og:description" content="<?= $this->escape($data['og_description']) ?>">
<meta property="og:type" content="<?= $data['og_type'] ?>">
<meta property="og:url" content="<?= $this->escape($data['canonical_url']) ?>">
<meta property="og:image" content="<?= $this->escape($data['og_image']) ?>">
<meta property="og:locale" content="ru_RU">
<meta property="og:site_name" content="Фонд кластерного развития и венчурных инвестиций Саратовской области">

<script type="application/ld+json">
    <?= $data['schema'] ?? '' ?>
</script>