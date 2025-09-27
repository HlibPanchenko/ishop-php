<?php


namespace app\controllers\admin;


use app\models\admin\Product;
use RedBeanPHP\R;
use wfm\App;
use wfm\Pagination;

/** @property Product $model */
class ProductController extends AppController
{

    public function indexAction()
    {
//        debug(ROOT, 1);
        $lang = App::$app->getProperty('language');
        $page = get('page');
        $perpage = 3;
        $total = R::count('product');
        $pagination = new Pagination($page, $perpage, $total);
        $start = $pagination->getStart();

        $products = $this->model->get_products($lang, $start, $perpage);
        $title = 'Список товаров';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title', 'products', 'pagination', 'total'));
    }

    public function addAction()
    {
        if (!empty($_POST)) {
            // если прошли валидацию
            if ($this->model->product_validate()) {
                if ($this->model->save_product()) {
                    $_SESSION['success'] = 'Товар добавлен';
                } else {
                    $_SESSION['errors'] = 'Ошибка добавления товара';
                }
            }
//            debug($_POST,1);
            redirect();
        }

        $title = 'Новый товар';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title'));
    }

    public function editAction()
    {
        $id = get('id');
        // данные передаются постом, то есть пользователь отправил форму на изменение данных товара
        if (!empty($_POST)) {
            if ($this->model->product_validate()) {
                if ($this->model->update_product($id)) {
                    $_SESSION['success'] = 'Товар сохранен';
                } else {
                    $_SESSION['errors'] = 'Ошибка обновления товара';
                }
            }
            redirect();
        }
        // если не $_POST, просто получаем данные о продукте
        $product = $this->model->get_product($id);
        if (!$product) {
            throw new \Exception('Not found product', 404);
        }
//        debug($product,1);

        $gallery = $this->model->get_gallery($id);

        $lang = App::$app->getProperty('language')['id'];
        App::$app->setProperty('parent_id', $product[$lang]['category_id']);
        $title = 'Редактирование товара';
        $this->setMeta("Админка :: {$title}");
        $this->set(compact('title', 'product', 'gallery'));
    }

    public function getDownloadAction()
    {
        // в этот action будут попадать по адрессу get-download.
        // такой массив ожидает плагин Select2 в качестве ответа
        /*$data = [
            'items' => [
                [
                    'id' => 1,
                    'text' => 'Файл 1',
                ],
                [
                    'id' => 2,
                    'text' => 'Файл 2',
                ],
                [
                    'id' => 3,
                    'text' => 'File 1',
                ],
                [
                    'id' => 4,
                    'text' => 'File 2',
                ],
            ]
        ];*/
        $q = get('q', 's');
        $downloads = $this->model->get_downloads($q);
        echo json_encode($downloads);
        die;
    }

}