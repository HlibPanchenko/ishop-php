<?php
/* если есть потомки, то не будем показывать кнопку удалить категорию*/
$parent = isset($category['children']);
if (!$parent) {
    $delete = '<a class="btn btn-danger btn-sm delete" href="' . ADMIN . '/category/delete?id=' . $id . '">
<i class="far fa-trash-alt"></i>
</a>';
} else {
    $delete = '';
}
/* формируем ссылку на редактирование категории*/
$edit = '<a class="btn btn-info btn-sm" href="' . ADMIN . '/category/edit?id=' . $id . '">
<i class="fas fa-pencil-alt"></i>
</a>';
?>
<!--Рисуем таблицу-->
<tr>
    <td>
        <a href="<?= ADMIN ?>/category/edit/?id=<?= $id ?>" style="padding-left: <?= strlen($tab)*3 ?>px"><?= $tab . $category['title'] ?></a>
    </td>
    <td width="50"><?= $edit ?></td>
    <td width="50"><?= $delete ?></td>
</tr>
<!--если есть потомки, вызываем рекурсивно метод getMenuHtml-->
<?php if (isset($category['children'])): ?>
    <?= $this->getMenuHtml($category['children'], $tab . '&#8211;');?>
<?php endif; ?>
