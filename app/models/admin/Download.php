<?php


namespace app\models\admin;


use app\models\AppModel;
use RedBeanPHP\R;

class Download extends AppModel
{

    public function get_downloads($lang, $start, $perpage): array
    {
        return R::getAll("SELECT d.*, dd.* FROM download d JOIN download_description dd on
    d.id = dd.download_id WHERE dd.language_id = ? LIMIT $start, $perpage", [$lang['id']]);
    }

    public function download_validate(): bool
    {
        $errors = '';
        foreach ($_POST['download_description'] as $lang_id => $item) {
            $item['name'] = trim($item['name']);
            // поле name обязательное
            if (empty($item['name'])) {
                $errors .= "Не заполнено наименование {$lang_id}<br>";
            }
        }
        // файл тоже обязательный
        if (empty($_FILES) || $_FILES['file']['error']) {
            $errors .= "Ошибка загрузки файла<br>";
        } else {
            $extensions = ['jpg', 'jpeg', 'png', 'zip', 'pdf', 'txt'];
            // получаем расширение файла (последний элемент после точки)
            $parts = explode('.', $_FILES['file']['name']);
            $ext = end($parts);
            // совпадает ли расширение
            if (!in_array($ext, $extensions)) {
                $errors .= "Допустимые для загрузки расширения: jpg, jpeg, png, zip, pdf, txt<br>";
            }
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            return false;
        }
        return true;
    }

    public function upload_file(): array|false
    {
        // оригинальное имя файла + добавляем рандомную строку (uniqid())
        $file_name = $_FILES['file']['name'] . uniqid();
        // путь куда сохраянем - public/downloads/имя файла
        $path = WWW . '/downloads/' . $file_name;
        /*
         * Как мы помним файл сначала загружается во временную папку.
         * Перемещаем файл с временной папки в public/downloads/
         * */
        if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
            return [
                'original_name' => $_FILES['file']['name'],
                'filename' => $file_name,
            ];
        }
        return false;
    }

    public function save_download($data): bool
    {
        // сохранение инфы о файле в БД
        // в $data массив инфы о загруженном файле
        /*Будет выгрузка данных в 2 таблице, поэтому используем транзакции*/
        R::begin();
        try {
            $download = R::dispense('download');
            $download->filename = $data['filename'];
            $download->original_name = $data['original_name'];
            $download_id = R::store($download);

            foreach ($_POST['download_description'] as $lang_id => $item) {
                R::exec("INSERT INTO  download_description (download_id, language_id, name) 
                    VALUES (?,?,?)", [
                    $download_id,
                    $lang_id,
                    $item['name'],
                ]);
            }
            R::commit();
            return true;
        } catch (\Exception $e) {
            R::rollback();
            return false;
        }
    }

    public function download_delete($id): bool
    {
        $file_name = R::getCell('SELECT filename FROM download WHERE id = ?', [$id]);
        $file_path = WWW . "/downloads/{$file_name}";
        if (file_exists($file_path)) {
            R::begin();
            try {
                // удаляем сразу с нескольких таблиц
                R::exec("DELETE FROM download_description WHERE download_id = ?", [$id]);
                R::exec("DELETE FROM download WHERE id = ?", [$id]);
                R::commit();
                // удаляем физически с папки
                @unlink($file_path);
                return true;
            } catch (\Exception $e) {
                R::rollback();
                return false;
            }
        }
        return false;
    }

}