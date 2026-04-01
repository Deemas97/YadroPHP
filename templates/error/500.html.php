<?php $this->extend('base_error.html.php') ?>

<?php $this->startSection('content') ?>

<section class="error-page server-error">
    <div class="container">
        <div class="error-content">
            <div class="error-code">500</div>
            <h1 class="error-title">Внутренняя ошибка сервера</h1>
            <p class="error-message">
                На сервере произошла непредвиденная ошибка
            </p>

            <div class="error-actions">
                <a href="/" class="error-btn primary">Вернуться на главную</a>
                <button onclick="location.reload()" class="error-btn secondary">Повторить попытку</button>
                <a href="/contacts" class="error-btn secondary">Сообщить о проблеме</a>
            </div>

            <div class="error-technical">
                <p class="technical-info">
                    <strong>ID ошибки:</strong> ERR-<?= substr(md5(uniqid()), 0, 8) ?><br>
                    <strong>Время:</strong> <?= date('Y-m-d H:i:s') ?><br>
                    <strong>Страница:</strong> <?= $_SERVER['REQUEST_URI'] ?? 'Не определен' ?>
                </p>
            </div>
        </div>
    </div>
</section>

<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
/* ===== 500 INTERNAL SERVER ERROR - ИНДУСТРИАЛЬНЫЙ СТИЛЬ ===== */
.error-page{
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-3xl) 0;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #2a1a1a 0%, #1a0a0a 100%);
    color: white;
}

.error-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
    position: relative;
    z-index: 10;
}

/* Код ошибки с эффектом перегрузки */
.error-code {
    font-size: 10rem;
    font-weight: 900;
    line-height: 1;
    color: var(--accent-color);
    text-shadow: 0 0 50px rgba(211, 47, 47, 0.8);
    margin-bottom: var(--spacing-lg);
    position: relative;
    display: inline-block;
    font-family: var(--font-family-mono);
    letter-spacing: 8px;
    animation: overload-pulse 2s infinite;
}

.error-code::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 400px;
    height: 150px;
    background: radial-gradient(circle, rgba(211, 47, 47, 0.2) 0%, transparent 70%);
    z-index: -1;
    animation: overload-radar 3s infinite;
}

.error-code::after {
    content: '⚠';
    position: absolute;
    top: -40px;
    right: -40px;
    font-size: var(--font-size-3xl);
    color: var(--accent-color);
    animation: blink 1s infinite;
}

/* Заголовок */
.error-title {
    font-size: var(--font-size-4xl);
    font-weight: 700;
    margin-bottom: var(--spacing-xl);
    position: relative;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 4px;
    background: rgba(211, 47, 47, 0.1);
    padding: var(--spacing-md) var(--spacing-2xl);
    border: 2px solid var(--accent-color);
    box-shadow: 0 0 30px rgba(211, 47, 47, 0.3);
    backdrop-filter: blur(5px);
}

.error-title::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 2px;
    background: repeating-linear-gradient(
        90deg,
        var(--accent-color),
        var(--accent-color) 10px,
        transparent 10px,
        transparent 20px
    );
    animation: scan 4s linear infinite;
}

/* Сообщение об ошибке */
.error-message {
    font-size: var(--font-size-xl);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-2xl);
    line-height: 1.8;
    background: rgba(0, 0, 0, 0.5);
    padding: var(--spacing-xl);
    border: 1px solid var(--accent-color);
    position: relative;
    backdrop-filter: blur(5px);
}

.error-message::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border: 1px solid rgba(211, 47, 47, 0.3);
    pointer-events: none;
    animation: border-pulse 2s infinite;
}

/* Индикатор прогресса */
.error-progress {
    max-width: 400px;
    margin: var(--spacing-2xl) auto;
    padding: var(--spacing-lg);
    background: rgba(0, 0, 0, 0.5);
    border: 1px solid var(--border-color);
}

.progress-indicator {
    text-align: center;
}

