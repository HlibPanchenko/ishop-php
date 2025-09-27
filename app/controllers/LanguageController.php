<?php

namespace app\controllers;

use app\models\Cart;
use wfm\App;

class LanguageController extends AppController
{

    public function changeAction()
    {
        // язык на который нужно переключиться
//        $lang = $_GET['lang'] ?? null;
        $lang = get('lang', 's');
        if ($lang) {
            // проверяем есть ли такой язык в контейнере
            if (array_key_exists($lang, App::$app->getProperty('languages'))) {
                // отрезаем базовый URL
                /*Это то откуда мы пришли ($_SERVER['HTTP_REFERER']):
                до: http://new-ishop.loc/en/product/canon-eos-5d
                после: en/product/canon-eos-5d
                */
                $url = trim(str_replace(PATH, '', $_SERVER['HTTP_REFERER']), '/');
//                var_dump($url); die;
                // разбиваем на 2 части... 1-я часть - возможный бывший язык
                /*en(1part)/product/canon-eos-5d(2part)*/
                $url_parts = explode('/', $url, 2);
//                var_dump($url_parts);

                // ищем первую часть (бывший язык) в массиве языков
                if (array_key_exists($url_parts[0], App::$app->getProperty('languages'))) {
                    // присваиваем первой части новый язык, если он не является базовым
                    if ($lang != App::$app->getProperty('language')['code']) {
                        $url_parts[0] = $lang;
                    } else {
                        // если это базовый язык - удалим язык из url, потому что мы не пишем ru/products
                        array_shift($url_parts);
                        /*
                         * То есть мы меняем с en/product на ru/product, но ru - базовый язык
                         * поэтому мы удаляем его с url - /product
                         * */
                    }
                } else {
                    // присваиваем первой части новый язык, если он не является базовым
                    if ($lang != App::$app->getProperty('language')['code']) {
                        array_unshift($url_parts, $lang);
                    }
                }
//                var_dump($url_parts); die;

                // переводим товары в корзине
                Cart::translate_cart(App::$app->getProperty('languages')[$lang]);

                $url = PATH . '/' . implode('/', $url_parts);
                redirect($url);
            }
        }
        // редирект на страницу откуда пришли если нет такого языка
        redirect();
    }

}