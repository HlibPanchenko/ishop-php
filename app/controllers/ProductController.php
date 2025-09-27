<?php

namespace app\controllers;

use app\models\Breadcrumbs;
use app\models\Product;
use wfm\App;

/** @property Product $model */
class ProductController extends AppController
{
    public function viewAction()
    {
//        debug($this->route, 1);
//        debug(ROOT, 1);

        $lang = App::$app->getProperty('language');
        $product = $this->model->get_product($this->route['slug'], $lang);
//        debug($product, 1);

        if (!$product) {
//            throw new \Exception("Товар по запросу {$this->route['slug']} не найден", 404);
            $this->error_404();
            return;
        }

        $breadcrumbs = Breadcrumbs::getBreadcrumbs($product['category_id'],$product['title']);
//        debug($breadcrumbs);

        $gallery = $this->model->get_gallery($product['id']);
        $this->setMeta($product['title'], $product['description'], $product['keywords']);
        $this->set(compact('product', 'gallery', 'breadcrumbs'));
    }
}