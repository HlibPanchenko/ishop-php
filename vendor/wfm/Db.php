<?php

namespace wfm;

use RedBeanPHP\R;

class Db
{
    use TSingleton; // trait

    private function __construct()
    {
        $db = require_once CONFIG . '/config_db.php'; // настройки подключения
        R::setup($db['dsn'], $db['user'], $db['password']);
        // testConnection() - для проверки получилось и подключиться к БД
        if (!R::testConnection()) {
            throw new \Exception('No connection to DB', 500);
        }
        R::freeze(true);
        if (DEBUG) {
            // будем видеть только в режиме разработки
            R::debug(true, 3); // возвращает SQL запрос, который он будет выполнять
        }
        R::ext('xdispense', function( $type ){
            return R::getRedBean()->dispense( $type );
        });
    }
}