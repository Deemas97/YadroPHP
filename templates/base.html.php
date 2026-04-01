<?php
use Bootstrap\Config\ProjectMode;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    
    <!-- SEO компонент -->
    <?= $this->renderComponent('components/seo_meta.html.php', $data) ?>
    
    <!-- THEME -->
    <meta name="theme-color" content="#de252c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="icon" href="<?= YADRO_PHP__ASSETS_DIR ?>/img/logo.png">
    <link rel="apple-touch-icon" href="<?= YADRO_PHP__ASSETS_DIR ?>/img/logo.png">

    <!-- SECURITY -->
    <meta name="csp-nonce" content="{{CSP_NONCE}}">
    <meta name="csrf-token" content="{{CSRF_TOKEN}}">

    <?= $this->style(YADRO_PHP__ASSETS_DIR . '/css/styles.css') ?>
    <?= $this->section('styles') ?>
</head>
<body class="light">
    <header class="site-header">
        <?= $this->renderComponent('components/header.html.php', $data) ?>
    </header>

    <main class="site-main">
        <?= $this->section('content') ?>
    </main>

    <footer class="site-footer">
        <?= $this->renderComponent('components/footer.html.php', $data) ?>
    </footer>

    <?= $this->renderComponent('components/back_to_top_btn.html.php') ?>
    <?= $this->renderComponent('components/cookie_consent_modal.html.php', $data) ?>

    <!-- DEV-компоненты - динамическая секция (никогда не кэшируется) -->
    <?php if (ProjectMode::getCurrentMode() === 'dev'): ?>
        <?= $this->renderDynamicComponent('dev/toolbar.html.php', $data); ?>
    <?php endif; ?>

    <?= $this->script(YADRO_PHP__ASSETS_DIR . '/js/main.js', [
        'defer' => true,
        'nonce' => '{{CSP_NONCE}}'
    ]) ?>
    
    <?= $this->section('scripts') ?>
</body>
</html>