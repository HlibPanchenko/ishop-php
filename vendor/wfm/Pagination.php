<?php

namespace wfm;

class Pagination
{

    public $currentPage;
    public $perpage; // сколько выводить товаров на одну страницу
    public $total; // все к-во товаров
    public $countPages; // к-во страниц
    public $uri; // параметры запроса (например, сортировка)

    public function __construct($page, $perpage, $total)
    {
        $this->perpage = $perpage;
        $this->total = $total;
        $this->countPages = $this->getCountPages();
        $this->currentPage = $this->getCurrentPage($page);
        $this->uri = $this->getParams();
    }

    public function getHtml()
    {
        // строит пагинацию
        $back = null; // ссылка НАЗАД
        $forward = null; // ссылка ВПЕРЕД
        $startpage = null; // ссылка В НАЧАЛО
        $endpage = null; // ссылка В КОНЕЦ
        $page2left = null; // вторая страница слева
        $page1left = null; // первая страница слева
        $page2right = null; // вторая страница справа
        $page1right = null; // первая страница справа

        // $back
        // если текущая страница больше чем 1, значит кнопка назад нам нужна
        if ($this->currentPage > 1) {
            $back = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage - 1) . "'>&lt;</a></li>";
        }

        // $forward
        if ($this->currentPage < $this->countPages) {
            $forward = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage + 1) . "'>&gt;</a></li>";
        }

        // $startpage
        if ($this->currentPage > 3) {
            $startpage = "<li class='page-item'><a class='page-link' href='" . $this->getLink(1) . "'>&laquo;</a></li>";
        }

        // $endpage
        if ($this->currentPage < ($this->countPages - 2)) {
            $endpage = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->countPages) . "'>&raquo;</a></li>";
        }

        // $page2left
        if ($this->currentPage - 2 > 0) {
            $page2left = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage - 2) . "'>" . ($this->currentPage - 2) . "</a></li>";
        }

        // $page1left
        if ($this->currentPage - 1 > 0) {
            $page1left = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage - 1) . "'>" . ($this->currentPage - 1) . "</a></li>";
        }

        // $page1right
        if ($this->currentPage + 1 <= $this->countPages) {
            $page1right = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage + 1) . "'>" . ($this->currentPage + 1) . "</a></li>";
        }

        // $page2right
        if ($this->currentPage + 2 <= $this->countPages) {
            $page2right = "<li class='page-item'><a class='page-link' href='" . $this->getLink($this->currentPage + 2) . "'>" . ($this->currentPage + 2) . "</a></li>";
        }
        // возвращаем верстку блока пагинации (назад, 1,2,3,вперед)
        return '<nav aria-label="Page navigation example"><ul class="pagination">' . $startpage .
            $back . $page2left . $page1left . '<li class="page-item active"><a class="page-link">' .
            $this->currentPage . '</a></li>' . $page1right . $page2right . $forward . $endpage . '</ul></nav>';
    }


    public function getLink($page)
    {
        // метод формирует ссылку, тут добавялем к ссылке page
        // для первой страницы это не нужно
        if ($page == 1) {
            return rtrim($this->uri, '?&');
        }

        if (str_contains($this->uri, '&')) {
            return "{$this->uri}page={$page}";
        } else {
            if (str_contains($this->uri, '?')) {
                return "{$this->uri}page={$page}";
            } else {
                return "{$this->uri}?page={$page}";
            }
        }
    }

    public function __toString()
    {
        return $this->getHtml();
    }

    public function getCountPages()
    {
        return ceil($this->total / $this->perpage) ?: 1;
    }

    public function getCurrentPage($page)
    {
        if (!$page || $page < 1) $page = 1;
        // если запросилу страницу больше чем их есть, то попадаем на last page
        if ($page > $this->countPages) $page = $this->countPages;
        return $page;
    }

    public function getStart()
        // чтобы в limit указать с какой записи должны возвращать товары
    {
        // текущая страница 5: 5-1*3 - возвращаем с 12 товара
        // то есть на 5й странице покажем 13,14,15 товары
        return ($this->currentPage - 1) * $this->perpage;
    }

    public function getParams()
    {
        // заполняем  $uri параметрами запроса (например, сортировка)
        // берем get params
        $url = $_SERVER['REQUEST_URI'];
        // http://new-ishop.loc/category/windows?page=1$sort=name
        $url = explode('?', $url);
        $uri = $url[0]; // запихиваем все что было до "?"
        if (isset($url[1]) && $url[1] != '') {
            // собираем все get params кроме page
            $uri .= '?';
            $params = explode('&', $url[1]);
            foreach ($params as $param) {
                if (!preg_match("#page=#", $param)) $uri .= "{$param}&";
            }
        }
        return $uri;
    }

}
