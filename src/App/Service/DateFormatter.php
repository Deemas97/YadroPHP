<?php
namespace App\Service;

use Core\Service\ServiceInterface;

class DateFormatter implements ServiceInterface
{
    private const MONTHS_RU = [
        'January' => 'января',
        'February' => 'февраля',
        'March' => 'марта',
        'April' => 'апреля',
        'May' => 'мая',
        'June' => 'июня',
        'July' => 'июля',
        'August' => 'августа',
        'September' => 'сентября',
        'October' => 'октября',
        'November' => 'ноября',
        'December' => 'декабря'
    ];
    
    /**
     * Форматирование даты в русском стиле (день месяц год)
     */
    public function formatRuDate(string $date): string
    {
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = self::MONTHS_RU[date('F', $timestamp)];
        $year = date('Y', $timestamp);
        
        return $day . ' ' . $month . ' ' . $year;
    }
    
    /**
     * Форматирование даты в коротком русском стиле (день месяц)
     */
    public function formatRuDateShort(string $date): string
    {
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = self::MONTHS_RU[date('F', $timestamp)];
        
        return $day . ' ' . $month;
    }
    
    /**
     * Форматирование времени
     */
    public function formatTime(string $datetime): string
    {
        return date('H:i', strtotime($datetime));
    }
    
    /**
     * Форматирование даты и времени
     */
    public function formatDateTime(string $datetime): string
    {
        return $this->formatRuDate($datetime) . ' ' . $this->formatTime($datetime);
    }
}