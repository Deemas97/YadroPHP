<?php
namespace Domain\DTO;

/**
 * Data Transfer Object для SEO-данных
 */
class SeoDataDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $keywords,
        public readonly string $ogTitle,
        public readonly string $ogDescription,
        public readonly string $ogType,
        public readonly string $ogImage,
        public readonly string $canonicalUrl,
        public readonly string $schema,
    ) {}
    
    /**
     * Преобразовать в массив для шаблона
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'meta_description' => $this->description,
            'meta_keywords' => $this->keywords,
            'og_title' => $this->ogTitle,
            'og_description' => $this->ogDescription,
            'og_type' => $this->ogType,
            'og_image' => $this->ogImage,
            'canonical_url' => $this->canonicalUrl,
            'schema' => $this->schema,
        ];
    }
}