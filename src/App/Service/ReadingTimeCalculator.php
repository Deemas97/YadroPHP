<?php
namespace App\Service;

use Core\Service\ServiceInterface;

class ReadingTimeCalculator implements ServiceInterface
{
    private int $wordsPerMinute = 200;
    
    /**
     * Рассчитать время чтения в минутах
     */
    public function calculate(string $content): int
    {
        $text = strip_tags($content);
        $wordCount = str_word_count($text, 0, 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ0123456789');
        
        return max(1, ceil($wordCount / $this->wordsPerMinute));
    }
}