<?php


namespace app\controllers\admin;


use RedBeanPHP\R;
use wfm\App;

class CategoryController extends AppController
{

    public function indexAction()
    {
        $title = 'Категории';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title'));
    }

    public function deleteAction()
    {
        $id = get('id');
        $errors = '';
        $children = R::count('category', 'parent_id = ?', [$id]);
        $products = R::count('product', 'category_id = ?', [$id]);
        // нельзя удалить категорию, у которой есть подкатегории
        if ($children) {
            $errors .= 'Ошибка! В категории есть вложенные категории<br>';
        }
        // нельзя удалить категорию, у которой есть товары
        if ($products) {
            $errors .= 'Ошибка! В категории есть товары<br>';
        }
        if ($errors) {
            $_SESSION['errors'] = $errors;
        } else {
            // удаляем
            R::exec("DELETE FROM category WHERE id = ?", [$id]);
            R::exec("DELETE FROM category_description WHERE category_id = ?", [$id]);
            $_SESSION['success'] = 'Категория удалена';
        }
        redirect();
    }

    public function addAction()
    {
        if (!empty($_POST)) {
            if ($this->model->category_validate()) {
                if ($this->model->save_category()) {
                    $_SESSION['success'] = 'Категория сохранена';
                } else {
                    $_SESSION['errors'] = 'Ошибка!';
                }
            }
            redirect();
        }
        $title = 'Добавление категории';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title'));
    }

    public function editAction()
    {
        $id = get('id');
        if (!empty($_POST)) {
            if ($this->model->category_validate()) {
                if ($this->model->update_category($id)) {
                    $_SESSION['success'] = 'Категория обновлена';
                } else {
                    $_SESSION['errors'] = 'Ошибка!';
                }
            }
            redirect();
        }
        $category = $this->model->get_category($id);
        if (!$category) {
            throw new \Exception('Not found category', 404);
        }
        $lang = App::$app->getProperty('language')['id'];
        // родительская категория
        App::$app->setProperty('parent_id', $category[$lang]['parent_id']);
        $title = 'Редактирование категории';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title', 'category'));
    }

}