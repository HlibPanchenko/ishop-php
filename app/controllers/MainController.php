<?php

namespace app\controllers;

use app\models\Main;
use RedBeanPHP\R;
use wfm\App;
use wfm\Cache;
use wfm\Language;

class MainController extends AppController
{

    public function indexAction()
    {

        $slides = R::findAll('slider');
        // язык - ru (1), limit - 6
//        $products = $this->model->get_hits(1, 5);
        // get current language
        $current_lang = App::$app->getProperty('language');
        $products = $this->model->get_hits($current_lang, 5);

//        debug($products, 1);
        $this->set(compact('slides', 'products'));
        $this->setMeta(___('main_index_meta_title'),
            ___('main_index_meta_description'),
            ___('main_index_meta_keywords'));
    }

}