<?php $this->extend('base_error.html.php') ?>

<?php $this->startSection('content') ?>

<section class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-code">400</div>
            <h1 class="error-title">Некорректный запрос</h1>
            <p class="error-message">
                Сервер не может обработать ваш запрос из-за синтаксической ошибки.<br>
                Пожалуйста, проверьте правильность введенных данных.
            </p>

            <div class="error-details">
                <div class="detail-item">
                    <span class="detail-label">Возможные причины:</span>
                    <ul class="detail-list">
                        <li>Некорректные символы в URL</li>
                        <li>Поврежденные или неправильно сформированные данные формы</li>
                        <li>Слишком большой размер запроса</li>
                        <li>Неверный формат передаваемых данных</li>
                    </ul>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Что делать:</span>
                    <ul class="detail-list">
                        <li>Проверьте URL на наличие опечаток</li>
                        <li>Обновите страницу (Ctrl+F5)</li>
                        <li>Очистите кэш браузера</li>
                        <li>Попробуйте позже</li>
                    </ul>
                </div>
            </div>

            <div class="error-actions">
                <a href="/" class="error-btn primary">Вернуться на главную</a>
                <a href="/contacts" class="error-btn secondary">Связаться с поддержкой</a>
                <button onclick="history.back()" class="error-btn secondary">Назад</button>
            </div>

            <div class="error-technical">
                <p class="technical-info">
                    Техническая информация для службы поддержки:<br>
                    <strong>Время:</strong> <?= date('Y-m-d H:i:s') ?><br>
                    <strong>IP:</strong> <?= $_SERVER['REMOTE_ADDR'] ?? 'Не определен' ?><br>
                    <strong>URL:</strong> <?= $_SERVER['REQUEST_URI'] ?? 'Не определен' ?>
                </p>
            </div>

            <div class="error-industry">
                <div class="industry-bg"></div>
            </div>
        </div>
    </div>
</section>

<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
/* ===== 400 BAD REQUEST - ИНДУСТРИАЛЬНЫЙ СТИЛЬ ===== */
.error-page{
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-3xl) 0;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, var(--color-gray-900) 0%, var(--color-gray-800) 100%);
    color: white;
}

.error-content {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
    position: relative;
    z-index: 10;
}

/* Код ошибки с индустриальным акцентом */
.error-code {
    font-size: 10rem;
    font-weight: 900;
    line-height: 1;
    color: transparent;
    -webkit-text-stroke: 4px var(--accent-color);
    text-shadow: 0 0 30px rgba(211, 47, 47, 0.5);
    margin-bottom: var(--spacing-lg);
    position: relative;
    display: inline-block;
    font-family: var(--font-family-mono);
    letter-spacing: 8px;
}

.error-code::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 300px;
    height: 100px;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(211, 47, 47, 0.1) 10px,
        rgba(211, 47, 47, 0.1) 20px
    );
    z-index: -1;
    animation: scan 8s linear infinite;
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
    border: 2px solid var(--accent-color);
    padding: var(--spacing-md) var(--spacing-2xl);
    background: rgba(0, 0, 0, 0.5);
    box-shadow: 0 0 20px rgba(211, 47, 47, 0.3);
}

.error-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 10%;
    width: 80%;
    height: 2px;
    background: repeating-linear-gradient(
        90deg,
        var(--accent-color),
        var(--accent-color) 10px,
        transparent 10px,
        transparent 20px
    );
}

/* Сообщение об ошибке */
.error-message {
    font-size: var(--font-size-xl);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-2xl);
    line-height: 1.8;
    background: rgba(255, 255, 255, 0.05);
    padding: var(--spacing-xl);
    border-left: 4px solid var(--accent-color);
    border-right: 4px solid var(--accent-color);
    position: relative;
}

.error-message::before,
.error-message::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid var(--accent-color);
}

.error-message::before {
    top: -2px;
    left: -2px;
    border-right: none;
    border-bottom: none;
}

.error-message::after {
    bottom: -2px;
    right: -2px;
    border-left: none;
    border-top: none;
}

/* Детали ошибки */
.error-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
    margin-bottom: var(--spacing-2xl);
    text-align: left;
    background: rgba(0, 0, 0, 0.7);
    padding: var(--spacing-xl);
    border: 1px solid var(--border-color);
    position: relative;
    backdrop-filter: blur(10px);
}

.error-details::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
}

.detail-item {
    color: var(--text-secondary);
}

.detail-label {
    display: block;
    font-weight: 700;
    color: var(--accent-color);
    margin-bottom: var(--spacing-md);
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: var(--font-size-sm);
    font-family: var(--font-family-mono);
}

.detail-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.detail-list li {
    margin-bottom: var(--spacing-sm);
    padding-left: var(--spacing-lg);
    position: relative;
    font-size: var(--font-size-sm);
    line-height: 1.6;
}

.detail-list li::before {
    content: '▶';
    position: absolute;
    left: 0;
    color: var(--accent-color);
    font-size: var(--font-size-xs);
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
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
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
    box-shadow: 0 10px 20px rgba(211, 47, 47, 0.3);
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
}

.technical-info strong {
    color: var(--accent-color);
    font-weight: 600;
}

/* Адаптивность */
@media (max-width: 768px) {
    .error-code {
        font-size: 8rem;
        -webkit-text-stroke: 3px var(--accent-color);
    }

    .error-title {
        font-size: var(--font-size-2xl);
        padding: var(--spacing-sm) var(--spacing-lg);
    }

    .error-message {
        font-size: var(--font-size-lg);
        padding: var(--spacing-lg);
    }

    .error-details {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
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
}

@media (max-width: 480px) {
    .error-code {
        font-size: 5rem;
        -webkit-text-stroke: 2px var(--accent-color);
    }

    .error-title {
        font-size: var(--font-size-xl);
        letter-spacing: 2px;
    }

    .error-message {
        font-size: var(--font-size-base);
    }
}
</style>
<?php $this->endSection() ?>