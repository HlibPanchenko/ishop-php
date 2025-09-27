<?php


namespace app\controllers;


use app\models\Search;
use wfm\App;
use wfm\Pagination;

/** @property Search $model */
class SearchController extends AppController
{

    public function indexAction()
    {
        $s = get('s', 's'); // получаем поисковый запрос
        $lang = App::$app->getProperty('language');
        $page = get('page');
        $perpage = App::$app->getProperty('pagination');
        // к-во товаров
        $total = $this->model->get_count_find_products($s, $lang);
        $pagination = new Pagination($page, $perpage, $total);
        // с какого товара начинаем выборку
        $start = $pagination->getStart();
        // получим продукты
        $products = $this->model->get_find_products($s, $lang, $start, $perpage);
        $this->setMeta(___('tpl_search_title'));
        // передаем в вид
        $this->set(compact('s', 'products', 'pagination', 'total'));
    }

}