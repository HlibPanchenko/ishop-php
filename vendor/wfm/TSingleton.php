<?php

namespace wfm;


trait TSingleton
{
    // типизируем self|null = ?self
    private static ?self $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): static
    {
        // если есть объект записанный в $instance, тогда вернем его - static::$instance
        // если нету, создадим его - static::$instance = new static()
        return static::$instance ?? static::$instance = new static();
    }

}
