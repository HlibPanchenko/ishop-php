<?php

namespace app\widgets\menu;

use RedBeanPHP\R;
use wfm\App;
use wfm\Cache;

class Menu
{

    // https://www.youtube.com/watch?v=fOMaYSmsiQU
    // https://www.youtube.com/watch?v=Qble3-723bs
    protected $data; // категории из БД
    protected $tree; // сформированное дерево из наших данных
    protected $menuHtml; // HTML код сформированого меню
    protected $tpl; // шаблон который будет использоваться
    protected $container = 'ul'; // во что будет оборачиваться наше меню
    protected $class = 'menu'; // класс, который навешивается на 'ul'
    protected $cache = 3600; // время жизни кеша
    protected $cacheKey = 'ishop_menu'; // ключ под которым данные будут кешироваться
    protected $attrs = []; // атрибуты которые можно добавить к нашему меню
    protected $prepend = ''; // то, что можно добавить перед нашим меню
    protected $language;

    public function __construct($options = [])
    {
        $this->language = App::$app->getProperty('language');
        $this->tpl = __DIR__ . '/menu_tpl.php';
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
    {
        $cache = Cache::getInstance();
        // Проверяем есть ли уже в кеше меню, если есть, то берем его.
        $this->menuHtml = $cache->get("{$this->cacheKey}_{$this->language['code']}");
        // если нету в кеше меню, то получаем категории с БД и формируем с них менюшку(дерево)
        if (!$this->menuHtml) {
            // получаем ассоциативный массив всех категорий
//            $this->data = R::getAssoc("SELECT c.*, cd.* FROM category c
//                        JOIN category_description cd
//                        ON c.id = cd.category_id
//                        WHERE cd.language_id = ?", [$this->language['id']]);
            $this->data = App::$app->getProperty("categories_{$this->language['code']}");

            // формируем дерево
            $this->tree = $this->getTree();
//            debug($this->tree);

            // из дерева делаем верстку
            $this->menuHtml = $this->getMenuHtml($this->tree);
            // кешируем меню
            if ($this->cache) {
                $cache->set("{$this->cacheKey}_{$this->language['code']}", $this->menuHtml, $this->cache);
            }
        }

        $this->output();
    }

    protected function output()
    {
        $attrs = '';
        if (!empty($this->attrs)) {
            foreach ($this->attrs as $k => $v) {
                $attrs .= " $k='$v' ";
            }
        }
        echo "<{$this->container} class='{$this->class}' $attrs>";
        echo $this->prepend; // что-то может выводить перед меню
        echo $this->menuHtml; // вывлдим меню
        echo "</{$this->container}>"; // закрываем тег </ul>
    }

    protected function getTree()
    {
        $tree = [];
        $data = $this->data;
        foreach ($data as $id => &$node) {
            if (!$node['parent_id']) {
                $tree[$id] = &$node;
            } else {
                $data[$node['parent_id']]['children'][$id] = &$node;
            }
        }
        return $tree;
    }

    protected function getMenuHtml($tree, $tab = '')
    {
        $str = '';
        foreach ($tree as $id => $category) {
            $str .= $this->catToTemplate($category, $tab, $id);
        }
        return $str;
    }

    protected function catToTemplate($category, $tab, $id)
    {
        ob_start();
        // подключаем шаблон
        require $this->tpl;
        return ob_get_clean();
    }

}