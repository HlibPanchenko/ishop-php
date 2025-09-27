<?php

namespace wfm;

class App
{
    public static $app; // сюда будет записан наш контейнер

    public function __construct()
    {
        $query = trim(urldecode($_SERVER['QUERY_STRING']), '/'); //текущий url-адресс
        new ErrorHandler();
        session_start();
        // иницилизируем приложение - объект класса Registry - наш контейнер
        self::$app = Registry::getInstance();
        // заполняем наш контейнер
        $this->getParams();
//        echo $query;
        Router::dispatch($query);
    }
    // подключаем параметры для нашего приложения. Создали их в config/params.php
    protected function getParams()
    {
        // получаем массив параметров с config/params.php
        $params = require_once CONFIG . '/params.php';
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                // все параметры записываем в контейнер
                self::$app->setProperty($k, $v);
            }
        }
    }

}