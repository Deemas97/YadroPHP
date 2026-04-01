<?php
namespace Core\Service\Renderer;

use Core\Service\CoreServiceInterface;

class HtmlMinificator implements CoreServiceInterface
{
    private array $preserveTags = ['pre', 'code', 'textarea'];
    private bool $removeComments = true;
    private bool $collapseWhitespace = true;
    private bool $removeAttributeWhitespace = true;
    private bool $minifyInlineCss = true;
    private bool $minifyInlineJs = true;

    public function minify(string $html): string
    {
        if (empty($html)) {
            return '';
        }
        
        $preserved = [];
        $html = $this->extractPreservedContent($html, $preserved);
        
        if ($this->minifyInlineCss || $this->minifyInlineJs) {
            $html = $this->minifyInlineContent($html);
        }
        
        if ($this->removeComments) {
            $html = $this->removeHtmlComments($html);
        }
        
        if ($this->removeAttributeWhitespace) {
            $html = $this->cleanAttributeWhitespace($html);
        }
        
        if ($this->collapseWhitespace) {
            $html = $this->collapseWhitespaceSafe($html);
        }
        
        $html = $this->trimTagWhitespace($html);
        
        $html = $this->restorePreservedContent($html, $preserved);
        
        return trim($html);
    }
    
    private function extractPreservedContent(string $html, array &$preserved): string
    {
        $pattern = '/<(' . implode('|', $this->preserveTags) . ')(\s[^>]*)?>(.*?)<\/\1>/is';
        
        return preg_replace_callback($pattern, function($matches) use (&$preserved) {
            $placeholder = '%%PRESERVED_' . count($preserved) . '%%';
            $preserved[$placeholder] = $matches[0];
            return $placeholder;
        }, $html);
    }
    
    private function restorePreservedContent(string $html, array $preserved): string
    {
        foreach ($preserved as $placeholder => $content) {
            $html = str_replace($placeholder, $content, $html);
        }
        return $html;
    }
    
    private function minifyInlineContent(string $html): string
    {
        if ($this->minifyInlineCss) {
            $html = preg_replace_callback('/<style([^>]*)>(.*?)<\/style>/is', function($matches) {
                $attributes = $matches[1];
                $css = $matches[2];
                
                $minifiedCss = $this->minifyCss($css);
                
                return '<style' . $attributes . '>' . $minifiedCss . '</style>';
            }, $html);
        }
        
        if ($this->minifyInlineJs) {
            $html = preg_replace_callback('/<script([^>]*)>(.*?)<\/script>/is', function($matches) {
                $attributes = $matches[1];
                $js = $matches[2];
                
                if (preg_match('/\ssrc=/i', $attributes)) {
                    return $matches[0];
                }
                
                if (strpos($js, '{{') === false) {
                    $minifiedJs = $this->minifyJs($js);
                } else {
                    $minifiedJs = $js;
                }
                
                return '<script' . $attributes . '>' . $minifiedJs . '</script>';
            }, $html);
        }
        
        return $html;
    }
    
    private function minifyCss(string $css): string
    {
        // Удаляем комментарии
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Удаляем пробелы
        $css = preg_replace('/\s+/', ' ', $css);
        
        // Удаляем пробелы вокруг { }
        $css = preg_replace('/\s*{\s*/', '{', $css);
        $css = preg_replace('/\s*}\s*/', '}', $css);
        
        // Удаляем ; перед }
        $css = preg_replace('/;}/', '}', $css);
        
        // Удаляем пробелы после ,
        $css = preg_replace('/,\s+/', ',', $css);
        
        // Удаляем пробелы вокруг :
        $css = preg_replace('/\s*:\s*/', ':', $css);
        
        // Удаляем пробелы вокруг ;
        $css = preg_replace('/\s*;\s*/', ';', $css);
        
        // Удаляем нулевые значения с единицами измерения
        $css = preg_replace('/:0(px|em|rem|%|vh|vw)/', ':0', $css);
        
        // Удаляем последнюю точку с запятой в блоке
        $css = preg_replace('/;}/', '}', $css);
        
        return trim($css);
    }
    
    private function minifyJs(string $js): string
    {
        // Удаляем однострочные комментарии (осторожно, не трогаем в строках)
        $js = preg_replace('/\/\/[^\n]*/', '', $js);
        
        // Удаляем многострочные комментарии
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        
        // Удаляем лишние пробелы
        $js = preg_replace('/\s+/', ' ', $js);
        
        // Удаляем пробелы вокруг операторов (упрощённо)
        $js = preg_replace('/\s*([=+\-*\/%&|!<>])\s*/', '$1', $js);
        
        // Удаляем пробелы вокруг точки с запятой
        $js = preg_replace('/\s*;\s*/', ';', $js);
        
        // Удаляем точку с запятой в конце блока
        $js = preg_replace('/;}/', '}', $js);
        
        return trim($js);
    }
    
    private function removeHtmlComments(string $html): string
    {
        // Сохраняем условные комментарии IE
        $conditionalComments = [];
        $html = preg_replace_callback('/<!--\[if\s+[^\]]+\]>.*?<!\[endif\]-->/is', function($matches) use (&$conditionalComments) {
            $placeholder = '%%CONDITIONAL_' . count($conditionalComments) . '%%';
            $conditionalComments[$placeholder] = $matches[0];
            return $placeholder;
        }, $html);
        
        // Удаляем остальные комментарии
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        
        // Восстанавливаем условные комментарии
        foreach ($conditionalComments as $placeholder => $content) {
            $html = str_replace($placeholder, $content, $html);
        }
        
        return $html;
    }
    
    private function cleanAttributeWhitespace(string $html): string
    {
        // Удаляем пробелы вокруг =
        $html = preg_replace('/\s*=\s*/', '=', $html);
        
        // Удаляем лишние пробелы между атрибутами
        $html = preg_replace('/\s{2,}/', ' ', $html);
        
        // Удаляем пробелы перед закрывающим тегом
        $html = preg_replace('/\s+\/?>/', '>', $html);
        
        return $html;
    }
    
    private function collapseWhitespaceSafe(string $html): string
    {
        // Заменяем переносы строк и табуляции на пробелы
        $html = preg_replace('/\n\s*/', ' ', $html);
        $html = preg_replace('/\t+/', ' ', $html);
        
        // Схлопываем множественные пробелы
        $html = preg_replace('/ {2,}/', ' ', $html);
        
        return $html;
    }
    
    private function trimTagWhitespace(string $html): string
    {
        // Удаляем пробелы между закрывающим и открывающим тегом
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Удаляем пробелы перед закрывающим тегом
        $html = preg_replace('/\s+</', '<', $html);
        
        // Удаляем пробелы после открывающего тега
        $html = preg_replace('/>\s+/', '>', $html);
        
        return $html;
    }
    
    public function getStats(string $original, string $minified): array
    {
        $originalSize = strlen($original);
        $minifiedSize = strlen($minified);
        $saved = $originalSize - $minifiedSize;
        $percent = $originalSize > 0 ? round(($saved / $originalSize) * 100, 2) : 0;
        
        return [
            'original_size' => $originalSize,
            'minified_size' => $minifiedSize,
            'saved_bytes' => $saved,
            'saved_percent' => $percent,
            'compression_ratio' => $originalSize > 0 ? round($minifiedSize / $originalSize, 3) : 0,
        ];
    }
}