<?php
namespace Domain\DTO;

/**
 * Data Transfer Object для пагинации
 */
class PaginationDTO
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $lastPage,
    ) {}
    
    /**
     * Проверка, есть ли предыдущая страница
     */
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }
    
    /**
     * Проверка, есть ли следующая страница
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->lastPage;
    }
    
    /**
     * Получить номер предыдущей страницы
     */
    public function getPrevPage(): int
    {
        return $this->hasPrev() ? $this->currentPage - 1 : 1;
    }
    
    /**
     * Получить номер следующей страницы
     */
    public function getNextPage(): int
    {
        return $this->hasNext() ? $this->currentPage + 1 : $this->lastPage;
    }
    
    /**
     * Получить диапазон страниц для отображения
     */
    public function getPageRange(int $delta = 2): array
    {
        $range = [];
        $start = max(1, $this->currentPage - $delta);
        $end = min($this->lastPage, $this->currentPage + $delta);
        
        for ($i = $start; $i <= $end; $i++) {
            $range[] = $i;
        }
        
        return $range;
    }
}