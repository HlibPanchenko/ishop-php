<?php

namespace app\models;

use RedBeanPHP\R;

class Cart extends AppModel
{
    // $id - id продукта, $lang - на каком языке получить продукт
    public function get_product($id, $lang): array
    {
        return R::getRow("SELECT p.*, pd.* FROM product p JOIN 
          product_description pd on p.id = pd.product_id WHERE 
            p.status = 1 AND p.id = ? AND pd.language_id = ?", [$id, $lang['id']]);
    }

    public function add_to_cart($product, $qty = 1)
    {
        $qty = abs($qty); // чтобы не было отрицательного числа
        // цифровой товар можно добавлять только 1 шт.
        if ($product['is_download'] && isset($_SESSION['cart'][$product['id']])) {
            return false;
        }
        // если в корзине уже есть продукт, то при добавлении увеличиваем его к-во
        if (isset($_SESSION['cart'][$product['id']])) {
            $_SESSION['cart'][$product['id']]['qty'] += $qty;
        } else {
            if ($product['is_download']) {
            // на всякий случай строго указываем что цифровой товар 1
                $qty = 1;
            }
            // продукт первый раз добавляем в корзину
            $_SESSION['cart'][$product['id']] = [
                'title' => $product['title'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'qty' => $qty,
                'img' => $product['img'],
                'is_download' => $product['is_download'],
            ];
        }
        // общая сумма и к-во всех товаров в корзине
        $_SESSION['cart.qty'] = !empty($_SESSION['cart.qty']) ? $_SESSION['cart.qty'] + $qty : $qty;
        $_SESSION['cart.sum'] = !empty($_SESSION['cart.sum']) ? $_SESSION['cart.sum'] + $qty * $product['price'] : $qty * $product['price'];
        return true;
    }

    public function delete_item($id)
    {
        $qty_minus = $_SESSION['cart'][$id]['qty'];
        $sum_minus = $_SESSION['cart'][$id]['qty'] * $_SESSION['cart'][$id]['price'];
        $_SESSION['cart.qty'] -= $qty_minus;
        $_SESSION['cart.sum'] -= $sum_minus;
        unset($_SESSION['cart'][$id]);
    }

    public static function translate_cart($lang)
    {
        if (empty($_SESSION['cart'])) {
            return;
        }
        // получаем id товаров в корзине, собираем их в строку
        $ids = implode(',', array_keys($_SESSION['cart']));
        // получим эти продукты, только уже на другом языке
        $products = R::getAll("SELECT p.id, pd.title FROM product p JOIN product_description pd on 
            p.id = pd.product_id WHERE p.id IN ($ids) AND pd.language_id = ?", [$lang['id']]);
        // меняем title на title другого языка
        foreach ($products as $product) {
            $_SESSION['cart'][$product['id']]['title'] = $product['title'];
        }
    }

}

// Пример как будет выглядеть массив корзины
/*Array
(
    [product_id] => Array
        (
            [qty] => QTY
            [title] => TITLE
            [price] => PRICE
            [img] => IMG
        )
    [product_id] => Array
        (
            [qty] => QTY
            [title] => TITLE
            [price] => PRICE
            [img] => IMG
        )
    )
    [cart.qty] => QTY, // итоговое к-во
    [cart.sum] => SUM // итоговая сумма
*/