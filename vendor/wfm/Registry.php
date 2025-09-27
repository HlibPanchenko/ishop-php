<?php

namespace wfm;

class Registry
{

    use TSingleton;
    // контейнер куда будем складывать данные
    protected static array $properties = []; // тип array

    public function setProperty($name, $value)
    {
        self::$properties[$name] = $value; // записываем в контейнйер данные
    }

    public function getProperty($name)
    {
        return self::$properties[$name] ?? null; // получаем данные с контейнера
    }

    public function getProperties(): array
    {
        return self::$properties; // возвращаем все что есть в контейнере
    }

}