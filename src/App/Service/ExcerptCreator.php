<?php
namespace App\Service;

use Core\Service\ServiceInterface;

class ExcerptCreator implements ServiceInterface
{
    private int $defaultLength = 200;
    
    /**
     * Создать excerpt из контента с подсветкой поискового запроса
     */
    public function create(string $content, string $query, ?int $length = null): ?string
    {
        $length = $length ?? $this->defaultLength;
        $text = strip_tags($content);
        
        if (empty($query)) {
            return $this->truncateUtf8($text, $length);
        }
        
        $queryPos = stripos($text, $query);
        
        if ($queryPos !== false) {
            $start = max(0, $queryPos - 50);
            $excerpt = substr($text, $start, $length);
            
            if ($start > 0) {
                $excerpt = '...' . $excerpt;
            }
            
            if (strlen($text) > $start + $length) {
                $excerpt .= '...';
            }
            
            // Подсветка запроса
            $excerpt = preg_replace('/(' . preg_quote($query, '/') . ')/iu', '<em>$1</em>', $excerpt);
        } else {
            $excerpt = $this->truncateUtf8($text, $length);
        }
        
        return $excerpt;
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
        $lastSpace = strrpos($truncated, ' ');
        
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . $suffix;
    }
}