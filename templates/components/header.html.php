<?php
/**
 * Компонент шапки сайта
 * 
 * @var array $data Данные:
 *   - serverData (Infrastructure\Http\ServerData) - опционально, для доступа к серверным данным
 */
$serverData = $data['serverData'] ?? null;
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
$isActive = function($path) use ($currentUri) {
    if ($path === '/' && $currentUri === '/') {
        return true;
    }
    return strpos($currentUri, $path) === 0 && $path !== '/';
};
?>
<div class="dynamic-header">
    <div class="header-top">
        <div class="container header-container">
            <div class="header-left">
                <button class="mobile-menu-toggle" aria-label="Меню" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <a href="/" class="logo">
                    <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/logo.png" 
                         alt="Фонд кластерного развития и венчурных инвестиций"
                         width="180" 
                         height="50">
                    <div class="logo-text">
                        <span class="logo-text--top">Некоммерческая организация</span>
                        <span class="logo-text--middle">«Фонд кластерного развития<br>и венчурных инвестиций</span>
                        <span class="logo-text--bottom">Саратовской области»</span>
                    </div>
                </a>
            </div>

            <div class="header-middle">
                <div class="partner-item" style="padding: 8px;">
                    <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/header/government.png" 
                         alt="Правительство Саратовской области"
                         class="partner-logo--header"
                         loading="lazy">
                </div>
                <div class="partner-item">
                    <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/header/npr.png" 
                         alt="Национальные проекты России"
                         class="partner-logo--header"
                         loading="lazy">
                </div>
                <a href="https://saratov-bis.ru/" class="partner-item">
                    <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/header/my_business.png" 
                         alt="Мой Бизнес"
                         class="partner-logo--header"
                         loading="lazy">
                </a>
            </div>

            <div class="header-right desktop-only">
                <div class="header-contacts">
                    <div class="contact-item">
                        <svg class="contact-icon" width="16" height="16" viewBox="0 0 24 24">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
                        </svg>
                        <a href="tel:+78452756403" class="contact-link">+7 (8452) 75-64-03</a>
                    </div>
                    <div class="contact-item">
                        <svg class="contact-icon" width="16" height="16" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                        </svg>
                        <a href="mailto:info@fsimp.ru" class="contact-link">info@fsimp.ru</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <nav class="main-nav" aria-label="Основное меню">
        <div class="container nav-container">
            <div class="industrial-overlay-menu"></div>
            <div class="nav-menu">
                <ul class="nav-menu--list">
                    <li class="nav-item nav-item--main-page <?= $isActive('/') ? 'active' : '' ?>">
                        <a href="/" class="nav-link">Главная</a>
                    </li>
                    <li class="nav-item <?= $isActive('/about') ? 'active' : '' ?>">
                        <a href="/about" class="nav-link">О Фонде</a>
                    </li>
                    <li class="nav-item <?= $isActive('/news') ? 'active' : '' ?>">
                        <a href="/news" class="nav-link">Новости</a>
                    </li>
                    <li class="nav-item <?= $isActive('/region') ? 'active' : '' ?>">
                        <a href="/region" class="nav-link">Региональное представительство ФСИ</a>
                    </li>
                    <li class="nav-item <?= $isActive('/preacceleration') ? 'active' : '' ?>">
                        <a href="/preacceleration" class="nav-link">Преакселерация</a>
                    </li>
                    <li class="nav-item <?= $isActive('/cluster') ? 'active' : '' ?>">
                        <a href="/cluster" class="nav-link">СОПК</a>
                    </li>
                    <li class="nav-item <?= $isActive('/mtk') ? 'active' : '' ?>">
                        <a href="/mtk" class="nav-link">МТК</a>
                    </li>
                    <li class="nav-item <?= $isActive('/contacts') ? 'active' : '' ?>">
                        <a href="/contacts" class="nav-link">Контакты</a>
                    </li>
                </ul>

                <div class="mobile-partners">
                    <div class="mobile-search">
                        <form action="/search" method="GET" class="mobile-search-form">
                            <input type="search" 
                                   name="q" 
                                   placeholder="Поиск по сайту..." 
                                   aria-label="Поисковый запрос"
                                   class="mobile-search-input">
                            <button type="submit" class="mobile-search-submit" aria-label="Найти">
                                <svg width="20" height="20" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8" stroke="currentColor" fill="none"/>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <div class="mobile-partners-grid">
                        <div class="mobile-partner-item">
                            <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/header/government.png" 
                                 alt="Правительство Саратовской области"
                                 class="mobile-partner-logo">
                        </div>
                        <div class="mobile-partner-item">
                            <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/header/npr.png" 
                                 alt="Национальные проекты России"
                                 class="mobile-partner-logo">
                        </div>
                        <a href="https://saratov-bis.ru/" class="mobile-partner-item">
                            <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/header/my_business.png" 
                                 alt="Мой Бизнес"
                                 class="mobile-partner-logo">
                        </a>
                    </div>

                    <div class="mobile-contacts">
                        <div class="mobile-contacts-header">Контакты</div>
                        <div class="mobile-contacts-list">
                            <div class="mobile-contact-item">
                                <svg class="contact-icon" width="18" height="18" viewBox="0 0 24 24">
                                    <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/>
                                </svg>
                                <a href="tel:+78452756403">+7 (8452) 75-64-03</a>
                            </div>
                            <div class="mobile-contact-item">
                                <svg class="contact-icon" width="18" height="18" viewBox="0 0 24 24">
                                    <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/>
                                </svg>
                                <a href="mailto:info@fsimp.ru">info@fsimp.ru</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nav-controls">
                <button class="theme-toggle" aria-label="Переключить тему">
                    <span class="theme-icon light">☀️</span>
                    <span class="theme-icon dark">🌙</span>
                </button>
                <div class="font-size-controls" id="fontSizeControls">
                    <button class="font-size-btn" data-size="small">A</button>
                    <button class="font-size-btn font-size-btn--middle" data-size="normal">A</button>
                    <button class="font-size-btn font-size-btn--large" data-size="large">A</button>
                </div>
                <button class="search-toggle desktop-only" aria-label="Поиск">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" stroke="currentColor" fill="none"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="search-panel" hidden>
            <div class="container">
                <form action="/search" method="GET" class="search-form">
                    <input type="search" 
                           name="q" 
                           placeholder="Поиск по сайту..." 
                           aria-label="Поисковый запрос"
                           class="search-input">
                    <button type="submit" class="search-submit">Найти</button>
                    <button type="button" class="search-close" aria-label="Закрыть поиск">✕</button>
                </form>
            </div>
        </div>
    </nav>
</div>

<script>
(function() {
    'use strict';
    
    const dynamicHeader = document.querySelector('.dynamic-header');
    const headerTop = document.querySelector('.header-top');
    const mainNav = document.querySelector('.main-nav');
    let lastScrollY = window.scrollY;
    let ticking = false;
    
    function updateHeader() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            dynamicHeader.classList.add('header-transision');
            mainNav.classList.add('nav-shifted');
        } else {
            dynamicHeader.classList.remove('header-transision');
            mainNav.classList.remove('nav-shifted');
        }
        
        lastScrollY = currentScrollY;
        ticking = false;
    }
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(updateHeader);
            ticking = true;
        }
    });
})();
</script>