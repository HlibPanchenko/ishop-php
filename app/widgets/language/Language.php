<?php

namespace app\widgets\language;

use RedBeanPHP\R;
use wfm\App;

class Language
{

    protected $tpl; // шаблон
    protected $languages; // все языки
    protected $language; // текущий язык

    public function __construct()
    {
        // путь к шаблону
        $this->tpl = __DIR__ . '/lang_tpl.php';
        $this->run();
    }

    protected function run()
    {
        $this->languages = App::$app->getProperty('languages');
        $this->language = App::$app->getProperty('language');
        echo $this->getHtml();
    }

    public static function getLanguages(): array
    {
        return R::getAssoc("SELECT code, title, base, id FROM language ORDER BY base DESC");
    }
    // получаем текущий язык
    public static function getLanguage($languages)
    {
        $lang = App::$app->getProperty('lang');
        // проверяем есть ли у нас такой язык
        if ($lang && array_key_exists($lang, $languages)) {
            $key = $lang; // кладем выбранный язык в переменную key
        } elseif (!$lang) {
            $key = key($languages); // берем первый ключ массива - базовый язык
        } else {
            $lang = h($lang);
            throw new \Exception("Not found language {$lang}", 404);
        }

        $lang_info = $languages[$key]; // вся инфа о языке
        $lang_info['code'] = $key;
        return $lang_info;
    }

    protected function getHtml(): string
    {
        ob_start();
        require_once $this->tpl;
        return ob_get_clean();
    }

}