<?php
namespace App\Service;

use Domain\DTO\SearchResultDTO;
use Core\Service\DBConnectionManager;
use Core\Service\ServiceInterface;
use Infrastructure\DataBase\DBConnectorInterface;

class SearchService implements ServiceInterface
{
    public const ITEMS_PER_PAGE = 10;
    public const MAX_QUERY_LENGTH = 100;
    
    private DBConnectorInterface $db;
    
    public function __construct(
        private DBConnectionManager $dbManager,
        private DateFormatter $dateFormatter,
        private ExcerptCreator $excerptCreator,
    )
    {
        $this->db = $this->dbManager->getConnection();
    }
    
    /**
     * Поиск по новостям
     */
    public function searchNews(
        string $query,
        string $sort,
        int $page,
        int &$totalResults
    ): array {
        $query = $this->sanitizeQuery($query);
        
        if (empty($query)) {
            $totalResults = 0;
            return [];
        }
        
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;
        $escapedQuery = $this->db->escape($query);
        
        // Разбиваем запрос на слова
        $searchWords = array_filter(explode(' ', $query));
        $wordConditions = [];
        $relevanceScore = [];
        
        foreach ($searchWords as $word) {
            $wordEscaped = $this->db->escape($word);
            $relevanceScore[] = "(CASE WHEN header LIKE '%$wordEscaped%' THEN 3 ELSE 0 END)";
            $relevanceScore[] = "(CASE WHEN content LIKE '%$wordEscaped%' THEN 1 ELSE 0 END)";
            $wordConditions[] = "header LIKE '%$wordEscaped%'";
            $wordConditions[] = "content LIKE '%$wordEscaped%'";
        }
        
        $whereClause = "WHERE header LIKE '%$escapedQuery%' OR content LIKE '%$escapedQuery%'";
        
        if (!empty($wordConditions)) {
            $whereClause .= " OR (" . implode(' OR ', $wordConditions) . ")";
        }
        
        $relevanceSql = !empty($relevanceScore) ? '+' . implode('+', $relevanceScore) : '0';
        
        $orderBy = match($sort) {
            'date_desc' => "ORDER BY n.created_at DESC",
            'date_asc' => "ORDER BY n.created_at ASC",
            default => "ORDER BY ($relevanceSql) DESC, n.created_at DESC"
        };
        
        // Получаем общее количество
        $countSql = "SELECT COUNT(*) as total FROM news n $whereClause";
        $countResult = $this->db->query($countSql);
        $totalResults = (int)($countResult[0]['total'] ?? 0);
        
        if ($totalResults === 0) {
            return [];
        }
        
        // Получаем результаты
        $sql = "SELECT 
                    n.id,
                    n.header as title,
                    n.content,
                    n.created_at as date,
                    'news' as type,
                    CONCAT('/news/', n.id) as url,
                    i.filename as image_filename,
                    ($relevanceSql) as relevance
                FROM news n
                LEFT JOIN images i ON n.image_id = i.id
                $whereClause
                $orderBy
                LIMIT $offset, " . self::ITEMS_PER_PAGE;
        
        $searchData = $this->db->query($sql) ?: [];
        
        $results = [];
        foreach ($searchData as $item) {
            $results[] = new SearchResultDTO(
                id: (int)$item['id'],
                title: $item['title'],
                type: $item['type'],
                url: $item['url'],
                dateFormatted: $this->dateFormatter->formatRuDate($item['date']),
                excerpt: $this->excerptCreator->create($item['content'], $query),
                image: !empty($item['image_filename']) 
                    ? YADRO_PHP__STORAGE_DIR . '/gallery/' . $item['image_filename'] 
                    : null,
                relevance: isset($item['relevance']) ? (float)$item['relevance'] : null,
            );
        }
        
        return $results;
    }
    
    /**
     * Очистка поискового запроса
     */
    public function sanitizeQuery(string $query): string
    {
        $query = trim($query);
        $query = strip_tags($query);
        $query = htmlspecialchars_decode($query, ENT_QUOTES);
        $query = substr($query, 0, self::MAX_QUERY_LENGTH);
        $query = preg_replace('/\s+/', ' ', $query);
        
        return $query;
    }
    
    /**
     * Очистка типа поиска
     */
    public function sanitizeType(string $type): string
    {
        $allowedTypes = ['all', 'news', 'pages', 'documents'];
        return in_array($type, $allowedTypes) ? $type : 'all';
    }
    
    /**
     * Очистка сортировки
     */
    public function sanitizeSort(string $sort): string
    {
        $allowedSort = ['relevance', 'date_desc', 'date_asc'];
        return in_array($sort, $allowedSort) ? $sort : 'relevance';
    }
}