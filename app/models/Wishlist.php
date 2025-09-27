<?php

namespace app\models;

use RedBeanPHP\R;

class Wishlist extends AppModel
{

    public function get_product($id): array|null|string
    {
        // получаем одну ячейку потому что в куках будем хранить не всю инфу об товаре, а только id
        return R::getCell("SELECT id FROM product WHERE status = 1 AND id = ?", [$id]);
    }

    public function add_to_wishlist($id)
    {
        // массив idшников либо пустой
        $wishlist = self::get_wishlist_ids();
        if (!$wishlist) {
            // добавляем текущий $id в куки в пустой массив
            // '/' - храним для всего домена
            setcookie('wishlist', $id, time() + 3600 * 24 * 7 * 30, '/');
        } else {
            // если в массиве уже что-то есть
            // максимум будем хранить 5 товаров в избранном в куки
            if (!in_array($id, $wishlist)) {
                if (count($wishlist) > 5) {
                    // удаляем первый элемент массива
                    array_shift($wishlist);
                }
                // добавляем $id в массив
                $wishlist[] = $id;
                // преобразуем в строку
                $wishlist = implode(',', $wishlist);
                // и уже как строчку записываем в куки
                setcookie('wishlist', $wishlist, time() + 3600 * 24 * 7 * 30, '/');
            }
        }
    }

    public static function get_wishlist_ids(): array
    {
        // проверяем нет ли уже товара в избранном (в куках)
        $wishlist = $_COOKIE['wishlist'] ?? '';
        if ($wishlist) {
            $wishlist = explode(',', $wishlist);
        }
        if (is_array($wishlist)) {
            // Это важно, чтобы убедиться, что данные из куки корректно преобразованы в массив.
            /* Если $wishlist является массивом, код обрезает его до первых 6 элементов.
            Это, вероятно, сделано для ограничения размера списка избранных до 6 элементов.*/
            $wishlist = array_slice($wishlist, 0, 6);
            /*каждый элемент массива $wishlist преобразуется в целое число с помощью функции intval.
            Это выполняется, чтобы удостовериться, что все идентификаторы продуктов представлены как целые числа*/
            $wishlist = array_map('intval', $wishlist);
            return $wishlist;
        }
        return [];
    }

    public function get_wishlist_products($lang): array
    {
        // получаем массив idшников избранных товаров с кук
        $wishlist = self::get_wishlist_ids();
        if ($wishlist) {
            $wishlist = implode(',', $wishlist);
            return R::getAll("SELECT p.*, pd.* FROM product p JOIN product_description pd on
                            p.id = pd.product_id WHERE p.status = 1 AND p.id IN ($wishlist) AND
                            pd.language_id = ? LIMIT 6", [$lang['id']]);
        }
        return [];
    }

    public function delete_from_wishlist($id): bool
    {
        // получаем массив idшников избранных товаров с кук
        $wishlist = self::get_wishlist_ids();
        // ключ первого найденного элемента
        $key = array_search($id, $wishlist);
        if (false !== $key) {
            // удаляем
            unset($wishlist[$key]);
            // если в $wishlist что-то еще осталось
            if ($wishlist) {
                // переводим в строку чтобы записать в куки
                $wishlist = implode(',', $wishlist);
                setcookie('wishlist', $wishlist, time() + 3600*24*7*30, '/');
            } else {
                // если удалили последний элемент в массие, то удаляем всю куку
                setcookie('wishlist', '', time()-3600, '/');
            }
            return true;
        }
        return false;
    }


}