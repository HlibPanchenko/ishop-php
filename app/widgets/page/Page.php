<?php

namespace app\widgets\page;

use RedBeanPHP\R;
use wfm\App;
use wfm\Cache;

class Page
{

    protected $language;
    protected string $container = 'ul';
    protected string $class = 'page-menu';
    protected int $cache = 3600; //time
    protected string $cacheKey = 'ishop_page_menu';
    protected string $menuPageHtml; // готовый html
    protected string $prepend = ''; // данные которые хотим добавить перед меню
    protected $data; // data from DB

    public function __construct($options = [])
    {
        $this->language = App::$app->getProperty('language');
        $this->getOptions($options);
        $this->run();
    }

    protected function getOptions($options)
    {
        foreach ($options as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    protected function run()
        //построение виджета
    {
        $cache = Cache::getInstance();
        $this->menuPageHtml = $cache->get("{$this->cacheKey}_{$this->language['code']}");
        // если виджет получили один раз, то кешируем его чтобы оптимизировать и не делать доп запросы
        // если в кеше нет данных, получаем их из БД
        if (!$this->menuPageHtml) {
            $this->data = R::getAssoc("SELECT p.*, pd.* FROM page p 
                        JOIN page_description pd
                        ON p.id = pd.page_id
                        WHERE pd.language_id = ?", [$this->language['id']]);
            // сторим из данных менюшку
            $this->menuPageHtml = $this->getMenuPageHtml();
            // если кеширование включено, кладем сформированное меню в кеш
            if ($this->cache) {
                $cache->set("{$this->cacheKey}_{$this->language['code']}", $this->menuPageHtml, $this->cache);
            }
        }
        // оборачтваем данные в контейнер
        $this->output();
    }

    protected function getMenuPageHtml()
    {
        $html = '';
        foreach ($this->data as $k => $v) {
            $html .= "<li><a href='page/{$v['slug']}'>{$v['title']}</a></li>";
        }
        return $html;
    }

    protected function output()
    {
        echo "<{$this->container} class='{$this->class}'>";
        echo $this->prepend;
        echo $this->menuPageHtml;
        echo "</{$this->container}>";
    }

}