<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * Генерирует уникальное значение STRING
     * @return string
     * @throws \Exception
     */
    public static function UniqStringId(): string
    {
        $m = microtime(true) - 1546300800;
        return sprintf('%08x%05x', floor($m), ($m - floor($m)) * 1000000) . sprintf('%04d', random_int(0, 9999));
    }
}