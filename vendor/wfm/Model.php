<?php

namespace wfm;

use RedBeanPHP\R;
use Valitron\Validator;

abstract class Model
{
    // автозаполнение моделей данными, если приходит форма, то берем только те данные, которые нам надо
    public array $attributes = [];
    public array $errors = [];
    // массив правил валидации
    public array $rules = [];
    // чтобы указывать какое именно поле не прошло валижацию
    public array $labels = [];

    public function __construct()
    {
        // получим объект подключения к БД
        Db::getInstance();
    }

//    public function load($data)
//    {
//        foreach ($this->attributes as $name => $value) {
//            if (isset($data[$name])) {
//                $this->attributes[$name] = $data[$name];
//            }
//        }
//    }

    public function load($post = true)
    {
        // берем данные либо с $_POST, либо $_GET
        $data = $post ? $_POST : $_GET;
        foreach ($this->attributes as $name => $value) {
            if (isset($data[$name])) {
                $this->attributes[$name] = $data[$name];
            }
        }
    }

    public function validate($data): bool
    {
        // папка с языковыми файлами
        Validator::langDir(APP . '/languages/validator/lang');
        // указываем какой языковой файл использовать
//        Validator::lang('ru');
        Validator::lang(App::$app->getProperty('language')['code']);

        $validator = new Validator($data);
        // правила валидации, у нас они в array $rules
        $validator->rules($this->rules);
        // говорим валидатору использовать наши labels
        $validator->labels($this->getLabels());
        // если валидация пройдена
        if ($validator->validate()) {
            return true;
        } else {
            $this->errors = $validator->errors();
            return false;
        }
    }

    public function getErrors()
    {
        $errors = '<ul>';
        foreach ($this->errors as $error) {
            foreach ($error as $item) {
                $errors .= "<li>{$item}</li>";
            }
        }
        $errors .= '</ul>';
        $_SESSION['errors'] = $errors;
    }

    public function getLabels(): array
    {
        $labels = [];
        foreach ($this->labels as $k => $v) {
            $labels[$k] = ___($v);
        }
        return $labels;
    }

    public function save($table): int|string
    {
        // $table - таблица в которую надо сохранить данные
        $tbl = R::dispense($table);
        foreach ($this->attributes as $name => $value) {
            if ($value != '') {
                $tbl->$name = $value;
            }
        }
        return R::store($tbl);
    }

    public function update($table, $id): int|string
    {
        // $table - таблица котрую нужно обновить, $id записи
        // получаем запись которую надо обновить
        $tbl = R::load($table, $id);
        foreach ($this->attributes as $name => $value) {
            if ($value != '') {
                $tbl->$name = $value;
            }
        }
        return R::store($tbl);
    }

}