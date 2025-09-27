<?php


namespace app\models\admin;


use app\models\AppModel;
use RedBeanPHP\R;
use wfm\App;

class Product extends AppModel
{

    public function get_products($lang, $start, $perpage): array
    {
        return R::getAll("SELECT p.*, pd.title FROM product p JOIN
                            product_description pd on p.id = pd.product_id WHERE
                            pd.language_id = ? LIMIT $start, $perpage", [$lang['id']]);
    }

    public function update_product($id): bool
    {
        R::begin(); // транзакция
        try {
            // product
            // получаем продукт из БД
            $product = R::load('product', $id);
            if (!$product) {
                return false;
            }
            // заполняем его обновленными данными
            $product->category_id = post('parent_id', 'i');
            $product->price = post('price', 'f');
            $product->old_price = post('old_price', 'f');
            $product->status = post('status') ? 1 : 0;
            $product->hit = post('hit') ? 1 : 0;
            $product->img = post('img') ?: NO_IMAGE;
            $product->is_download = post('is_download') ? 1 : 0;
            $product_id = R::store($product);

            // заполняем таблицу product_description
            foreach ($_POST['product_description'] as $lang_id => $item) {
                R::exec("UPDATE product_description SET title = ?, content = ?, exerpt = ?, keywords = ?, description = ? WHERE product_id = ? AND language_id = ?", [
                    $item['title'],
                    $item['content'],
                    $item['exerpt'],
                    $item['keywords'],
                    $item['description'],
                    $id,
                    $lang_id,
                ]);
            }

            //  заполняем таблицу product_gallery if exists
            // если пользователь не прислал картинки, значит он хочет чтобы продукт был без них
            // тогда удаляем все картинки в БД для этого товара
            if (!isset($_POST['gallery'])) {
                R::exec("DELETE FROM product_gallery WHERE product_id = ?", [$id]);
            }
            // если пользователь прислал хоть 1-ну картинку
            if (isset($_POST['gallery']) && is_array($_POST['gallery'])) {
                // получаем картинки которые есть в БД
                $gallery = self::get_gallery($id);
                // array_diff - сравнивает 2 массива
                // нам надо чтобы $gallery в БД отличалась от $_POST['gallery'] - то что прислал пользователь
                if ( (count($gallery) != count($_POST['gallery'])) || array_diff($gallery, $_POST['gallery']) || array_diff($_POST['gallery'], $gallery) ) {
                   // удаляем все картинки
                    R::exec("DELETE FROM product_gallery WHERE product_id = ?", [$id]);
                    // формируем sql чтобы положить все картинки, которые пришли от пользователя, в БД
                    $sql = "INSERT INTO product_gallery (product_id, img) VALUES ";
                    foreach ($_POST['gallery'] as $item) {
                        $sql .= "({$id}, ?),";
                    }
                    $sql = rtrim($sql, ',');
                    R::exec($sql, $_POST['gallery']);
                }
            }

            // заполняем таблицу product_download if is_download
            // сначала удаляем все
            R::exec("DELETE FROM product_download WHERE product_id = ?", [$id]);
            if ($product->is_download) {
            // добавляем новые файлы
                $download_id = post('is_download', 'i');
                R::exec("INSERT INTO product_download (product_id, download_id) VALUES (?,?)", [$product_id, $download_id]);
            }

            R::commit();
            return true;
        } catch (\Exception $e) {
            R::rollback();
            return false;
        }
    }

    public function get_product($id): array|false
    {
        $product = R::getAssoc("SELECT pd.language_id, pd.*, p.* FROM product_description pd JOIN
    product p ON p.id = pd.product_id WHERE pd.product_id = ?", [$id]);
        if (!$product) {
            return false;
        }
        $key = key($product);
        // если продукт цифровой, то достаем еще инфу про файл
        if ($product[$key]['is_download']) {
            $download_info = self::get_product_download($id);
            $product[$key]['download_id'] = $download_info['download_id'];
            $product[$key]['download_name'] = $download_info['name'];
        }
        return $product;
    }

    public function get_product_download($product_id): array
    {
        // получаем прикрепленный файл к цифровому товару
        $lang_id = App::$app->getProperty('language')['id'];
        return R::getRow("SELECT pd.download_id, dd.name FROM product_download pd JOIN
    download_description dd ON pd.download_id = dd.download_id WHERE pd.product_id = ? AND dd.language_id = ?",
            [$product_id, $lang_id]);
    }

    public function get_downloads($q): array
    {
        $data = [];
        // поиск по полю name
        $downloads = R::getAssoc("SELECT download_id, name FROM download_description WHERE
                                                       name LIKE ? LIMIT 10", ["%{$q}%"]);
        if ($downloads) {
            $i = 0;
            foreach ($downloads as $id => $title) {
                $data['items'][$i]['id'] = $id;
                $data['items'][$i]['text'] = $title;
                $i++;
            }
        }
        return $data;
    }

    public function product_validate(): bool
    {
        $errors = '';
        if (!is_numeric(post('price'))) {
            $errors .= "Цена должна быть числовым значением<br>";
        }
        if (!is_numeric(post('old_price'))) {
            $errors .= "Старая цена должна быть числовым значением<br>";
        }

        foreach ($_POST['product_description'] as $lang_id => $item) {
            $item['title'] = trim($item['title']);
            $item['exerpt'] = trim($item['exerpt']);
            if (empty($item['title'])) {
                $errors .= "Не заполнено Наименование во вкладке {$lang_id}<br>";
            }
            if (empty($item['exerpt'])) {
                $errors .= "Не заполнено Краткое описание во вкладке {$lang_id}<br>";
            }
        }

        if ($errors) {
            // сохраянем в сессию ошибки и заполненые поля формы
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = $_POST;
            return false;
        }
        return true;
    }

    public function save_product(): bool
    {
        $lang = App::$app->getProperty('language')['id'];
        R::begin(); // транзакция
        try {
            // таблица product
            $product = R::dispense('product');
            $product->category_id = post('parent_id', 'i');
            $product->price = post('price', 'f');
            $product->old_price = post('old_price', 'f');
            $product->status = post('status') ? 1 : 0;
            $product->hit = post('hit') ? 1 : 0;
            $product->img = post('img') ?: NO_IMAGE;
            $product->is_download = post('is_download') ? 1 : 0;
            $product_id = R::store($product);

            $product->slug = AppModel::create_slug('product', 'slug',
                $_POST['product_description'][$lang]['title'],
                $product_id);
            R::store($product);

            // выгружаем данные в таблицу product_description
            foreach ($_POST['product_description'] as $lang_id => $item) {
                R::exec("INSERT INTO product_description (product_id, language_id, title, content, exerpt, keywords, description) VALUES (?,?,?,?,?,?,?)", [
                    $product_id,
                    $lang_id,
                    $item['title'],
                    $item['content'],
                    $item['exerpt'],
                    $item['keywords'],
                    $item['description'],
                ]);
            }

            // таблица product_gallery if exists (если прикреплены картинки к товару)
            if (isset($_POST['gallery']) && is_array($_POST['gallery'])) {
                // формируем sql запрос
                $sql = "INSERT INTO product_gallery (product_id, img) VALUES ";
                foreach ($_POST['gallery'] as $item) {
                    // дополняем sql запрос в цикле
                    // ? - картинка
                    $sql .= "({$product_id}, ?),";
                }
                // удаляем кому в конце
                $sql = rtrim($sql, ',');
                // выполняем sql запрос
                R::exec($sql, $_POST['gallery']);
            }

            // таблица product_download if is_download
            // если товар цифрофой, то должны выгрузить id товара и download_id (id файла который прикреплен к товару)
            if ($product->is_download) {
                // в 'is_download' id файла
                $download_id = post('is_download', 'i');
                R::exec("INSERT INTO product_download (product_id, download_id) VALUES (?,?)",
                     [$product_id, $download_id]);
            }

            R::commit();
            return true;
        } catch (\Exception $e) {
            R::rollback();
            $_SESSION['form_data'] = $_POST;
            return false;
        }
    }

    public function get_gallery($id): array
    {
        return R::getCol("SELECT img FROM product_gallery WHERE product_id = ?", [$id]);
    }



}