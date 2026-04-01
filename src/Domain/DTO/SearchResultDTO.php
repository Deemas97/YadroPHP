<?php
namespace Domain\DTO;

/**
 * Data Transfer Object для результата поиска
 */
class SearchResultDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $type,
        public readonly string $url,
        public readonly string $dateFormatted,
        public readonly ?string $excerpt,
        public readonly ?string $image,
        public readonly ?float $relevance,
    ) {}
    
    /**
     * Получить бейдж типа результата
     */
    public function getTypeBadge(): string
    {
        $badges = [
            'news' => 'Новость',
            'page' => 'Страница',
            'document' => 'Документ'
        ];
        
        return $badges[$this->type] ?? $this->type;
    }
    
    /**
     * Получить CSS класс для бейджа типа
     */
    public function getTypeClass(): string
    {
        return $this->type;
    }
}