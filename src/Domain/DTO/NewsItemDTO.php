<?php
namespace Domain\DTO;

/**
 * Data Transfer Object для новости
 */
class NewsItemDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $header,
        public readonly string $content,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?int $imageId,
        public readonly ?string $imageFilename,
        public readonly ?string $imageAlt,
        public readonly string $createdAtFormatted,
        public readonly ?string $updatedAtFormatted,
        public readonly int $readingTime,
    ) {}
    
    /**
     * Получить URL изображения
     */
    public function getImageUrl(): ?string
    {
        if (!$this->imageFilename) {
            return null;
        }
        return YADRO_PHP__STORAGE_DIR . '/gallery/' . $this->imageFilename;
    }
    
    /**
     * Получить alt для изображения
     */
    public function getImageAlt(): string
    {
        return $this->imageAlt ?? $this->header;
    }
    
    /**
     * Создать excerpt для отображения
     */
    public function getExcerpt(int $length = 200): string
    {
        $text = strip_tags($this->content);
        return $this->truncateUtf8($text, $length);
    }
    
    /**
     * Усечение UTF-8 строки
     */
    private function truncateUtf8(string $text, int $length, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $truncated = substr($text, 0, $length);
        
        // Проверяем, не разорвали ли мы многобайтовый символ
        while (strlen($truncated) > 0) {
            $lastChar = ord(substr($truncated, -1));
            if (($lastChar & 0xC0) === 0xC0 || ($lastChar & 0xC0) === 0x80) {
                $truncated = substr($truncated, 0, -1);
            } else {
                break;
            }
        }
        
        $lastSpace = strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . $suffix;
    }
}