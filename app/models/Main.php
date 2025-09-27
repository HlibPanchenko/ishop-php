<?php

namespace app\models;


use RedBeanPHP\R;

class Main extends AppModel
{
    // язык пользователя и лимит записей сколько покажем.
    public function get_hits($lang, $limit): array
    {
        return R::getAll("SELECT p.* , pd.* FROM product p JOIN product_description pd on p.id = pd.product_id 
        WHERE p.status = 1 AND p.hit = 1 AND pd.language_id = ? LIMIT $limit", [$lang['id']]);
    }

}

