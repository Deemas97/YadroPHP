<div class="cookie-notice" id="cookieNotice" role="dialog" aria-label="Уведомление об использовании cookies" style="display: none;">
    <div class="cookie-notice-container">
        <div class="cookie-notice-content">
            <div class="cookie-notice-icon" aria-hidden="true">🍪</div>
            <div class="cookie-notice-text">
                <p>
                    <strong>Мы используем файлы cookie</strong>
                </p>
                <p>
                    Для&nbsp;обеспечения технической функциональности сайта мы&nbsp;используем файлы&nbsp;cookie.<br>Продолжая находиться на&nbsp;нашем сайте, 
                    вы&nbsp;соглашаетесь с&nbsp;условиями&nbsp;
                    <a href="/credentials/privacy" class="cookie-link">Политики&nbsp;конфиденциальности</a>
                    <br class="mobile-only">&nbsp;и&nbsp;
                    <a href="/credentials/agreement" class="cookie-link">Пользовательского&nbsp;соглашения</a>.
                </p>
            </div>
        </div>
        <div class="cookie-notice-actions">
            <button class="cookie-button cookie-button-primary" id="cookieAccept" aria-label="Принять использование cookies">
                Принять
            </button>
        </div>
    </div>
</div>

<!-- Стили для cookie-уведомления -->
<style>
    /* Основные переменные для cookie-уведомления (наследуются из основного CSS) */
    .cookie-notice {
        width: 100%;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: var(--bg-primary, #ffffff);
        border-top: 3px solid var(--accent-color, #d32f2f);
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        padding: var(--spacing-md, 16px) 0;
        transform: translateY(0);
        transition: transform 0.3s ease, opacity 0.3s ease;
        font-family: var(--font-family, 'Roboto', sans-serif);
        opacity: 1;
    }

    .cookie-notice.hidden {
        transform: translateY(100%);
        opacity: 0;
        pointer-events: none;
    }

    .cookie-notice-container {
        max-width: 1280px;
        width: 100%;
        margin: 0 auto;
        padding: 0 var(--spacing-lg, 24px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--spacing-xl, 32px);
    }

    .cookie-notice-content {
        display: flex;
        align-items: flex-start;
        gap: var(--spacing-md, 16px);
        flex: 1;
    }

    .cookie-notice-icon {
        font-size: 2rem;
        line-height: 1;
        flex-shrink: 0;
    }

    .cookie-notice-text {
        width: 90%;
        color: var(--text-primary, #212121);
        font-size: var(--font-size-sm, 14px);
        line-height: 1.6;
    }

    .cookie-notice-text p {
        margin: 0 0 var(--spacing-xs, 4px);
    }

    .cookie-notice-text p:last-child {
        margin-bottom: 0;
    }

    .cookie-notice-text strong {
        color: var(--accent-color, #d32f2f);
        font-weight: 600;
    }

    .cookie-link {
        color: var(--accent-color, #d32f2f);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
        border-bottom: 1px solid transparent;
    }

    .cookie-link:hover {
        color: var(--accent-color-hover, #b71c1c);
        border-bottom-color: currentColor;
    }

    .cookie-notice-actions {
        display: flex;
        gap: var(--spacing-md, 16px);
        flex-shrink: 0;
    }

    .cookie-button {
        padding: var(--spacing-sm, 8px) var(--spacing-xl, 24px);
        font-size: var(--font-size-sm, 14px);
        font-weight: 500;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        font-family: inherit;
    }

    .cookie-button-primary {
        background: var(--accent-color, #d32f2f);
        color: white;
    }

    .cookie-button-primary:hover {
        background: var(--accent-color-hover, #b71c1c);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(211, 47, 47, 0.3);
    }

    .cookie-button-secondary {
        background: var(--bg-secondary, #f5f5f5);
        color: var(--text-primary, #212121);
        border: 1px solid var(--border-color, #e0e0e0);
    }

    .cookie-button-secondary:hover {
        background: var(--border-color, #e0e0e0);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Адаптивность для планшетов */
    @media (max-width: 768px) {
        .cookie-notice-container {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
            gap: var(--spacing-md, 16px);
        }

        .cookie-notice-content {
            align-items: center;
            text-align: center;
        }

        .cookie-notice-actions {
            justify-content: center;
        }

        .cookie-button {
            padding: var(--spacing-sm, 8px) var(--spacing-lg, 24px);
        }
    }

    /* Адаптивность для мобильных */
    @media (max-width: 480px) {
        .cookie-notice-content {
            flex-direction: column;
            text-align: center;
        }

        .cookie-notice-icon {
            font-size: 2.5rem;
        }

        .cookie-notice-actions {
            flex-direction: column;
            width: 100%;
        }

        .cookie-button {
            width: 100%;
            padding: var(--spacing-md, 12px);
        }
    }

    /* Поддержка темной темы */
    [data-theme="dark"] .cookie-notice {
        background: var(--bg-secondary, #2d2d2d);
        border-top-color: var(--accent-color, #d32f2f);
    }

    [data-theme="dark"] .cookie-notice-text {
        color: var(--text-primary, #f5f5f5);
    }

    [data-theme="dark"] .cookie-button-secondary {
        background: var(--bg-tertiary, #424242);
        border-color: var(--border-color, #616161);
        color: var(--text-primary, #f5f5f5);
    }

    [data-theme="dark"] .cookie-button-secondary:hover {
        background: #616161;
    }

    /* Анимация появления */
    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .cookie-notice:not(.hidden) {
        animation: slideUp 0.5s ease forwards;
    }
</style>