.progress-bar {
    height: 20px;
    background: #1a1a1a;
    border: 2px solid var(--border-color);
    position: relative;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: repeating-linear-gradient(
        45deg,
        var(--accent-color),
        var(--accent-color) 10px,
        var(--accent-color-hover) 10px,
        var(--accent-color-hover) 20px
    );
    animation: progress-pulse 2s ease-in-out infinite;
    position: relative;
}

.progress-fill::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: scan 2s linear infinite;
}

.progress-text {
    color: var(--text-secondary);
    font-size: var(--font-size-sm);
    margin-top: var(--spacing-md);
    font-family: var(--font-family-mono);
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Кнопки действий */
.error-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: center;
    margin-bottom: var(--spacing-2xl);
    flex-wrap: wrap;
}

.error-btn {
    position: relative;
    display: inline-block;
    padding: var(--spacing-md) var(--spacing-xl);
    text-decoration: none;
    border-radius: 0;
    font-weight: 600;
    transition: all var(--transition-base);
    border: none;
    cursor: pointer;
    font-size: var(--font-size-sm);
    text-transform: uppercase;
    letter-spacing: 2px;
    overflow: hidden;
    z-index: 1;
}

.error-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
    z-index: -1;
}

.error-btn:hover::before {
    left: 100%;
}

.error-btn.primary {
    background: var(--accent-color);
    color: white;
    border: 2px solid var(--accent-color);
}

.error-btn.primary:hover {
    background: var(--accent-color-hover);
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(211, 47, 47, 0.5);
}

.error-btn.secondary {
    background: transparent;
    color: white;
    border: 2px solid var(--border-color);
}

.error-btn.secondary:hover {
    border-color: var(--accent-color);
    color: var(--accent-color);
    transform: translateY(-2px);
}

/* Техническая информация */
.error-technical {
    margin-top: var(--spacing-2xl);
    padding: var(--spacing-lg);
    background: rgba(0, 0, 0, 0.5);
    border: 1px solid var(--border-color);
    position: relative;
}

.technical-info {
    color: var(--text-tertiary);
    font-size: var(--font-size-xs);
    font-family: var(--font-family-mono);
    line-height: 1.8;
    text-align: left;
    margin: 0;
    word-break: break-word;
}

.technical-info strong {
    color: var(--accent-color);
    font-weight: 600;
}

/* ID ошибки */
.error-technical strong:first-child {
    color: #ff9900;
    animation: blink 2s infinite;
}

/* Анимации */
@keyframes overload-pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.9; }
}

@keyframes overload-radar {
    0% { transform: translate(-50%, -50%) scale(0.8); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(1.5); opacity: 0; }
}

@keyframes progress-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

@keyframes border-pulse {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

@keyframes scan {
    0% { transform: translateX(-100%); }
    20% { transform: translateX(100%); }
    100% { transform: translateX(100%); }
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* Адаптивность */
@media (max-width: 768px) {
    .error-code {
        font-size: 8rem;
    }

    .error-title {
        font-size: var(--font-size-2xl);
        padding: var(--spacing-sm) var(--spacing-lg);
    }

    .error-message {
        font-size: var(--font-size-lg);
        padding: var(--spacing-lg);
    }

    .error-actions {
        flex-direction: column;
        gap: var(--spacing-sm);
    }

    .error-btn {
        width: 100%;
        text-align: center;
    }

    .error-progress {
        max-width: 100%;
        margin: var(--spacing-lg) var(--spacing-md);
    }
}

@media (max-width: 480px) {
    .error-code {
        font-size: 5rem;
    }

    .error-code::after {
        top: -20px;
        right: -20px;
        font-size: var(--font-size-xl);
    }

    .error-title {
        font-size: var(--font-size-xl);
        letter-spacing: 2px;
    }

    .error-message {
        font-size: var(--font-size-base);
    }

    .error-actions {
        flex-direction: column;
    }
}
</style>
<?php $this->endSection() ?>