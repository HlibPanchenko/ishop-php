<?php

namespace wfm;

use RedBeanPHP\R;

class View
{
    // данные, которые будем вставлять в шаблон
    public string $content = '';

    public function __construct(
        public $route, // текущий маршрут
        public $layout = '', // название шаблона
        public $view = '',
        public $meta = [],
    )
    {
        // по умолчанию шаблон - ishop (указали в config/init.php)
        // мы можем переопределить его через контроллер или action контроллера
        if (false !== $this->layout) {
            $this->layout = $this->layout ?: LAYOUT; // LAYOUT - поумолчанию (config/init.php)
        }
    }

    public function render($data)
    {
        if (is_array($data)) {
            extract($data);//из ключей делает переменные и присваивает им знаения
        }
        // replace 'admin\' to 'admin/'
        $prefix = str_replace('\\', '/', $this->route['admin_prefix']);
        // путь к view -  "D:\OSPanel\domains\new-ishop.loc/app/views/admin/Main/index.php"
        $view_file = APP . "/views/{$prefix}{$this->route['controller']}/{$this->view}.php";
        if (is_file($view_file)) {
            ob_start(); // буферизируем view
            // подключаем файл view
            require_once $view_file;
            $this->content = ob_get_clean(); //вид заберем из буфера в свойство $content
        } else {
            throw new \Exception("Не найден вид {$view_file}", 500);
        }
        // подключаем шаблоны
        if (false !== $this->layout) {
            $layout_file = APP . "/views/layouts/{$this->layout}.php";
            if (is_file($layout_file)) {
                require_once $layout_file;
            } else {
                throw new \Exception("Не найден шаблон {$layout_file}", 500);
            }
        }
    }

    public function getMeta()
    {
        $out = '<title>' . App::$app->getProperty('site_name') . '::' . h($this->meta['title']) . '</title>' . PHP_EOL;
        $out .= '<meta name="description" content="' . h($this->meta['description']) . '">' . PHP_EOL;
        $out .= '<meta name="keywords" content="' . h($this->meta['keywords']) . '">' . PHP_EOL;
        return $out;
    }

    public function getDbLogs()
    {
        if (DEBUG) {

            $logs = R::getDatabaseAdapter()
                ->getDatabase()
                ->getLogger();
            // Объединяем логи
            $logs = array_merge($logs->grep( 'SELECT' ),
                $logs->grep( 'INSERT' ),
                $logs->grep( 'UPDATE' ),
                $logs->grep( 'DELETE' ));
            debug($logs);
        }
    }
    // метод, который будет подключать части шаблона
    public function getPart($file, $data = null)
    {
        if (is_array($data)) {
            extract($data);
        }
        // подключаемый шаблон (например header, footer)
        $file = APP . "/views/{$file}.php";
        if (is_file($file)) {
            // подключаем файл - часть шаблона
            require $file;
        } else {
            echo "File {$file} not found...";
        }
    }

}