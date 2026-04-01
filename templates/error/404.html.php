<?php $this->extend('base_error.html.php') ?>

<?php $this->startSection('content') ?>

<section class="error-page not-found">
    <div class="container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1 class="error-title">Страница не найдена</h1>
            <p class="error-message">
                Запрашиваемая страница не существует или была перемещена.<br>
                Возможно, вы перешли по устаревшей ссылке или неправильно ввели адрес.
            </p>

            <div class="error-suggestions">
                <h2 class="suggestions-title">Вы можете попробовать:</h2>
                <div class="suggestions-grid">
                    <a href="/" class="suggestion-card">
                        <div class="suggestion-icon">🏠</div>
                        <div class="suggestion-text">Вернуться на главную</div>
                    </a>
                    <a href="/news" class="suggestion-card">
                        <div class="suggestion-icon">📰</div>
                        <div class="suggestion-text">Перейти к новостям</div>
                    </a>
                    <a href="/region" class="suggestion-card">
                        <div class="suggestion-icon">🗺️</div>
                        <div class="suggestion-text">Найти программы поддержки</div>
                    </a>
                    <a href="/contacts" class="suggestion-card">
                        <div class="suggestion-icon">📞</div>
                        <div class="suggestion-text">Связаться с нами</div>
                    </a>
                </div>
            </div>

            <div class="error-search">
                <h3 class="search-title">Поиск по сайту</h3>
                <form action="/search" method="GET" class="search-form">
                    <input type="search" 
                           name="q" 
                           class="search-input" 
                           placeholder="Введите запрос..."
                           aria-label="Поиск">
                    <button type="submit" class="search-button">Найти</button>
                </form>
            </div>

            <div class="error-actions">
                <button onclick="history.back()" class="error-btn secondary">← Вернуться назад</button>
                <a href="/" class="error-btn primary">На главную</a>
            </div>

            <div class="error-technical">
                <p class="technical-info">
                    <strong>Страница не найдена:</strong> <?= $_SERVER['REQUEST_URI'] ?? 'Не определен' ?><br>
                    <strong>Время:</strong> <?= date('Y-m-d H:i:s') ?><br>
                    <strong>Ссылающаяся страница:</strong> <?= $_SERVER['HTTP_REFERER'] ?? 'Не определено' ?>
                </p>
            </div>

            
        </div>
    </div>
</section>

<?php $this->endSection() ?>

<?php $this->startSection('styles') ?>
<style>
/* ===== 404 NOT FOUND - ИНДУСТРИАЛЬНЫЙ СТИЛЬ ===== */
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

/* Код ошибки с эффектом потери сигнала */
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
    animation: glitch 3s infinite;
}

.error-code::before,
.error-code::after {
    content: '404';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: transparent;
    clip: rect(0, 0, 0, 0);
}

.error-code::before {
    left: -2px;
    text-shadow: 2px 0 #757575;
    animation: glitch-1 2s infinite linear alternate-reverse;
}

.error-code::after {
    left: 2px;
    text-shadow: -2px 0 #2b2b2b;
    animation: glitch-2 3s infinite linear alternate-reverse;
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
    background: rgba(0, 0, 0, 0.5);
    padding: var(--spacing-md) var(--spacing-2xl);
    border: 1px solid var(--border-color);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
}

.error-title::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border: 1px solid var(--accent-color);
    pointer-events: none;
    animation: border-pulse 2s infinite;
}

/* Сообщение об ошибке */
.error-message {
    font-size: var(--font-size-xl);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-2xl);
    line-height: 1.8;
    background: rgba(0, 0, 0, 0.3);
    padding: var(--spacing-xl);
    border: 1px dashed var(--border-color);
    position: relative;
}

.error-message::before {
    content: '⚠';
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--accent-color);
    color: white;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: var(--font-size-lg);
    animation: blink 2s infinite;
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

/* Секция предложений */
.error-suggestions {
    margin: var(--spacing-2xl) 0;
    padding: var(--spacing-xl);
    background: rgba(0, 0, 0, 0.7);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
    position: relative;
}

.suggestions-title {
    font-size: var(--font-size-lg);
    color: var(--accent-color);
    margin-bottom: var(--spacing-lg);
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: var(--font-family-mono);
}

.suggestions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--spacing-md);
}

.suggestion-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-lg);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02));
    border: 1px solid var(--border-color);
    text-decoration: none;
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
}

.suggestion-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(211, 47, 47, 0.2), transparent);
    transition: left 0.5s ease;
}

.suggestion-card:hover::before {
    left: 100%;
}

.suggestion-card:hover {
    transform: translateY(-4px);
    border-color: var(--accent-color);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
}

.suggestion-icon {
    font-size: var(--font-size-3xl);
    filter: drop-shadow(0 0 10px rgba(211, 47, 47, 0.5));
}

.suggestion-text {
    color: var(--accent-color);
    font-size: var(--font-size-sm);
    font-weight: 500;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Поиск */
.error-search {
    margin: var(--spacing-2xl) 0;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.search-title {
    font-size: var(--font-size-base);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-md);
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.search-form {
    display: flex;
    gap: var(--spacing-sm);
    position: relative;
}

.search-input {
    flex: 1;
    padding: var(--spacing-md);
    background: rgba(0, 0, 0, 0.5);
    border: 2px solid var(--border-color);
    color: var(--text-primary);
    font-size: var(--font-size-base);
    transition: all var(--transition-base);
}

.search-input:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 20px rgba(211, 47, 47, 0.3);
}

.search-button {
    padding: var(--spacing-md) var(--spacing-xl);
    background: var(--accent-color);
    color: white;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-base);
    text-transform: uppercase;
    letter-spacing: 1px;
    position: relative;
    overflow: hidden;
}

.search-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.search-button:hover::before {
    left: 100%;
}

.search-button:hover {
    background: var(--accent-color-hover);
}

/* Анимации */
@keyframes glitch-1 {
    0% { clip: rect(20px, 9999px, 20px, 0); }
    5% { clip: rect(85px, 9999px, 95px, 0); }
    10% { clip: rect(40px, 9999px, 60px, 0); }
    15% { clip: rect(50px, 9999px, 70px, 0); }
    20% { clip: rect(25px, 9999px, 35px, 0); }
    25% { clip: rect(80px, 9999px, 90px, 0); }
    30% { clip: rect(10px, 9999px, 30px, 0); }
    to { clip: rect(0, 0, 0, 0); }
}

@keyframes glitch-2 {
    0% { clip: rect(65px, 9999px, 75px, 0); }
    5% { clip: rect(35px, 9999px, 45px, 0); }
    10% { clip: rect(95px, 9999px, 105px, 0); }
    15% { clip: rect(10px, 9999px, 20px, 0); }
    20% { clip: rect(70px, 9999px, 80px, 0); }
    25% { clip: rect(55px, 9999px, 65px, 0); }
    30% { clip: rect(15px, 9999px, 25px, 0); }
    to { clip: rect(0, 0, 0, 0); }
}

@keyframes border-pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 1; }
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

    .suggestions-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-sm);
    }

    .search-form {
        flex-direction: column;
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

    .suggestions-grid {
        grid-template-columns: 1fr;
    }

    .error-actions {
        flex-direction: column;
    }

    .error-actions .error-btn {
        width: 100%;
        text-align: center;
    }
}
</style>
<?php $this->endSection() ?>