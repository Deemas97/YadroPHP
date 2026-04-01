<div class="footer-main">
    <div class="container">
        <div class="footer-grid">
            <!-- Колонка 1: Лого и контакты -->
            <div class="footer-col">
                <img src="<?= YADRO_PHP__ASSETS_DIR ?>/img/logo.png"
                     class="footer-logo"
                     alt="Фонд кластерного развития"
                     width="160" 
                     height="45">
                <address class="footer-address">
                    <p>410012, г. Саратов, ул. Краевая, д. 85, офис 304</p>
                    <p class="contacts-address-item"><span>Телефон:</span><a href="tel:+78452756403">+7 (8452) 75-64-03</a></p>
                    <p class="contacts-address-item"><span>Почта:</span><a href="mailto:info@fsimp.ru">info@fsimp.ru</a></p>
                </address>
            </div>
            <!-- Колонка 2: О Фонде -->
            <div class="footer-col">
                <h2 class="footer-title">О Фонде</h2>
                <ul class="footer-menu">
                    <li><a href="/news">Новости</a></li>
                    <li><a href="/contacts">Контакты</a></li>
                </ul>
            </div>
            <!-- Колонка 3: Деятельность -->
            <div class="footer-col">
                <h2 class="footer-title">Деятельность</h2>
                <ul class="footer-menu">
                    <li><a href="/region">Региональное представительство ФСИ</a></li>
                    <li><a href="/preacceleration">Преакселерация</a></li>
                    <li><a href="/cluster">СОПК</a></li>
                    <li><a href="/mtk">MTK</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Нижний колонтитул с юридической информацией -->
<div class="footer-bottom">
    <div class="container">
        <div class="footer-bottom-grid">
            <div >
                <div class="legal-links">
                    <a href="/credentials/personal_data">Политика обработки персональных данных</a>
                    <a href="/credentials/privacy">Политика конфиденциальности</a>
                    <a href="/credentials/agreement">Пользовательское соглашение</a>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                <div class="copyright">
                    © <?= date('Y') ?> Фонд кластерного развития и венчурных инвестиций Саратовской области. Все права защищены.
                </div>
                <div class="developer">
                    Сайт разработан на YadroPHP
                </div>
            </div>
        </div>
    </div>
</div>