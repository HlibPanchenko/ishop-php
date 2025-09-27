<?php

namespace wfm;

abstract class Controller
{
    public array $data = []; // массив с данными, которые загружаются из модели и передаются в view
    // чтобы из контроллера в шаблон могли передавать метаданные страницы
    public array $meta = ['title'=>'', 'description'=>'', 'keywords'=>''];
    public false|string $layout = ''; // шаблон
    // с контроллера мы можем переопределить вид. По умолчанию он  будет соответствовать названию екшена
    // и будет находиться в папке с названием контроллера
    public string $view = '';
    public object $model;

    public function __construct(public $route = [])
    {

    }

    public function getModel()
    {
        // путь к моделе. модель называется по имени контроллера. имя контроллера находится в route
        $model = 'app\models\\' . $this->route['admin_prefix'] . $this->route['controller'];
        if (class_exists($model)) {
            $this->model = new $model(); // создаем экземпляр модели
            // то есть для контроллера Main есть в папке models соответствующая модель, то мы создадим экземпляр модели
        }
    }

    public function getView()
    {
        // по умолчанию название вида совпадает с названием action
        $this->view = $this->view ?: $this->route['action'];
        // мы получаем данные в Router, потом пробрасываем в контроллер, а из контроллера в view
        // $this->route - текущий маршрут
        // $this->layout - шаблон; $this->view - вид
        (new View($this->route, $this->layout, $this->view, $this->meta))->render($this->data);
    }

    public function set($data)
    {
        $this->data = $data; // кладем данные в массив data

    }

    public function setMeta($title = '', $description = '', $keywords = '')
    {
        $this->meta = [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
        ];
    }


    public function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function loadView($view, $vars = [])
    {
        // $vars - массив данных для этого вида
        extract($vars);
        $prefix = str_replace('\\', '/', $this->route['admin_prefix']);
        require APP . "/views/{$prefix}{$this->route['controller']}/{$view}.php";
        die;
    }

    public function error_404($folder = 'Error', $view = 404, $response = 404)
    {
        // будем искать вид в папке app/views/$folder/$view
        http_response_code($response);
        $this->setMeta(___('tpl_error_404'));
        // переопределяем вид страницы, чтобы вид искался в нужнем нам контроллере.
        // Название контроллера совпадает с папкой вида
        // папка вида:
        $this->route['controller'] = $folder;
        // имя вида:
        $this->view = $view;
    }

}