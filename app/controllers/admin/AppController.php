<?php

namespace app\controllers\admin;

use app\models\admin\User;
use app\models\AppModel;
use app\widgets\language\Language;
use RedBeanPHP\R;
use wfm\App;
use wfm\Controller;

class AppController extends Controller
{

    public false|string $layout = 'admin';

    public function __construct($route)
    {
        parent::__construct($route);

        /*
         * Ограничение доступа к админке:
        1) если не админ запрашивает текущую страницу
        2) если action не login-admin
         */
        if (!User::isAdmin() && $route['action'] != 'login-admin') {
            // редирект на старницу авторизации
            redirect(ADMIN . '/user/login-admin');
        }

        // подключение модели чтобы был доступ (подключение) к БД
        new AppModel();
        // записалу инфу об языках в контейнер
        App::$app->setProperty('languages', Language::getLanguages());
        App::$app->setProperty('language', Language::getLanguage(App::$app->getProperty('languages')));

        $lang = App::$app->getProperty('language');
        $categories = R::getAssoc("SELECT c.*, cd.* FROM category c 
                        JOIN category_description cd
                        ON c.id = cd.category_id
                        WHERE cd.language_id = ?", [$lang['id']]);
        App::$app->setProperty("categories_{$lang['code']}", $categories);
    }

}