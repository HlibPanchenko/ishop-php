<?php

// dirname(__DIR__) - вышли в корень проекта
require_once dirname(__DIR__) . '/config/init.php';
require_once HELPERS . '/functions.php';
require_once CONFIG . '/routes.php';

if (PHP_MAJOR_VERSION < 8) {
    die('Необходима версия PHP >= 8');
}

new \wfm\App(); // иницилизируем приложение
//теперь нам доступен контенйер нашего приложения
//var_dump(\wfm\App::$app->getProperties());

//throw new Exception('Возникла ошибочка!', 404);
//echo $test;

