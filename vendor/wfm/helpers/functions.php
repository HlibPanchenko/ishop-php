<?php


function debug($data, $die = false)
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
    if ($die) {
        die;
    }
}

function h($str)
{
    return htmlspecialchars($str);
}

function redirect($http = false)
{
    // если есть $http, то значит мы хотим сдлеать редирект на конкретный адресс
    if ($http) {
        $redirect = $http;
    } else {
        // если $http не передан, то возвращаем пользователя на ту страницу, откуда пришел ('HTTP_REFERER')
        // или отправим его на главную страницу - PATH
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH;
    }
    // делаем редирект
    header("Location: $redirect");
    die;
}


function base_url()
{
    // PATH – http://new-ishop.loc/ + en or ‘’.
    return PATH . '/' . (\wfm\App::$app->getProperty('lang') ?
            \wfm\App::$app->getProperty('lang') . '/' : '');
}

/**
 * @param string $key Key of GET array
 * @param string $type Values 'i', 'f', 's'
 * @return float|int|string
 */
// get('page')
function get($key, $type = 'i')
{
    $param = $key;
    $$param = $_GET[$param] ?? '';
    //$page = $_GET[$page]
    if ($type == 'i') {
        return (int)$$param;
    } elseif ($type == 'f') {
        return (float)$$param;
    } else {
        return trim($$param);
    }
}

/**
 * @param string $key Key of POST array
 * @param string $type Values 'i', 'f', 's'
 * @return float|int|string
 */
function post($key, $type = 's')
{
    $param = $key;
    $$param = $_POST[$param] ?? '';
    if ($type == 'i') {
        return (int)$$param;
    } elseif ($type == 'f') {
        return (float)$$param;
    } else {
        return trim($$param);
    }
}

function __($key)
{
    // выводить переводную фразу на экран
    echo \wfm\Language::get($key);
}

function ___($key)
{
    // возвращать переводную фразу на экран чтобы потом сохранить в переменную
    return \wfm\Language::get($key);
}

function get_cart_icon($id)
{
    if (!empty($_SESSION['cart']) && array_key_exists($id, $_SESSION['cart'])) {
        $icon = '<i class="fas fa-luggage-cart"></i>';
    } else {
        $icon = '<i class="fas fa-shopping-cart"></i>';
    }
    return $icon;
}

function get_field_value($name)
{
    return isset($_SESSION['form_data'][$name]) ? h($_SESSION['form_data'][$name]) : '';
}

function get_field_array_value($name, $key, $index)
{
    // сохраняем данные формы
    /*
     * $name - category_description
     * $key - id языка
     * */
    return isset($_SESSION['form_data'][$name][$key][$index]) ?
        h ($_SESSION['form_data'][$name][$key][$index]) : '';
}