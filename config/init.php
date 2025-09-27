<?php
// объявим константы
// наш фреймворка находится в режиме разработки, будем видеть все ошибки
// на хлстинге готовое приложение сделаем false - define("DEBUG", 0);
define("DEBUG", 1);
// корень нашего приложения - D:\OSPanel\domains\new-ishop.loc
define("ROOT", dirname(__DIR__));
// путь к публичной папке
define("WWW", ROOT . '/public');
// путь к папке приложения
define("APP", ROOT . '/app');
// путь к ядру
define("CORE", ROOT . '/vendor/wfm');
// путь к функциям помощникам
define("HELPERS", ROOT . '/vendor/wfm/helpers');
// путь к папке с кешом
define("CACHE", ROOT . '/tmp/cache');
// путь к логам
define("LOGS", ROOT . '/tmp/logs');
// путь к папке конфига
define("CONFIG", ROOT . '/config');
// шаблон сайта по умолчанию - его название
define("LAYOUT", 'ishop');
// address of out website - наша главная страница
define("PATH", 'http://new-ishop.loc');
// путь к админке
define("ADMIN", 'http://new-ishop.loc/admin');
// путь к картинке, если у нас не назначена картинка для товара - картинка по умолчанию
define("NO_IMAGE", 'uploads/no_image.jpg');

// подключим автозагрузчик
require_once ROOT . '/vendor/autoload.php';


