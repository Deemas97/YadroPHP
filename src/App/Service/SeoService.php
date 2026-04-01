<?php
namespace App\Service;

use Core\Service\ServiceInterface;
use Domain\DTO\NewsItemDTO;
use Domain\DTO\SeoDataDTO;
use Infrastructure\Http\ServerData;

class SeoService implements ServiceInterface
{
    private const ORGANIZATION_NAME = 'Фонд кластерного развития и венчурных инвестиций Саратовской области';
    private const ORGANIZATION_NAME_SHORT = 'ФКРВИ СО';
    private const DEFAULT_OG_IMAGE = '/assets/img/logo.png';
    
    public function __construct(
        private ServerData $server,
    ) {}
    
    /**
     * Получить SEO данные для главной страницы
     */
    public function getMainPageSeo(): SeoDataDTO
    {
        $title = self::ORGANIZATION_NAME . ' | ' . self::ORGANIZATION_NAME_SHORT;
        $description = 'Официальный сайт Фонда кластерного развития и венчурных инвестиций Саратовской области. Преакселерация УМНИК, региональное представительство ФСИ, реестр МТК, поддержка инноваций и промышленных кластеров.';
        $keywords = 'фонд кластерного развития, венчурные инвестиции, Саратовская область, преакселерация, УМНИК, ФСИ, МТК, промышленный кластер, гранты, инновации, поддержка бизнеса';
        
        return $this->createSeoData($title, $description, $keywords, '/');
    }
    
    /**
     * Получить SEO данные для списка новостей
     */
    public function getNewsListSeo(): SeoDataDTO
    {
        $title = 'Новости Фонда | Актуальные события и анонсы | ' . self::ORGANIZATION_NAME_SHORT;
        $description = 'Новости Фонда кластерного развития и венчурных инвестиций Саратовской области. Анонсы конкурсов и грантов, отчеты о мероприятиях, информация о программах поддержки инноваций, преакселерации УМНИК и развитии промышленных кластеров.';
        $keywords = 'новости фонда, события, анонсы конкурсов, гранты для инноваций, отчеты о мероприятиях, преакселерация новости, МТК новости, промышленные кластеры, Саратов новости, инновации в регионе, грантополучатели, победители конкурсов';
        
        return $this->createSeoData($title, $description, $keywords, '/news');
    }
    
    /**
     * Получить SEO данные для поста новости
     */
    public function getNewsPostSeo(NewsItemDTO $news): SeoDataDTO
    {
        $headerTitle = strlen($news->header) > 32 
            ? substr($news->header, 0, 32) . '...' 
            : $news->header;
        
        $plainText = strip_tags($news->content);
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        $description = substr($plainText, 0, 160);
        
        if (strlen($plainText) > 160) {
            $description .= '...';
        }
        
        $description .= ' Читайте подробнее на официальном сайте ' . self::ORGANIZATION_NAME . '.';
        
        $title = $headerTitle . ' | Новости | ' . self::ORGANIZATION_NAME_SHORT;
        $keywords = 'новости фонда, события Саратов, инновации, гранты, преакселерация УМНИК, ' . self::ORGANIZATION_NAME_SHORT;
        
        return $this->createSeoData($title, $description, $keywords, '/news/' . $news->id);
    }
    
    /**
     * Получить SEO данные для страницы преакселерации
     */
    public function getPreaccelerationSeo(): SeoDataDTO
    {
        $title = 'Преакселерационная программа УМНИК | ONLINE Преакселератор | ' . self::ORGANIZATION_NAME_SHORT;
        $description = 'ONLINE Преакселератор «УМНИК» для грантополучателей Фонда содействия инновациям. 8 лет работы, 700+ обученных участников из 89 регионов. Помощь в разработке бизнес-плана, индивидуальные консультации экспертов, сертификат установленного образца. Подайте заявку на участие!';
        $keywords = 'преакселерация УМНИК, онлайн преакселератор, обучение для грантополучателей, бизнес-план для стартапа, подготовка к Студенческому стартапу, программа Старт-1, консультации экспертов, сертификат о прохождении, Фонд содействия инновациям, развитие инновационных проектов, Саратов';
        
        return $this->createSeoData($title, $description, $keywords, '/preacceleration');
    }
    
    /**
     * Получить SEO данные для страницы МТК
     */
    public function getMtkSeo(): SeoDataDTO
    {
        $title = 'Реестр малых технологических компаний (МТК) | ' . self::ORGANIZATION_NAME_SHORT;
        $description = 'Реестр малых технологических компаний (МТК). Центр экспертизы на базе Фонда кластерного развития Саратовской области. Требования к участникам, меры поддержки: налоговые льготы, льготное кредитование, промышленная ипотека. Стоимость экспертизы 60 352 ₽. Подайте заявку онлайн.';
        $keywords = 'реестр МТК, малые технологические компании, центр экспертизы МТК, налоговая льгота для технологических компаний, льготное кредитование, промышленная ипотека, витрина стартапов, гранты для МТК, поддержка инноваций, критерии МТК, Федеральный закон 478-ФЗ, постановление 1847';
        
        return $this->createSeoData($title, $description, $keywords, '/mtk');
    }
    
    /**
     * Получить SEO данные для страницы контактов
     */
    public function getContactsSeo(): SeoDataDTO
    {
        $title = 'Контакты | ' . self::ORGANIZATION_NAME;
        $description = 'Контакты ' . self::ORGANIZATION_NAME . '. Адрес: г. Саратов, ул. Краевая, д. 85, офис 304. Телефон: +7 (8452) 75-64-03, Email: info@fsimp.ru. Часы работы, схема проезда, реквизиты организации, социальные сети ВКонтакте и Max.';
        $keywords = 'контакты фонда, адрес Саратов, телефон ФКРВИ, электронная почта, часы работы, схема проезда, реквизиты организации, ИНН 6450922729, ОГРН 1066400009246, ВКонтакте fsimp64, Telegram fsimp64, как добраться, бизнес-инкубатор Саратов';
        
        return $this->createSeoData($title, $description, $keywords, '/contacts');
    }
    
    /**
     * Создать DTO с SEO данными
     */
    private function createSeoData(
        string $title,
        string $description,
        string $keywords,
        string $path
    ): SeoDataDTO {
        $canonicalUrl = 'https://' . $this->server->getHost() . $path;
        $ogImage = 'https://' . $this->server->getHost() . self::DEFAULT_OG_IMAGE;
        $schema = $this->generateOrganizationSchema();
        
        return new SeoDataDTO(
            title: $title,
            description: $description,
            keywords: $keywords,
            ogTitle: $title,
            ogDescription: $description,
            ogType: 'website',
            ogImage: $ogImage,
            canonicalUrl: $canonicalUrl,
            schema: $schema,
        );
    }
    
    /**
     * Генерация schema.org для организации
     */
    private function generateOrganizationSchema(): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => self::ORGANIZATION_NAME,
            'alternateName' => self::ORGANIZATION_NAME_SHORT,
            'url' => 'https://' . $this->server->getHost(),
            'logo' => 'https://' . $this->server->getHost() . self::DEFAULT_OG_IMAGE,
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Саратов',
                'streetAddress' => 'ул. Краевая, д. 85, офис 304',
                'postalCode' => '410012'
            ],
            'telephone' => '+7 (8452) 75-64-03',
            'email' => 'info@fsimp.ru',
            'sameAs' => [
                'https://vk.com/fsimp64',
                'https://t.me/fsimp64'
            ]
        ];
        
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}