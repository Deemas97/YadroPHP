<?php
namespace App\Service;

use Domain\DTO\NewsItemDTO;
use Domain\DTO\PaginationDTO;
use Core\Service\DBConnectionManager;
use Core\Service\ServiceInterface;
use Infrastructure\DataBase\DBConnectorInterface;

class NewsService implements ServiceInterface
{
    private DBConnectorInterface $db;
    
    public function __construct(
        private DBConnectionManager $dbManager,
        private DateFormatter $dateFormatter,
        private ReadingTimeCalculator $readingTimeCalculator,
    ) {
        $this->db = $this->dbManager->getConnection();
    }
    
    /**
     * Получить новости с пагинацией
     */
    public function getPaginatedNews(int $page, int $perPage = 9): array
    {
        $offset = ($page - 1) * $perPage;
        
        $newsData = $this->db->query(
            "SELECT n.*, i.filename as image_filename, i.alt_text as image_alt
             FROM news n
             LEFT JOIN images i ON n.image_id = i.id
             ORDER BY n.created_at DESC
             LIMIT $offset, $perPage"
        );
        
        $totalResult = $this->db->query("SELECT COUNT(*) as total FROM news");
        $totalCount = (int)($totalResult[0]['total'] ?? 0);
        
        $news = [];
        foreach ($newsData as $item) {
            $news[] = $this->hydrateNewsItem($item);
        }
        
        $pagination = new PaginationDTO($page, $perPage, $totalCount, (int)ceil($totalCount / $perPage));
        
        return [$news, $pagination];
    }
    
    /**
     * Получить последние новости
     */
    public function getLatestNews(int $limit = 3): array
    {
        $newsData = $this->db->query(
            "SELECT n.*, i.filename as image_filename, i.alt_text as image_alt
             FROM news n
             LEFT JOIN images i ON n.image_id = i.id
             ORDER BY n.created_at DESC 
             LIMIT $limit"
        );
        
        $news = [];
        foreach ($newsData as $item) {
            $news[] = $this->hydrateNewsItem($item);
        }
        
        return $news;
    }
    
    /**
     * Получить новость по ID
     */
    public function getNewsById(int $id): ?NewsItemDTO
    {
        $result = $this->db->query(
            "SELECT n.*, i.filename as image_filename, i.alt_text as image_alt
             FROM news n
             LEFT JOIN images i ON n.image_id = i.id
             WHERE n.id = $id
             LIMIT 1"
        );
        
        if (empty($result)) {
            return null;
        }
        
        return $this->hydrateNewsItem($result[0]);
    }
    
    /**
     * Получить соседние новости
     */
    public function getAdjacentNews(int $currentId): array
    {
        $prev = $this->db->query(
            "SELECT n.id, n.header, i.filename as image_filename
             FROM news n
             LEFT JOIN images i ON n.image_id = i.id
             WHERE n.id < $currentId
             ORDER BY n.id DESC LIMIT 1"
        );
        
        $next = $this->db->query(
            "SELECT n.id, n.header, i.filename as image_filename
             FROM news n
             LEFT JOIN images i ON n.image_id = i.id
             WHERE n.id > $currentId
             ORDER BY n.id ASC LIMIT 1"
        );
        
        return [
            'prev' => $prev[0] ?? null,
            'next' => $next[0] ?? null,
        ];
    }
    
    /**
     * Гидратация новости в DTO
     */
    private function hydrateNewsItem(array $item): NewsItemDTO
    {
        return new NewsItemDTO(
            id: (int)$item['id'],
            header: $item['header'],
            content: $item['content'],
            createdAt: $item['created_at'],
            updatedAt: $item['updated_at'] ?? null,
            imageId: isset($item['image_id']) ? (int)$item['image_id'] : null,
            imageFilename: $item['image_filename'] ?? null,
            imageAlt: $item['image_alt'] ?? null,
            createdAtFormatted: $this->dateFormatter->formatRuDate($item['created_at']),
            updatedAtFormatted: !empty($item['updated_at']) ? $this->dateFormatter->formatRuDate($item['updated_at']) : null,
            readingTime: $this->readingTimeCalculator->calculate($item['content']),
        );
    }
}