<?php

namespace wfm;

class Language
{
    // массив со всеми переводными фразами страницы (и шаблона, и вида)
    public static array $lang_data = [];
    // массив с переводными фразами шаблона
    public static array $lang_layout = [];
    // массив с переводными фразами вида
    public static array $lang_view = [];

    // принимает код языка и вид (приходит route)
    public static function load($code, $view)
    // загружаем переводные фразы в массивы
    {
        $lang_layout = APP . "/languages/{$code}.php";
        $lang_view = APP . "/languages/{$code}/{$view['controller']}/{$view['action']}.php";
        if (file_exists($lang_layout)) {
            // подключаем файлы с переводными фразами
            // по сути получаем массивы фраз, так как
            // require_once $lang_layout возвращает массив переводных фраз
            self::$lang_layout = require_once $lang_layout;
        }
        if (file_exists($lang_view)) {
            self::$lang_view = require_once $lang_view;
        }
        // сливаем 2 массива в 1 (переводные фразы шаблона и вида)
        self::$lang_data = array_merge(self::$lang_layout, self::$lang_view);
    }

    public static function get($key)
    {
        // по ключу влзвращает переводную фразу
        // 'home' => 'Главная'
        return self::$lang_data[$key] ?? $key;
    }

}