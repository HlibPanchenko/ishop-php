<?php


namespace app\controllers;


use app\models\AppModel;
use app\models\Wishlist;
use app\widgets\language\Language;
use RedBeanPHP\R;
use wfm\App;
use wfm\Controller;

class AppController extends Controller
{

    public function __construct($route)
    {
        parent::__construct($route);
        new AppModel();
        // запишем языки в контейнер
        App::$app->setProperty('languages', Language::getLanguages());
        App::$app->setProperty('language', Language::getLanguage(App::$app->getProperty('languages')));

        // загружаем переводные фразы
        $current_lang = App::$app->getProperty('language');
        \wfm\Language::load($current_lang['code'], $this->route);
        /*
            загрузилось, теперь можем посмотреть $lang_view,$lang_layout, $lang_data
            [tpl_search] => Поиск...; [tpl_login] => Авторизация; [tpl_signup] => Регистрация
            debug(\wfm\Language::$lang_data, 1);
         * */

        $categories = R::getAssoc("SELECT c.*, cd.* FROM category c 
                        JOIN category_description cd
                        ON c.id = cd.category_id
                        WHERE cd.language_id = ?", [$current_lang['id']]);
        // у нас будут "categories_ru", "categories_en"
        App::$app->setProperty("categories_{$current_lang['code']}", $categories);

        App::$app->setProperty('wishlist', Wishlist::get_wishlist_ids());

//        print_r(App::$app->getProperty('languages')); // Array ( [ru] => Array ( [title] => Русский [base] => 1 [id] => 1 ) [en] => Array ( [title] => English [base] => 0 [id] => 2 ) )
//        print_r(App::$app->getProperty('language')); // Array ( [title] => English [base] => 0 [id] => 2 [code] => en )
    }

}