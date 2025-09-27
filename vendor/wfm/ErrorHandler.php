<?php

namespace wfm;

class ErrorHandler
{

    public function __construct()
    {
        // https://habr.com/ru/post/161483/
        if (DEBUG) {
            error_reporting(-1);
        } else {
            error_reporting(0);
        }
        // отлов исключений, указываем нашу функцию exceptionHandler
        set_exception_handler([$this, 'exceptionHandler']);
        // отлов ошибок, указываем нашу функцию errorHandler
        // $this - указываем что обработчик находится в этом классе
        set_error_handler([$this, 'errorHandler']);
        // чтобы ошибка не выводилась, должны положить в буфер, потом ее оттуда заберем
        ob_start(); // включаем буферизацию
        register_shutdown_function([$this, 'fatalErrorHandler']);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->logError($errstr, $errfile, $errline);
        $this->displayError($errno, $errstr, $errfile, $errline);
    }

    public function fatalErrorHandler()
    {
        // получаем последнюю ошибку
        $error = error_get_last();
        if (!empty($error) && $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
            $this->logError($error['message'], $error['file'], $error['line']);
            ob_end_clean(); // выключаем буфер
            $this->displayError($error['type'], $error['message'], $error['file'], $error['line']);
        } else {
            ob_end_flush(); // отключаем буфер
        }
    }

    public function exceptionHandler(\Throwable $e)
    {
        // ошибку логгируем и показываем
        // $e - объект ошибки
        $this->logError($e->getMessage(), $e->getFile(), $e->getLine());
        $this->displayError('Исключение', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
    }

    protected function logError($message = '', $file = '', $line = '')
    {
        // ошибку надо залоггировать, то есть сохранить в какой-то файл
        file_put_contents(
            LOGS . '/errors.log',
            "[" . date('Y-m-d H:i:s') . "] Текст ошибки: {$message} | Файл: {$file} | Строка: {$line}\n=================\n",
            FILE_APPEND); // FILE_APPEND - дозаписываем, а не перезаписываем содержимое файла
    }
    // показывать ошибку
    protected function displayError($errno, $errstr, $errfile, $errline, $responce = 500)
    {
        if ($responce == 0) {
            $responce = 404;
        }
        // отправляем код ответа в заголовках
        http_response_code($responce);
        // решаем какую страницу показать пользователю
        if ($responce == 404 && !DEBUG) {
            // на продакшене показываем страницу "404"
            require WWW . '/errors/404.php';
            die; // завершаем дальнейшее выполнение кода
        }
        if (DEBUG) {
            // в момент разработки показываем страницу "404"
            require WWW . '/errors/development.php';
        } else {
            require WWW . '/errors/production.php';
        }
        die;
    }

}