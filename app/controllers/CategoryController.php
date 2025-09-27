<?php


namespace app\controllers;


use app\models\Breadcrumbs;
use app\models\Category;
use wfm\App;
use wfm\Pagination;

/** @property Category $model */
class CategoryController extends AppController
{

    public function viewAction()
    {
        $lang = App::$app->getProperty('language');
        $category = $this->model->get_category($this->route['slug'], $lang);
//        debug($category, 1);
        if (!$category) {
            $this->error_404();
            return;
        }

        $breadcrumbs = Breadcrumbs::getBreadcrumbs($category['id']);
        // получаем строку idшников вложенных категорий
        $ids = $this->model->getIds($category['id']);
        // добавляем еще id выбранной категории
        $ids = !$ids ? $category['id'] : $ids . $category['id'];

        // Pagination
        $page = get('page');
        $perpage = App::$app->getProperty('pagination');
        // общее количество товаров
        $total = $this->model->get_count_products($ids);
        $pagination = new Pagination($page, $perpage, $total);
        // вернет нам информацию о том с какого товара делать выборку (limit)
        $start = $pagination->getStart();
//        echo $pagination;

        $products = $this->model->get_products($ids, $lang, $start, $perpage);
//        debug($products);
        $this->setMeta($category['title'], $category['description'], $category['keywords']);
        $this->set(compact('products', 'category', 'breadcrumbs', 'total', 'pagination'));
    }

}